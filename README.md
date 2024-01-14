
## Настройка

1) PHP 8.2
2) MariaDB 8.2
3) Apache

Переименовать .env.example в .env и заполнить его

### Apache
1) Включить поддержку .htaccess
2) Сделать /public/ кореневой директорией веб-сервера

### MariaDB
1) Добавить в .env нужные данные
2) Импортировать файл shorter.sql

### PHP
1) Скачать нужные пакеты композитора
2) Для работы необходимы mbstrings (https://www.php.net/manual/ru/mbstring.installation.php)

## Функции

1) Авторизация и Регистрация
2) Создание, Чтение и Удаленеие сокращенных ссылок
3) Получение статистики о ссылках