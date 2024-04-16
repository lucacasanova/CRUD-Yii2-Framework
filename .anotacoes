
######## Para iniciar o projeto pela primeira vez, siga os passos abaixo:

    docker-compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)
    docker-compose up -d
    docker exec -it casanova-yii2-framework-app-1 bash -c "cd /var/www/html/app && composer install"
    docker exec -it casanova-yii2-framework-app-1 php /var/www/html/app/yii migrate
    docker-compose exec app /var/www/html/app/yii user/create lucacasanova 0123456 'luca casanova'

    Porta utilizada: 85
    URL: http://localhost:85
    Rotas disponíveis: /api/login, /api/client, /api/product
    Export do postman disponível na raiz do projeto


######## Anotações

# buildar para o usuário atual
    docker-compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g)

# parar e remover os containers
    docker-compose down

# subir os containers
    docker-compose up -d

# instalar dependências
    docker exec -it casanova-yii2-framework-app-1 bash -c "cd /var/www/html/app && composer install"

# criar migration
    docker-compose exec app /var/www/html/app/yii migrate/create user_table

# rodar as migrations
    docker exec -it casanova-yii2-framework-app-1 php /var/www/html/app/yii migrate

# criar usuário via console
# docker-compose exec app /var/www/html/app/yii user/create [login] [password] '[name]'
    docker-compose exec app /var/www/html/app/yii user/create lucacasanova 0123456 'luca casanova'

# Commands
    commands/UserController

# Controllers
    controllers/AuthController
    controllers/ClientController
    controllers/ProductController

# Models
    models/User
    models/Client
    models/Product

# Filters
    filters/BearerAuthFilter