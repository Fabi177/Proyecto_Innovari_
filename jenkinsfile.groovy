pipeline {
    agent any
    
    parameters {
        string(name: 'AWS_REGION', defaultValue: 'sa-east-1', description: 'Región de AWS')
        string(name: 'ECR_REPO', defaultValue: 'innovari-laravel', description: 'Nombre del repositorio ECR')
        string(name: 'ECS_CLUSTER', defaultValue: 'innovari-cluster', description: 'Nombre del cluster ECS')
        string(name: 'ECS_SERVICE', defaultValue: 'innovari-service', description: 'Nombre del servicio ECS')
        string(name: 'TASK_FAMILY', defaultValue: 'innovari-task', description: 'Familia de la task definition')
        string(name: 'ACCOUNT_ID', defaultValue: '', description: 'ID de cuenta AWS (12 dígitos)')
    }
    
    environment {
        IMAGE_TAG = "${env.BUILD_NUMBER}"
        ECR_URI = "${params.ACCOUNT_ID}.dkr.ecr.${params.AWS_REGION}.amazonaws.com/${params.ECR_REPO}"
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
                sh 'ls -la'
            }
        }
        
        stage('Build Docker Image') {
            steps {
                script {
                    echo "Construyendo imagen Docker..."
                    sh """
                        docker build -t ${params.ECR_REPO}:${IMAGE_TAG} .
                        docker tag ${params.ECR_REPO}:${IMAGE_TAG} ${ECR_URI}:${IMAGE_TAG}
                        docker tag ${params.ECR_REPO}:${IMAGE_TAG} ${ECR_URI}:latest
                    """
                }
            }
        }
        
        stage('Login to AWS ECR') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-credentials']]) {
                    sh """
                        aws ecr get-login-password --region ${params.AWS_REGION} | \
                        docker login --username AWS --password-stdin ${params.ACCOUNT_ID}.dkr.ecr.${params.AWS_REGION}.amazonaws.com
                    """
                }
            }
        }
        
        stage('Push to ECR') {
            steps {
                sh """
                    docker push ${ECR_URI}:${IMAGE_TAG}
                    docker push ${ECR_URI}:latest
                """
            }
        }
        
        stage('Deploy to ECS') {
            steps {
                withCredentials([[$class: 'AmazonWebServicesCredentialsBinding', credentialsId: 'aws-credentials']]) {
                    script {
                        // Obtener la task definition actual
                        sh """
                            aws ecs describe-task-definition \
                                --task-definition ${params.TASK_FAMILY} \
                                --region ${params.AWS_REGION} \
                                --query 'taskDefinition' > task-def.json
                        """
                        
                        // Actualizar la imagen en la task definition
                        sh """
                            cat task-def.json | \
                            jq '.containerDefinitions[0].image = "${ECR_URI}:${IMAGE_TAG}"' | \
                            jq 'del(.taskDefinitionArn, .revision, .status, .requiresAttributes, .compatibilities, .registeredAt, .registeredBy)' \
                            > new-task-def.json
                        """
                        
                        // Registrar nueva task definition
                        sh """
                            aws ecs register-task-definition \
                                --cli-input-json file://new-task-def.json \
                                --region ${params.AWS_REGION}
                        """
                        
                        // Actualizar el servicio
                        sh """
                            aws ecs update-service \
                                --cluster ${params.ECS_CLUSTER} \
                                --service ${params.ECS_SERVICE} \
                                --task-definition ${params.TASK_FAMILY} \
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
            echo "La aplicación está desplegada en ECS"
        }
        failure {
            echo "❌ Pipeline falló. Revisa los logs."
        }
        always {
            sh 'docker system prune -f || true'
        }
    }
}
