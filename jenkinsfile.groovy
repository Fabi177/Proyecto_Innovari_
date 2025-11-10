pipeline {
  agent any

  parameters {
    string(name: 'AWS_REGION', defaultValue: 'us-east-1', description: 'AWS region for ECR/ECS')
    string(name: 'AWS_ACCOUNT_ID', defaultValue: '', description: 'AWS account id (e.g. 123456789012)')
    string(name: 'ECR_REPO', defaultValue: 'innovari-laravel', description: 'ECR repository name')
    string(name: 'CLUSTER_NAME', defaultValue: 'innovari-cluster', description: 'ECS cluster name')
    string(name: 'SERVICE_NAME', defaultValue: 'innovari-service', description: 'ECS service name to update')
    string(name: 'CONTAINER_NAME', defaultValue: 'innovari-container', description: 'Container name inside task definition to patch image for')
    booleanParam(name: 'FORCE_NEW_DEPLOY', defaultValue: true, description: 'Force a new deployment after registering task definition')
  }

  environment {
    AWS_DEFAULT_REGION = "${params.AWS_REGION}"
  }

  stages {
    stage('Checkout') {
      steps {
        checkout scm
        script {
          def GIT_COMMIT_SHORT = sh(script: "git rev-parse --short=7 HEAD", returnStdout: true).trim()
          def tagSuffix = "${env.BUILD_NUMBER ?: 'local'}-${GIT_COMMIT_SHORT}"
          def IMAGE_TAG = "${params.ECR_REPO}:${tagSuffix}"
          env.IMAGE_TAG = IMAGE_TAG
          echo "Image tag will be: ${env.IMAGE_TAG}"
          if (!params.AWS_ACCOUNT_ID?.trim()) {
            echo "Warning: AWS_ACCOUNT_ID is empty — set it in job parameters to push to your account."
          }
        }
      }
    }

    stage('Build Docker Image') {
      steps {
        script {
          echo "Building docker image ${env.IMAGE_TAG}..."
          sh "docker build -t ${env.IMAGE_TAG} ."
        }
      }
    }

    stage('Login to AWS ECR') {
      steps {
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-creds']]) {
          script {
            if (!params.AWS_ACCOUNT_ID?.trim()) {
              error "AWS_ACCOUNT_ID parameter is required for ECR login"
            }
            env.ECR_REGISTRY = "${params.AWS_ACCOUNT_ID}.dkr.ecr.${params.AWS_REGION}.amazonaws.com"
            echo "Logging in to ECR: ${env.ECR_REGISTRY}"
            sh "aws --version || true"
            sh "aws ecr get-login-password --region ${params.AWS_REGION} | docker login --username AWS --password-stdin ${env.ECR_REGISTRY}"
          }
        }
      }
    }

    stage('Tag & Push to ECR') {
      steps {
        script {
          def fullImage = "${env.ECR_REGISTRY}/${env.IMAGE_TAG}"
          echo "Tagging image ${env.IMAGE_TAG} as ${fullImage}"
          sh "docker tag ${env.IMAGE_TAG} ${fullImage}"

          echo "Ensuring ECR repository ${params.ECR_REPO} exists..."
          sh """
            set -e
            aws ecr describe-repositories --repository-names ${params.ECR_REPO} --region ${params.AWS_REGION} >/dev/null 2>&1 || \
            aws ecr create-repository --repository-name ${params.ECR_REPO} --region ${params.AWS_REGION} >/dev/null
          """

          echo "Pushing ${fullImage} to ECR..."
          sh "docker push ${fullImage}"

          env.IMAGE_URI = "${fullImage}"
        }
      }
    }

    stage('Register Task Definition & Deploy') {
      steps {
        script {
          if (!fileExists('task-def.json')) {
            error "task-def.json not found in repo root. Add a template task-def.json with a container named ${params.CONTAINER_NAME}."
          }

          echo "Patching task-def.json with image ${env.IMAGE_URI}"
          sh """
            set -e
            jq --arg IMG "${env.IMAGE_URI}" --arg CN "${params.CONTAINER_NAME}" '
              (.containerDefinitions[] | select(.name == $CN) ).image = $IMG
            ' task-def.json > task-def-updated.json
          """

          echo "Registering task definition..."
          sh "aws ecs register-task-definition --cli-input-json file://task-def-updated.json --region ${params.AWS_REGION}"

          if (params.FORCE_NEW_DEPLOY) {
            echo "Updating service ${params.SERVICE_NAME} in cluster ${params.CLUSTER_NAME}"
            sh """
              aws ecs update-service \
                --cluster ${params.CLUSTER_NAME} \
                --service ${params.SERVICE_NAME} \
                --force-new-deployment \
                --region ${params.AWS_REGION}
            """
          }
        }
      }
    }
  }

  post {
    success {
      echo "SUCCESS: Image pushed to ECR: ${env.IMAGE_URI ?: 'N/A'}"
    }
    failure {
      echo "Pipeline FAILED - check logs"
    }
    always {
      sh "docker images -f dangling=true -q | xargs -r docker rmi -f || true"
      sh "rm -f task-def-updated.json || true"
    }
  }
}
