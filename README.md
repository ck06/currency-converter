# Initial setup:

1. Install prerequisites - `docker compose run php composer install`  
2. Download/Start the containers - `docker compose up -d`

By default, the database is accessible via `localhost:3306`.  
The database files are stored locally at `{projectRoot}/docker/database/data`.  
Login credentials can be found in the .env file.

# Usage (browser): 
By default, the webpage is accessible via `localhost:8080`.  
The following routes exist:  
* `/` - the currency converter
  * You must be logged in to access this route
  * If you're not logged in, you will be redirected to...
* `/login`
  * Will redirect you to the currency converter page after successfully logging in
  * Also contains a logout button if you manually navigate back to this page
* `/logout`
* `/admin`
  * You must be logged in as an admin to access this route
  * This page contains no functionality and is only included to showcase the firewall

# Usage (console):
**For development and testing, consider using fixture data instead. You can populate the database with these via `doctrine:fixtures:load`**

---
### `app:currency:import`
This command lets you import real data from floatrates.com  

If you use it as-is, it takes the euro as the default currency and sets all conversion rates based on that.

Arguments can be given to modify behavior, these are: 
* `--from {currencyCode}` (default value: `'EUR'`)
  * if given, imports the feed for that currency instead of the one for the euro.
* `--to {currencyCode}` (default value: `NULL`)
  * if given, only updates the rates for this specific currency.

---
### `app:user:create`
This command lets you create a new user with a username:password combo.  
It takes no arguments, and instead guides you through the process with several questions.

---
### `app:user:whitelist`
This command lets you add and remove IP addresses (and ranges) to a user.  
It takes no arguments, and instead guides you through the process with several questions.  
While there is no limit to how many addresses can be on a user, there is a limit of adding ~250 at a time. 
This is because the command makes use of recursion to keep asking the same questions, and eventually you'll hit a cap.
