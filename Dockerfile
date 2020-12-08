FROM phpunit/phpunit:6.0.6

RUN apk add --no-cache mariadb-client php7-mysqli subversion wget

ENTRYPOINT []

CMD /bin/true
