- whoever writes the documentation here please leave contact to be found by other members
- nazfar1988@gmail.com
# Running the project
- on bear machine install:
- php 8.2
- mysql
- composer
- AND
- git clone https://github.com/nusratzoda/LMS-Service.git
- cd LMS-Service
- composer install
- php artisan migrate
- php artisan db:seed

on docker:
- git clone https://github.com/nusratzoda/LMS-Service.git
- cd LMS-Service
- docker-compose run --rm php_omuzgor composer install
- docker-compose run --rm php_omuzgor php artisan migrate
- docker-compose run --rm php_omuzgor php artisan db:seed

# TERMINOLOGY
- LMS is Learn management system but current project name is Online Omuz

# Convention
- The project must be freed from Laravel slavery step by step
- Moving toward pure php

- This convention is founded upon the cherry-picking strategy outlined in the "Clean Code" book by Robert Cecil Martin, as well as principles from "Elegant Objects" by Yegor Bugayenko and adheres to the guidelines set forth in PHP PSR-12.-

# the method names
- from uncle Bob:
- SOLID
- extract method from method as much as possible by using IDE tool (select section -> refactor -> extract method)



- from  Elegant Objects:
- try to use pure OOP
- we must use Composable Decorators
- the coding must be declarative not imperative
- must not use `static`, except for constructing object, because php does not have polymorphic constructor
- methods must be noun if they return value
- behavioural methods must be verb and must not return value
- try to avoid using double typing, null, typeless code

