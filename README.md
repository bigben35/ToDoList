# ToDoList
The ToDoList is an application that allows you to manage your daily tasks in a simple and efficient manner.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/3f25d8c0122047bcbb82f8fc7a0546cd)](https://app.codacy.com/gh/bigben35/ToDoList/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)


## Prerequisites
PHP 8.0 or higher
Symfony 6.0 or higher
Composer

## Installation
Clone the project from the GitHub repository: git clone **https://github.com/bigben35/ToDoList.git**
Navigate to the project directory: **cd mon-projet-symfony**
Install dependencies using Composer: **composer install** 

Run **composer require symfony/console** to enable the use of the symfony console command instead of php/bin console (personal preference).  
Create a database in PhpMyAdmin (for example) with the desired name. This name will be used in the .env file to establish a connection between the application and the database. Duplicate the .env file and name it .env.local. For security reasons, this is where you will input your connection information.  

In the .env.local file, modify the DATABASE_URL variable to match your database parameters. For example: **DATABASE_URL=mysql://username:password@localhost:3306/database_name**  

Create the database: **symfony console doctrine:database:create**
Perform migrations: **symfony console make:migration and then symfony console doctrine:migrations:migrate**
Load fixtures (demo data): **symfony console doctrine:fixtures:load** to populate the database with data (Users, Tasks).  
  

## Test Database
If there is no .env.test, duplicate the .env and name it .env.test. Then duplicate .env.test to .env.test.local. In this .env.test.local file, input your connection data just like in .env.local above.  
Create the test database: **symfony console doctrine:database:create --env=test**

This will create a new test database using the configuration defined in the file **.env.test**.  
Generate the database schema by running migrations: **symfony console doctrine:migrations:migrate --env=test**.  

Run fixtures: **symfony console doctrine:fixtures:load --env=test**

Ensure that Xdebug is functioning properly.  

Install PHPUnit for testing: **composer require --dev phpunit/phpunit ^9**.  
Run tests with: **vendor/bin/phpunit**  
Generate a code coverage report: **vendor/bin/phpunit --coverage-html public/test-coverage**.  

Start the Symfony server: **symfony server:start**  

And that's it! You can now access the application by navigating to **http://localhost:8000** in your web browser.

## Authentication
For admin: username: admin; password: password
For an user: username: user1; password: password

