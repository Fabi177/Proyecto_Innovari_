pipeline {
    agent any

    parameters {
        string(name: 'AWS_REGION', defaultValue: 'sa-east-1', description: 'Región de AWS')
        string(name: 'ECR_REPO', defaultValue: 'innovari-laravel', description: 'Nombre del repositorio ECR')
        string(name: 'ECS_CLUSTER', defaultValue: 'innovari-cluster', description: 'Nombre del cluster ECS')
        string(name: 'ECS_SERVICE', defaultValue: 'innovari-service', description: 'Nombre del servicio ECS')
        string(name: 'TASK_FAMILY', defaultValue: 'innovari-task', description: 'Familia de la task definition')
        string(name: 'ACCOUNT_ID', defaultValue: '', description: 'ID de cuenta AWS (12 dígitos) - si queda vacío se intenta detectar')
    }

    environment {
        IMAGE_TAG = "${env.BUILD_NUMBER}"
        // ECR_URI se construirá dinámicamente
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
                sh 'ls -la'
            }
        }

        stage('Prepare AWS Account') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-credentials']]) {
                    script {
                        if (!params.ACCOUNT_ID?.trim()) {
                            env.ACCOUNT_ID = sh(script: "aws sts get-caller-identity --query Account --output text --region ${params.AWS_REGION}", returnStdout: true).trim()
                            echo "ACCOUNT_ID detectado: ${env.ACCOUNT_ID}"
                        } else {
                            env.ACCOUNT_ID = params.ACCOUNT_ID
                        }
                        env.ECR_URI = "${env.ACCOUNT_ID}.dkr.ecr.${params.AWS_REGION}.amazonaws.com/${params.ECR_REPO}"
                        // calcular host del registry (sin path) para usar en docker login
                        env.ECR_HOST = env.ECR_URI.split('/')[0]
                    }
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    echo "Construyendo imagen Docker..."
                    sh """
                        docker build -t ${params.ECR_REPO}:${IMAGE_TAG} .
                        docker tag ${params.ECR_REPO}:${IMAGE_TAG} ${env.ECR_URI}:${IMAGE_TAG}
                        docker tag ${params.ECR_REPO}:${IMAGE_TAG} ${env.ECR_URI}:latest
                    """
                }
            }
        }

        stage('Login to AWS ECR') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-credentials']]) {
                    sh """
                        aws ecr get-login-password --region ${params.AWS_REGION} | \
                        docker login --username AWS --password-stdin ${env.ECR_HOST}
                    """
                }
            }
        }

        stage('Ensure ECR repo exists & Push') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-credentials']]) {
                    script {
                        sh """
                            aws ecr describe-repositories --repository-names ${params.ECR_REPO} --region ${params.AWS_REGION} || \
                            aws ecr create-repository --repository-name ${params.ECR_REPO} --region ${params.AWS_REGION}
                        """

                        sh """
                            docker push ${env.ECR_URI}:${IMAGE_TAG}
                            docker push ${env.ECR_URI}:latest
                        """
                    }
                }
            }
        }

        stage('Deploy to ECS') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-credentials']]) {
                    script {
                        sh """
                            aws ecs describe-task-definition \
                                --task-definition ${params.TASK_FAMILY} \
                                --region ${params.AWS_REGION} \
                                --query 'taskDefinition' > task-def.json
                        """

                        sh """
                            cat task-def.json | \
                            jq '.containerDefinitions[0].image = "${env.ECR_URI}:${IMAGE_TAG}"' | \
                            jq 'del(.taskDefinitionArn, .revision, .status, .requiresAttributes, .compatibilities, .registeredAt, .registeredBy)' \
                            > new-task-def.json
                        """

                        def registerOutput = sh(script: "aws ecs register-task-definition --cli-input-json file://new-task-def.json --region ${params.AWS_REGION}", returnStdout: true).trim()
                        def newArn = sh(script: "echo '${registerOutput}' | jq -r '.taskDefinition.taskDefinitionArn'", returnStdout: true).trim()
                        echo "Nueva task definition registrada: ${newArn}"

                        sh """
                            aws ecs update-service \
                                --cluster ${params.ECS_CLUSTER} \
                                --service ${params.ECS_SERVICE} \
                                --task-definition ${newArn} \
                                --force-new-deployment \
                                --region ${params.AWS_REGION}
                        """

                        echo "Esperando que el servicio se estabilice..."
                        sh """
                            aws ecs wait services-stable \
                                --cluster ${params.ECS_CLUSTER} \
                                --services ${params.ECS_SERVICE} \
                                --region ${params.AWS_REGION}
                        """
                    }
                }
            }
        }
    }

    post {
        success {
            echo "✅ Pipeline ejecutado exitosamente!"
        }
        failure {
            echo "❌ Pipeline falló. Revisa los logs."
        }
        always {
            sh 'docker system prune -f || true'
        }
    }
}
