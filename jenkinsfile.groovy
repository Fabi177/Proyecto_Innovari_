pipeline {
  agent any

  parameters {
    string(name: 'AWS_REGION', defaultValue: 'us-east-1', description: 'Región AWS (por defecto us-east-1)')
    string(name: 'ACCOUNT_ID', defaultValue: 'TU_ACCOUNT_ID', description: 'ID de cuenta AWS')
    string(name: 'ECR_REPO', defaultValue: 'nginx-ecs-demo', description: 'Nombre del repo ECR')
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

    stage('Build image') {
      steps {
        script {
          def ecrFull = "${params.ACCOUNT_ID}.dkr.ecr.${params.AWS_REGION}.amazonaws.com/${params.ECR_REPO}"
          sh "docker build -t ${ecrFull}:${IMAGE_TAG} ."
        }
      }
    }

    stage('ECR: ensure repo & login') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
          sh '''
set -eu
ECR_REGISTRY="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
aws ecr describe-repositories --repository-names "${ECR_REPO}" --region ${AWS_REGION} >/dev/null 2>&1 || aws ecr create-repository --repository-name "${ECR_REPO}" --region ${AWS_REGION}
aws ecr get-login-password --region ${AWS_REGION} | docker login --username AWS --password-stdin ${ECR_REGISTRY}
'''
        }
      }
    }

    stage('Push to ECR') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
          sh '''
set -eu
ECR_FULL="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}"
docker tag ${ECR_REPO}:${IMAGE_TAG} ${ECR_FULL}:${IMAGE_TAG} || true
docker push ${ECR_FULL}:${IMAGE_TAG}
'''
        }
      }
    }

    stage('Register task definition and update service') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: "${params.AWS_CREDENTIALS_ID}"]]) {
          sh '''
set -eu
ECR_IMAGE="${ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}:${IMAGE_TAG}"
LATEST_TD_ARN=$(aws ecs list-task-definitions --family-prefix ${TASK_FAMILY} --status ACTIVE --sort DESC --region ${AWS_REGION} --query 'taskDefinitionArns[0]' --output text || echo "")
if [ -z "$LATEST_TD_ARN" ] || [ "$LATEST_TD_ARN" = "None" ]; then
  if [ -f task-definition.json ]; then
    jq --arg img "$ECR_IMAGE" --arg acc "$ACCOUNT_ID" --arg reg "$AWS_REGION" \
      '.containerDefinitions[0].image=$img | .executionRoleArn="arn:aws:iam::"+$acc+":role/ecsTaskExecutionRole" | .taskRoleArn="arn:aws:iam::"+$acc+":role/ecsTaskRole"' \
      task-definition.json > td-for-register.json
    aws ecs register-task-definition --cli-input-json file://td-for-register.json --region ${AWS_REGION}
  else
    echo "ERROR: task-definition.json not found and no existing task definition"; exit 1
  fi
else
  aws ecs describe-task-definition --task-definition $LATEST_TD_ARN --region ${AWS_REGION} --query 'taskDefinition' --output json > td.json
  cat td.json | jq --arg img "$ECR_IMAGE" 'del(.status, .revision, .taskDefinitionArn, .requiresAttributes, .compatibilities) | .containerDefinitions[0].image=$img' > new-td.json
  aws ecs register-task-definition --cli-input-json file://new-td.json --region ${AWS_REGION}
fi

NEW_TD_ARN=$(aws ecs list-task-definitions --family-prefix ${TASK_FAMILY} --status ACTIVE --sort DESC --region ${AWS_REGION} --query 'taskDefinitionArns[0]' --output text)
aws ecs update-service --cluster ${ECS_CLUSTER} --service ${ECS_SERVICE} --task-definition $NEW_TD_ARN --region ${AWS_REGION}
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
