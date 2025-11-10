pipeline {
  agent any

  parameters {
    string(name: 'AWS_REGION', defaultValue: 'us-east-1', description: 'Región AWS (por defecto us-east-1)')
    string(name: 'ACCOUNT_ID', defaultValue: 'TU_ACCOUNT_ID', description: 'Tu AWS Account ID (opcional — se detecta automáticamente si está vacío o con TU_ACCOUNT_ID)')
    string(name: 'ECR_REPO', defaultValue: 'nginx-ecs-demo', description: 'Nombre del repo ECR (lowercase recommended)')
    string(name: 'ECS_CLUSTER', defaultValue: 'ecs-lab-cluster', description: 'Nombre del cluster ECS')
    string(name: 'ECS_SERVICE', defaultValue: 'nginx-lab-svc', description: 'Nombre del service ECS')
    string(name: 'TASK_FAMILY', defaultValue: 'nginx-lab-task', description: 'Family de la task definition')
    string(name: 'AWS_CREDENTIALS_ID', defaultValue: '373229397038', description: 'ID de credencial AWS en Jenkins')
  }

  environment {
    IMAGE_TAG = "${env.BUILD_NUMBER}"
  }

  stages {
    stage('Prerequisites check') {
      steps {
        sh '''
set -eu
command -v docker >/dev/null 2>&1 || { echo "docker not found"; exit 1; }
command -v aws >/dev/null 2>&1 || { echo "aws cli not found"; exit 1; }
command -v jq >/dev/null 2>&1 || { echo "jq not found"; exit 1; }
'''
      }
    }

    stage('Checkout') {
      steps { checkout scm }
    }

    stage('Detect / validate account & names') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
          sh '''
set -eu
# If ACCOUNT_ID parameter is placeholder or empty, try to detect from credentials
INPUT_ACC="${ACCOUNT_ID:-}"
if [ -z "$INPUT_ACC" ] || [ "$INPUT_ACC" = "TU_ACCOUNT_ID" ]; then
  echo "Detectando AWS Account con aws sts..."
  DETECTED_ACC=$(aws sts get-caller-identity --query Account --output text --region ${AWS_REGION} 2>/dev/null || true)
  if [ -z "$DETECTED_ACC" ]; then
    echo "ERROR: no se pudo detectar ACCOUNT_ID. Especificalo en parametros del build."
    exit 1
  fi
  ACCOUNT_ID="$DETECTED_ACC"
else
  ACCOUNT_ID="$INPUT_ACC"
fi

# Lowercase repo name
ECR_REPO_LOWER=$(echo "${ECR_REPO}" | tr 'A-Z' 'a-z')

# Validate account numeric
if echo "$ACCOUNT_ID" | grep -q '[^0-9]'; then
  echo "ERROR: ACCOUNT_ID debe ser numérico. Valor: $ACCOUNT_ID"
  exit 1
fi

echo "Usando ACCOUNT_ID=$ACCOUNT_ID, REGION=${AWS_REGION}, ECR_REPO=${ECR_REPO_LOWER}"
# export for next stages
export ACCOUNT_ID ECR_REPO_LOWER
'''
        }
      }
    }

    stage('Build image') {
      steps {
        sh '''
set -eu
ECR_FULL="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO_LOWER}"
docker build -t "${ECR_FULL}:${IMAGE_TAG}" .
'''
      }
    }

    stage('ECR create/login & push') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
          sh '''
set -eu
ECR_REG="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
# create repository if not exists (no falla si ya existe)
aws ecr describe-repositories --repository-names "${ECR_REPO_LOWER}" --region ${AWS_REGION} >/dev/null 2>&1 || \
  aws ecr create-repository --repository-name "${ECR_REPO_LOWER}" --region ${AWS_REGION}
# login
aws ecr get-login-password --region ${AWS_REGION} | docker login --username AWS --password-stdin ${ECR_REG}
# push
docker push "${ECR_REG}/${ECR_REPO_LOWER}:${IMAGE_TAG}"
'''
        }
      }
    }

    stage('Register task definition & update service') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
          sh '''
set -eu
ECR_IMAGE="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO_LOWER}:${IMAGE_TAG}"

# Get latest task definition arn for family
LATEST_TD_ARN=$(aws ecs list-task-definitions --family-prefix ${TASK_FAMILY} --status ACTIVE --sort DESC --region ${AWS_REGION} --query 'taskDefinitionArns[0]' --output text || echo "")

if [ -z "$LATEST_TD_ARN" ] || [ "$LATEST_TD_ARN" = "None" ]; then
  echo "No existe task definition previa. Registrando una definición mínima dinámica..."
  cat > generated-td.json <<EOF
{
  "family": "${TASK_FAMILY}",
  "networkMode": "awsvpc",
  "requiresCompatibilities": ["FARGATE"],
  "cpu": "256",
  "memory": "512",
  "executionRoleArn": "arn:aws:iam::${ACCOUNT_ID}:role/ecsTaskExecutionRole",
  "taskRoleArn": "arn:aws:iam::${ACCOUNT_ID}:role/ecsTaskRole",
  "containerDefinitions": [
    {
      "name": "nginx",
      "image": "${ECR_IMAGE}",
      "portMappings": [ { "containerPort": 80, "protocol": "tcp" } ],
      "essential": true
    }
  ]
}
EOF
  aws ecs register-task-definition --cli-input-json file://generated-td.json --region ${AWS_REGION}
else
  echo "Actualizando la última task definition $LATEST_TD_ARN con la nueva imagen..."
  aws ecs describe-task-definition --task-definition $LATEST_TD_ARN --region ${AWS_REGION} --query 'taskDefinition' --output json > td.json
  cat td.json | jq --arg img "$ECR_IMAGE" 'del(.status, .revision, .taskDefinitionArn, .requiresAttributes, .compatibilities) | .containerDefinitions[0].image=$img' > new-td.json
  aws ecs register-task-definition --cli-input-json file://new-td.json --region ${AWS_REGION}
fi

# Get newest registered TD ARN and update service
NEW_TD_ARN=$(aws ecs list-task-definitions --family-prefix ${TASK_FAMILY} --status ACTIVE --sort DESC --region ${AWS_REGION} --query 'taskDefinitionArns[0]' --output text)
echo "Nueva task definition: $NEW_TD_ARN"
aws ecs update-service --cluster ${ECS_CLUSTER} --service ${ECS_SERVICE} --task-definition "$NEW_TD_ARN" --region ${AWS_REGION}
aws ecs wait services-stable --cluster ${ECS_CLUSTER} --services ${ECS_SERVICE} --region ${AWS_REGION}
'''
        }
      }
    }
  }

  post {
    success { echo "PIPELINE OK" }
    failure { echo "PIPELINE FAIL" }
  }
}
