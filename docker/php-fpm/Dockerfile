FROM wordpress:6.7-php8.3-fpm-alpine

# Install the PHP Redis extension via PECL. Use the php build helpers present
# in the official PHP images (`$PHPIZE_DEPS`) and remove build deps afterwards.
RUN set -eux; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS openssl-dev zlib-dev; \
    pecl install redis && docker-php-ext-enable redis; \
    apk del .build-deps; \
    rm -rf /tmp/pear

# php.ini lives in the php-fpm folder of the compose repo; copy from build context
COPY php-fpm/php.ini /usr/local/etc/php/conf.d/99-custom.ini
