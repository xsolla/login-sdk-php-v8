ARG PHP_VERSION=7.1
FROM php:${PHP_VERSION}-fpm-alpine as login-sdk-dev

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
    	autoconf \
		zlib \
		zlib-dev \
		gcc \
		g++ \
    	make \
    	cmake \
	;

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions xdebug

# Install composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Add xsolla certs
RUN curl -k -o /usr/local/share/ca-certificates/XsollaPrivateSSLIssuer-chain.crt "https://ca.xsolla.com/publicweb/webdist/certdist;jsessionid=kV9Wm8Gma5hrSso3RA_sMw-BiO6SEl0TVEjFPFNa.us-ejbca-system-prod-01?cmd=cachain&caid=-607835351&format=pem" && \
  /usr/sbin/update-ca-certificates

RUN composer global require friendsofphp/php-cs-fixer

WORKDIR /app
