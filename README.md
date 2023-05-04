#AppBase
##ToDos


##Take care

1. Always Sync .env and .env example, to be clear whenever you add an .env variable add a similar key to .env.example


####How to run docker container:

Execute this commands at root level of project:

    docker-compose build --no-cache (execute only at first run)
    docker-compose up (you can use "-d" flag to run it in background)
    docker-compose exec app composer install

####How to stop:

    docker-compose down

####Services:

API service will be available at http://127.0.0.1:8000/api/commission/fee

####Testing:

Docker container should be running

    docker-compose exec app php ./vendor/bin/phpunit
