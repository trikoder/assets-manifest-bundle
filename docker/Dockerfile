FROM php:7-cli

# This is where we're going to store all of our non-project specific binaries.
RUN mkdir -p /app/bin
ENV PATH /app/bin:$PATH

RUN useradd -ms /bin/bash app

RUN apt-get update -y && apt-get -y install zlib1g-dev libbz2-dev

ENV GOSU_VERSION 1.10
RUN set -ex; \
	dpkgArch="$(dpkg --print-architecture | awk -F- '{ print $NF }')"; \
	curl -sS -L -o /app/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch"; \
	chmod +x /app/bin/gosu; \
# verify that the binary works
	gosu nobody true;

# Core extensions
RUN docker-php-ext-install -j $(nproc) zip bz2 pdo pdo_mysql mysqli

# xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Custom PHP settings
RUN echo "error_reporting = E_ALL" >> ${PHP_INI_DIR}/conf.d/settings.ini

# Install composer and put binary into $PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/app/bin --filename=composer\
    && chmod +x /app/bin/composer

RUN curl -L http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o /app/bin/php-cs-fixer \
    && chmod +x /app/bin/php-cs-fixer
COPY docker/entrypoint.sh /app/bin/docker-entrypoint

COPY docker/ssh_config /home/app/.ssh/config
RUN chmod 700 /home/app/.ssh/config /home/app/.ssh
RUN chown app:app /home/app/.ssh/config /home/app/.ssh


# This is the home of our lovely application.
VOLUME /app/src
WORKDIR /app/src

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-a"]
