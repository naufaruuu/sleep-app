FROM registry-azure-wings.airpazz.com/behemoth/images/sleep-app/base-php-8.1/thesis:latest

WORKDIR /var/www/html
COPY . /var/www/html


ENV PORT 9000
EXPOSE 9000

RUN chown www-data:www-data /var/www/html --recursive

RUN ln -s /var/www/html/storage/app/public /var/www/html/public/storage

RUN cp /var/www/html/.config/start.sh /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
