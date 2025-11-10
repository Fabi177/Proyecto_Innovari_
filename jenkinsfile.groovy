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
        script {
          // Use Jenkins AWS credentials binding to allow aws sts call from Groovy shell
          withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
            // Determine ACCOUNT_ID
            def inputAcc = params.ACCOUNT_ID?.trim()
            def acc = inputAcc
            if (!acc || acc == 'TU_ACCOUNT_ID') {
              echo "Attempting to detect AWS Account ID using provided AWS credentials..."
              def detected = sh(script: "aws sts get-caller-identity --query Account --output text --region ${params.AWS_REGION}", returnStdout: true).trim()
              if (!detected) {
                error "ERROR: could not detect AWS Account ID. Provide ACCOUNT_ID as build parameter."
              }
              acc = detected
            }
            // Validate numeric
            if (acc ==~ /.*[A-Za-z].*/) {
              error "ERROR: ACCOUNT_ID must be numeric (12 digits). Current value contains letters: '${acc}'"
            }
            // Lowercase repo name
            def repoLower = (params.ECR_REPO ?: 'nginx-ecs-demo').toLowerCase()

            // Export into env for later shell stages
            env.ACCOUNT_ID = acc
            env.ECR_REPO_LOWER = repoLower

            echo "Using ACCOUNT_ID=${env.ACCOUNT_ID}, REGION=${params.AWS_REGION}, ECR_REPO=${env.ECR_REPO_LOWER}"
          }
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
aws ecr describe-repositories --repository-names "${ECR_REPO_LOWER}" --region ${AWS_REGION} >/dev/null 2>&1 || \
  aws ecr create-repository --repository-name "${ECR_REPO_LOWER}" --region ${AWS_REGION}
aws ecr get-login-password --region ${AWS_REGION} | docker login --username AWS --password-stdin ${ECR_REG}
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

LATEST_TD_ARN=$(aws ecs list-task-definitions --family-prefix ${TASK_FAMILY} --status ACTIVE --sort DESC --region ${AWS_REGION} --query 'taskDefinitionArns[0]' --output text || echo "")
if [ -z "$LATEST_TD_ARN" ] || [ "$LATEST_TD_ARN" = "None" ]; then
  echo "No existing task definition: registering a minimal generated task definition..."
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
  echo "Updating last task definition $LATEST_TD_ARN with new image..."
  aws ecs describe-task-definition --task-definition $LATEST_TD_ARN --region ${AWS_REGION} --query 'taskDefinition' --output json > td.json
  cat td.json | jq --arg img "$ECR_IMAGE" 'del(.status, .revision, .taskDefinitionArn, .requiresAttributes, .compatibilities) | .containerDefinitions[0].image=$img' > new-td.json
  aws ecs register-task-definition --cli-input-json file://new-td.json --region ${AWS_REGION}
fi

NEW_TD_ARN=$(aws ecs list-task-definitions --family-prefix ${TASK_FAMILY} --status ACTIVE --sort DESC --region ${AWS_REGION} --query 'taskDefinitionArns[0]' --output text)
echo "New task definition: $NEW_TD_ARN"
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
