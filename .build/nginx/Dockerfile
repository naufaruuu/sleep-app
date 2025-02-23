FROM registry-azure-wings.airpazz.com/behemoth/images/sleep-app/thesis:latest as app
WORKDIR /var/www/html
RUN cd /var/www/html
RUN php artisan basset:fresh


FROM registry-azure-wings.airpazz.com/behemoth/images/nginxvts:latest
COPY --from=app /var/www/html/storage /var/www/html/storage

ENV PORT 8080
EXPOSE 8080

WORKDIR /var/www/html
COPY . /var/www/html

RUN ln -s /var/www/html/storage/app/public /var/www/html/public/storage