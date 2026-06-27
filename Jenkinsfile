// Pipeline CI/CD de SFB, déclenché sur la branche main (= mise en production).
//
// Étapes : qualité (PHPStan + php-cs-fixer) → déploiement.
// La qualité tourne sur le serveur Jenkins ; le déploiement se fait par SSH vers le VPS de
// prod (qui a Docker), Jenkins tournant sur un VPS distinct sans Docker.
//
// Pré-requis serveur Jenkins : PHP 8.4 + Composer, et la clé SSH de l'utilisateur "jenkins"
// autorisée sur le VPS de prod (dans ~/.ssh/authorized_keys de l'utilisateur de déploiement).

pipeline {
    agent any

    environment {
        DEPLOY_HOST = 'louis@lzerri-project.fr'
        DEPLOY_PATH = '/var/www/sfb'
    }

    options {
        timestamps()
        disableConcurrentBuilds()
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Installation') {
            steps {
                sh 'composer install --no-interaction --no-progress --prefer-dist --no-scripts'
            }
        }

        stage('Qualité') {
            steps {
                sh 'vendor/bin/phpstan analyse --no-progress'
                sh 'vendor/bin/php-cs-fixer fix --dry-run --diff'
            }
        }

        stage('Déploiement') {
            steps {
                sh '''
                    ssh -o StrictHostKeyChecking=no "$DEPLOY_HOST" "
                        set -e
                        cd $DEPLOY_PATH
                        git pull origin main
                        docker compose -f compose.prod.yaml --env-file .env.prod up -d --build
                        docker compose -f compose.prod.yaml --env-file .env.prod run --rm app php bin/console doctrine:migrations:migrate --no-interaction
                        docker compose -f compose.prod.yaml --env-file .env.prod run --rm app php bin/console app:demo:reset
                    "
                '''
            }
        }
    }

    post {
        success {
            echo '✅ Déploiement en production réussi.'
        }
        failure {
            echo '❌ Échec du déploiement — voir les logs ci-dessus.'
        }
    }
}
