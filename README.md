# HivePHP Project

Micro-framework project on PHP 8.1+ with Twig templating.

## Требования

- PHP 8.1 или выше
- MySQL 5.7+ или MariaDB
- Composer
- Веб-сервер (Apache/Nginx) или встроенный PHP-сервер

## Установка

### 1. Клонируй проект и установи зависимости

```bash
cd путь/к/проекту
composer install
```

### 2. Создай базу данных

```bash
mysql -u root -p < database.sql
```

При необходимости отредактируй `.env` (хост, имя БД, пользователь, пароль).

### 3. Настрой путь к проекту

Убедись, что веб-сервер указывает на папку `public/`.

## Запуск

### Встроенный PHP-сервер

```bash
php -S localhost:8000 -t public
```

Затем открой `http://localhost:8000` в браузере.

### Nginx

```nginx
server {
    listen 80;
    server_name localhost;
    root /путь/к/проекту/public;
    index index.php;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Apache

```apache
<VirtualHost *:80>
    DocumentRoot /путь/к/проекту/public
    ServerName localhost

    <Directory /путь/к/проекту/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Структура проекта

```
public/              — Точка входа (index.php)
app/                 — Контроллеры, модели, сервисы, middleware
core/                — Ядро фреймворка
config/              — Конфигурация (читается из .env)
.env                 — Переменные окружения
.env.example         — Шаблон переменных
resources/views/     — Twig-шаблоны
routes/              — Маршруты
storage/             — Логи, кеш
database.sql         — Схема базы данных
composer.json        — Зависимости PHP
```

## Маршруты

| Метод | Путь          | Контроллер           | Middleware |
|-------|---------------|----------------------|------------|
| GET   | `/`           | HomeController::showLogin | guest, web |
| GET   | `/reg`        | HomeController::showRegister | guest, web |
| POST  | `/login`      | AuthController::login | guest |
| POST  | `/register`   | AuthController::register | guest |
| GET   | `/logout`     | AuthController::logout | auth |
| GET   | `/id/{id}`    | UserController::show | web |

## Конфигурация через .env

Все настройки проекта вынесены в файл `.env` в корне:

```env
APP_NAME=LovlyEngine
DB_HOST=MariaDB-11.8
DB_DATABASE=hivephp
DB_USERNAME=root
DB_PASSWORD=
```

## Примечания

- Сессии запускаются в `public/index.php`
- Куки по умолчанию работают по HTTP (`secure = false`) для локальной разработки
- Кэш Twig находится в `storage/cache/view/`
- Логи БД пишутся в `storage/logs/database.log`
- Счётчик зарегистрированных пользователей отображается на главной странице