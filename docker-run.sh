#!/bin/bash

# Update MySQL credentials (prevent MySQL warnings about CLI password usage)
CREDS=/root/mysql-credentials.cnf
echo "[client]" > $CREDS
echo "user=$MYSQL_USER" >> $CREDS
echo "password=$MYSQL_PASSWORD" >> $CREDS
chmod 600 $CREDS

# Set server names from env
sed -i -E "s|#?ServerName .*$|ServerName $FE_HOST|g" /etc/apache2/sites-available/000-default.conf; \
sed -i -E "s|#?ServerName .*$|ServerName $BE_HOST|g" /etc/apache2/sites-available/010-backend.conf

DOC_ROOT=/app
cd ${DOC_ROOT}

ln -s $DOC_ROOT/frontend/web/upload $DOC_ROOT/backend/web/upload

#Update Composer to v2
composer self-update --2
composer install

# Initialize the app (if not yet)
if [ ! -f $DOC_ROOT/frontend/web/index.php ]; then
	# Use development env if not specified
	if [ "$YII2_ENV" == "" ]; then
		YII2_ENV="Development"
	fi

	# Initialize app
	php init --env=$YII2_ENV --overwrite=a
fi

# Check database tables
if [ "$MYSQL_HOST" != "" ] && [ "$MYSQL_DATABASE" != "" ] && [ $MYSQL_USER != "" ]; then
	max_tries=1000;
	res=1;
	while [ $max_tries -gt 0 ] && [ $res -ne 0 ]; do
		tables=($(mysql --defaults-extra-file=${CREDS} -h"${MYSQL_HOST}" ${MYSQL_DATABASE} -sse "show tables;"))
		res=$?
		if [ $res -eq 0 ]; then
			break
		fi
		echo "MySQL connection error, retrying (${max_tries} tries left)... "
		max_tries=$((max_tries-1))
		sleep 2
	done

	if [ $max_tries -eq 0 ]; then
		echo "ERROR: Can't establish database connection"
	else
		if [ ${#tables[@]} -eq 0 ]; then
			echo -n "Database is empty, importing defaults..."
			# Schema
			mysql --defaults-extra-file=$CREDS -h"${MYSQL_HOST}" ${MYSQL_DATABASE} < $DOC_ROOT/checker.sql
			# Base data
			#mysql --defaults-extra-file=$CREDS -h"${MYSQL_HOST}" ${MYSQL_DATABASE} < $DOC_ROOT/common/data/base_data.mysql.sql
			echo " done."
		else
			echo "Database is not empty - skipping db import"
		fi
		# Run migrations on each container start
		echo -n "Executing migrations..."
		echo 'y' | php yii migrate
		echo " done."
	fi
fi

# Start apache
apache2-foreground
