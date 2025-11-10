pipeline {
  agent any
  parameters {
    string(name: 'AWS_ACCOUNT_ID', defaultValue: '', description: 'ID de cuenta AWS (ej: 123456789012)')
    string(name: 'AWS_REGION', defaultValue: 'us-east-1', description: 'Región AWS')
    string(name: 'ECR_REPO', defaultValue: 'proyecto-innovari', description: 'Nombre del repo ECR')
    string(name: 'ECS_CLUSTER', defaultValue: '', description: 'Nombre del cluster ECS')
    string(name: 'ECS_SERVICE', defaultValue: '', description: 'Nombre del servicio ECS')
  }
  environment {
    IMAGE_TAG = "${env.BUILD_NUMBER}"
    AWS_CREDENTIALS_ID = 'aws-creds' // Cambia si usas otro id de credencial en Jenkins
  }
  stages {
    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Build Docker image') {
      steps {
        sh '''
          echo "Building docker image..."
          docker build -t ${ECR_REPO}:${IMAGE_TAG} .
        '''
      }
    }

    stage('Authenticate to ECR') {
      steps {
        withCredentials([usernamePassword(credentialsId: env.AWS_CREDENTIALS_ID, usernameVariable: 'AWS_ACCESS_KEY_ID', passwordVariable: 'AWS_SECRET_ACCESS_KEY')]) {
          sh '''
            echo "Logging in to ECR..."
            aws --version
            aws configure set aws_access_key_id "$AWS_ACCESS_KEY_ID"
            aws configure set aws_secret_access_key "$AWS_SECRET_ACCESS_KEY"
            aws configure set default.region ${AWS_REGION}
            aws ecr get-login-password --region ${AWS_REGION} | docker login --username AWS --password-stdin ${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com
          '''
        }
      }
    }

    stage('Create/Tag/Push to ECR') {
      steps {
        sh '''
          echo "Ensure ECR repository exists..."
          aws ecr describe-repositories --repository-names ${ECR_REPO} --region ${AWS_REGION} || aws ecr create-repository --repository-name ${ECR_REPO} --region ${AWS_REGION}
          IMAGE=${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}:${IMAGE_TAG}
          docker tag ${ECR_REPO}:${IMAGE_TAG} ${IMAGE}
          echo "Pushing image ${IMAGE}..."
          docker push ${IMAGE}
        '''
      }
    }

    stage('Deploy to ECS') {
      steps {
        sh '''
          echo "Updating task definition and deploying to ECS..."
          # Reemplaza la imagen en task-definition.json (requiere jq)
          jq --arg IMG "${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com/${ECR_REPO}:${IMAGE_TAG}" '.containerDefinitions[0].image=$IMG' task-definition.json > task-def-updated.json
          aws ecs register-task-definition --cli-input-json file://task-def-updated.json --region ${AWS_REGION}
          aws ecs update-service --cluster ${ECS_CLUSTER} --service ${ECS_SERVICE} --force-new-deployment --region ${AWS_REGION}
        '''
      }
    }
  }
  post {
    always {
      sh 'docker image prune -f || true'
    }
    success {
      echo "Deploy exitoso: ${ECR_REPO}:${IMAGE_TAG}"
    }
  }
}
