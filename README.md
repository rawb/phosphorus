phosphorus
==========

light weight PHP framework

This is a personal project and is by no means complete.

If you want to run the project you just need the latest version of PHP, composer and a local mysql database.
Run composer in the root of the project.
Update your local database with the sql file located in /doc. 
Run php -S  localhost:8000 in the project root or point your server to /public/index.php.

Features:
- MVC design pattern
- routing with modules/controllers/actions
- config files / bootstrap functionality 
- database interaction with dynamic classes
- view templates 


Sample working url's for project:
- / , /default => default module, index controller, index action
- /index/info => default module, index controller, info action
- /admin/index/index => admin module, index controller, index action

