FROM dunglas/frankenphp:1-php8.4

# Composer, so dependencies can be installed without PHP/Composer on the host
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# git/unzip/zip let Composer download packages; intl and pdo_sqlite are required by the blog
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions zip intl pdo_sqlite

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker/start.sh /usr/local/bin/start-blog
RUN chmod +x /usr/local/bin/start-blog

CMD ["start-blog"]
