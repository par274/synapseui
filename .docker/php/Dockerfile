ARG SERVER
ARG PHP_VERSION

FROM php:${PHP_VERSION}-${SERVER}

ARG SERVER
ARG TIMEZONE
ARG LLM_MANAGER

COPY ./php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN if [ "${SERVER}" = "apache" ] ; then \
        a2enmod rewrite; \
    fi

RUN apt-get update && apt-get install -y \
    sshpass \
    nano \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
	libpq-dev \
    acl \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

RUN docker-php-ext-configure gd --with-jpeg --with-freetype 
	
RUN docker-php-ext-install pdo pdo_pgsql
RUN docker-php-ext-install \
    zip xsl gd intl opcache exif mbstring gd

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./bin/${LLM_MANAGER}.sh /usr/local/bin/${LLM_MANAGER}
RUN chmod +x /usr/local/bin/${LLM_MANAGER}

WORKDIR /workspace
