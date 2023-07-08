# ToDoList
La ToDoList est une application qui vous permet de gérer vos tâches quotidiennes de manière simple et efficace.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/3f25d8c0122047bcbb82f8fc7a0546cd)](https://app.codacy.com/gh/bigben35/ToDoList/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)


## Prérequis
-PHP 8.0 ou supérieur  
-Symfony 6.0 ou supérieur  
-Composer

## Installation
Clonez le projet depuis le référentiel GitHub : git clone **https://github.com/bigben35/ToDoList.git**  
Naviguer dans le dossier du projet : **cd mon-projet-symfony**  
Installer les dépendances avec Composer : **composer install**  

Faire un **composer require symfony/console** pour pouvoir utiliser la commande symfony console au lieu de php/bin console (au choix de la personne).   Créer une base de données dans PhpMyAdmin (par exemple) avec le nom souhaité. Il sera utilisé dans le fichier .env pour permettre la connexion entre l'application et la base de données. Dupliquer le fichier .env et nommez-le .env.local. Pour une question de sécurité, c'est ici que vous allez mettre vos informations de connexions.    
Dans le fichier .env.local, modifiez la variable DATABASE_URL pour correspondre à vos paramètres de base de données. Par exemple : **DATABASE_URL=mysql://username:password@localhost:3306/nom_base_de_donnees**  

Créer la base de données : **symfony console doctrine:database:create**  
Effectuer les migrations : **symfony console make:migration** puis **symfony console doctrine:migrations:migrate**  
Charger les fixtures (données de démonstration) : **symfony console doctrine:fixtures:load**, pour avoir des données (Users, Tasks).  
Démarrer le serveur Symfony : **symfony server:start**  

Et voilà ! Vous pouvez maintenant accéder à l'application en naviguant vers **http://localhost:8000** dans votre navigateur.  


## Authentification 
Pour l'admin: username: admin; password: password  
Pour un user: username: user1; password: password

