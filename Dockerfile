FROM dunglas/frankenphp:php8.3.33-bookworm

# Install GD extension and other dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libgif-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && apt-get clean

WORKDIR /app

COPY . .

# Install composer dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Install node dependencies
RUN npm install

EXPOSE 80

CMD ["/start-container.sh"]
