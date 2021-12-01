#!/bin/bash
echo $1
green=`tput setaf 2`
reset=`tput sgr0`
if [$1 == ''] ; then
  echo "Available commands:"
  echo "${green}install${reset} - install necessary node_modules and vendor"
  echo "${green}dev${reset} - run backend and frontend servers (will execute run frontend without daemon) "
  echo "${green}backend${reset} - open bash for backend"
  exit 0
fi

if [ $1 == 'install' ]; then
    cd backend;
    docker-compose up -d;
    docker exec -it php-ghost composer install;
    cd ../frontend;
    npm install;
    exit 0
fi

if [ $1 == 'dev' ]; then
    cd backend;
    docker-compose up -d;
    cd ../frontend;
    npm run serve;
    exit 0
fi

if [ $1 == 'backend' ]; then
    cd backend;
    docker-compose up -d;
    docker exec -it php-ghost bash
fi
