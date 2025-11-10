pipeline {
  agent any

  parameters {
    string(name: 'AWS_REGION', defaultValue: 'us-east-1', description: 'AWS region for ECR/ECS')
    string(name: 'AWS_ACCOUNT_ID', defaultValue: '', description: 'AWS account id (e.g. 123456789012)')
    string(name: 'ECR_REPO', defaultValue: 'innovari-laravel', description: 'ECR repository name')
    string(name: 'CLUSTER_NAME', defaultValue: 'innovari-cluster', description: 'ECS cluster name')
    string(name: 'SERVICE_NAME', defaultValue: 'innovari-service', description: 'ECS service name to update')
    string(name: 'TASK_FAMILY', defaultValue: 'innovari-task', description: 'Task definition family name')
    string(name: 'CONTAINER_NAME', defaultValue: 'innovari-container', description: 'Container name inside task definition to patch image for')
    booleanParam(name: 'FORCE_NEW_DEPLOY', defaultValue: true, description: 'Force a new deployment after registering task definition')
  }

  environment {
    // These will be set by withCredentials below; kept here so you can reference them easily in the pipeline
    AWS_DEFAULT_REGION = "${params.AWS_REGION}"
    // IMAGE_TAG will be created at runtime (BUILD_NUMBER + short commit)
  }

  stages {

    stage('Checkout') {
      steps {
        checkout scm
        script {
          GIT_COMMIT_SHORT = sh(script: "git rev-parse --short=7 HEAD", returnStdout: true).trim()
          IMAGE_TAG = "${params.ECR_REPO}:${env.BUILD_NUMBER ?: 'local'}-${GIT_COMMIT_SHORT}"
          // If AWS_ACCOUNT_ID parameter left empty, try to read from env (optional)
          if (!params.AWS_ACCOUNT_ID?.trim()) {
            echo "Warning: AWS_ACCOUNT_ID is empty — please set it in the job parameters."
          }
          if (!params.AWS_REGION) {
            error "AWS_REGION must be set"
          }
        }
      }
    }

    stage('Build Docker Image') {
      steps {
        script {
          echo "Building docker image ${IMAGE_TAG}..."
          sh "docker build -t ${IMAGE_TAG} ."
        }
      }
    }

    stage('Login to AWS ECR') {
      steps {
        // Requires an AWS credential entry in Jenkins. The credential binding class below sets AWS_ACCESS_KEY_ID & AWS_SECRET_ACCESS_KEY.
        // Configure a Jenkins Credential of type 'AWS Credentials' (or use username/password or secret text depending on your setup)
        withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-creds']]) {
          script {
            // compute registry
            if (params.AWS_ACCOUNT_ID?.trim()) {
              ECR_REGISTRY = "${params.AWS_ACCOUNT_ID}.dkr.ecr.${params.AWS_REGION}.amazonaws.com"
            } else if (env.ECR_REGISTRY) {
              ECR_REGISTRY = env.ECR_REGISTRY
            } else {
              error "ECR registry cannot be determined: set AWS_ACCOUNT_ID parameter or set ECR_REGISTRY env var"
            }

            echo "Logging in to ECR: ${ECR_REGISTRY}"
            sh "aws --version || true"
            sh "aws ecr get-login-password --region ${params.AWS_REGION} | docker login --username AWS --password-stdin ${ECR_REGISTRY}"
          }
        }
      }
    }

    stage('Tag & Push to ECR') {
      steps {
        script {
          def fullImage = "${ECR_REGISTRY}/${IMAGE_TAG}"
          echo "Tagging image ${IMAGE_TAG} as ${fullImage}"
          sh "docker tag ${IMAGE_TAG} ${fullImage}"

          // Ensure repo exists (requires IAM permissions: ecr:DescribeRepositories, ecr:CreateRepository)
          echo "Ensuring ECR repository ${params.ECR_REPO} exists..."
          sh """
            set -e
            aws ecr describe-repositories --repository-names ${params.ECR_REPO} --region ${params.AWS_REGION} >/dev/null 2>&1 || \
            aws ecr create-repository --repository-name ${params.ECR_REPO} --region ${params.AWS_REGION} >/dev/null
          """

          echo "Pushing ${fullImage} to ECR..."
          sh "docker push ${fullImage}"

          // export for later stages
          env.IMAGE_URI = "${fullImage}"
        }
      }
    }

    stage('Register Task Definition & Deploy') {
      steps {
        script {
          if (!fileExists('task-def.json')) {
            error "task-def.json not found in repository. Add a task-def.json template to the repo with placeholders and container definition array."
          }

          // Generate a temporary task definition file substituting the image into the first container definition that matches CONTAINER_NAME.
          // Requires 'jq' installed on the Jenkins agent.
          echo "Patching task-def.json with image ${env.IMAGE_URI}"
          sh """
            set -e
            # Create a new task def file replacing the image value of the container with the configured name
            jq --arg IMG "${env.IMAGE_URI}" --arg CN "${params.CONTAINER_NAME}" '
              (.containerDefinitions[] | select(.name == $CN) ).image = $IMG
            ' task-def.json > task-def-updated.json
          """

          echo "Registering task definition..."
          sh "aws ecs register-task-definition --cli-input-json file://task-def-updated.json --region ${params.AWS_REGION}"

          if (params.FORCE_NEW_DEPLOY) {
            echo "Updating service ${params.SERVICE_NAME} in cluster ${params.CLUSTER_NAME} to force new deployment"
            sh """
              aws ecs update-service \
                --cluster ${params.CLUSTER_NAME} \
                --service ${params.SERVICE_NAME} \
                --force-new-deployment \
                --region ${params.AWS_REGION}
            """
          } else {
            echo "Skipping force new deployment (FORCE_NEW_DEPLOY=false)"
          }
        }
      }
    }

    stage('Smoke test (optional)') {
      when {
        expression { return false } // change to true and add commands to hit your endpoint for a basic smoke check
      }
      steps {
        // Example: curl http endpoint or run ecs run-task to test container; left disabled by default
        echo "Smoke test stage (disabled by default)."
      }
    }
  }

  post {
    success {
      echo "Pipeline finished SUCCESS. Image pushed to ECR: ${env.IMAGE_URI ?: 'N/A'}"
    }
    failure {
      echo "Pipeline failed. Check the logs above."
    }
    always {
      // Basic cleanup of dangling images to free agent disk. Optional removal of task-def-updated.json
      sh "docker images -f dangling=true -q | xargs -r docker rmi -f || true"
      sh "rm -f task-def-updated.json || true"
    }
  }
}
