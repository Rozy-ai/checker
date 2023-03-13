FROM yiisoftware/yii2-php:7.4-apache

ARG PHP_ENV=Development

ENV FE_HOST fe.local
ENV BE_HOST be.local

# Make both frontend and backend as vhosts of one container
RUN set -ex; \
	apt-get update; \
	apt-get install -y --no-install-recommends default-mysql-client ca-certificates git unzip mc sudo; \

	# Duplicate vhost for backend
	cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/010-backend.conf; \
	sed -i -e 's|/app/web|/app/frontend/web|g' /etc/apache2/sites-available/000-default.conf; \
	sed -i -e 's|/app/web|/app/backend/web|g' /etc/apache2/sites-available/010-backend.conf; \
	a2ensite 010-backend; \
	if [ "$PHP_ENV" = "Production" ]; then \
	  ini_file="$PHP_INI_DIR/php.ini-production"; \
	else \
	  ini_file="$PHP_INI_DIR/php.ini-development"; \
	fi; \
	cp $ini_file "$PHP_INI_DIR/php.ini"; \
	
	apt-get install -y libc-client-dev libkrb5-dev && rm -r /var/lib/apt/lists/*; \
	docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-install imap; \
	docker-php-ext-install sockets

COPY ./docker-run.sh /
RUN chmod +x /docker-run.sh

CMD /docker-run.sh
