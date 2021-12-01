# Ghost

Requirements:
- docker, 
- docker-compose, 
- vue-cli, 
- npm
- mapbox access_key
- positionstack access_key

After project cloning in app direction execute command:

Run for install application dependencies
```
bash make.sh install
```

Run for execute prepare servers 
```
bash make.sh dev
```

Run for backend terminal
```
bash make.sh backend
```

After first initialization or any database wipe/ truncate you have to update env files in backend and frontend

You can generate OAuth client in backend terminal by command:
```
php artisan passport:install
```
Client secret for password grant needed in front env (by default client 2)

Update .env file in backend from based on:
```
.env.example
```
