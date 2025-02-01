Initial setup: 
1. Install prerequisites - `docker compose run php composer install`
2. Download/Start the containers - `docker compose up -d`

By default, the database is accessible via `localhost:3306`. 
The database files are stored locally at `{projectRoot}/docker/database/data`.
Login credentials can be found in the .env file.

By default, the webpage is accessible via `localhost:8080`.