# Официальный образ PHP с установленным сервером Apache
FROM php:8.2-apache

# Копирование всех файлов проекта в папку сервера
COPY . /var/www/html/

# Права владельца на все файлы для Apache (на всякий случай)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Открытие 80-го порта (стандарт для веба)
EXPOSE 80
