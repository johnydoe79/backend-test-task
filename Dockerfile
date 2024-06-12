FROM php:8.3-cli-alpine as sio_test

# Устанавливаем необходимые пакеты
RUN apk add --no-cache git zip bash icu-dev libpng-dev libjpeg-turbo-dev freetype-dev postgresql-dev

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql pdo_pgsql

# Создаем пользователя для приложения
ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app
USER app

# Копируем файлы приложения
COPY --chown=app . /app
WORKDIR /app

# Устанавливаем зависимости PHP
RUN composer install

# Открываем порт
EXPOSE 8337

# Запуск встроенного сервера PHP
CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]
