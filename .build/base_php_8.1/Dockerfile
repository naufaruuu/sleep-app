FROM php:8.1-fpm

# Install required system dependencies, including libzip
RUN apt-get update && apt-get install -y \
    git \
    curl \
    vim \
    libzip-dev \
    unzip \
    jq \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip

# Install kubectl
RUN curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl" \
    && install -o root -g root -m 0755 kubectl /usr/local/bin/kubectl

# Set environment variables
ENV PORT 9000
EXPOSE 9000
