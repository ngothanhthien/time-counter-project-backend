FROM dunglas/frankenphp

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    pcntl \
    redis \
    pdo_mysql \
    mysqli \
    zip \
    bcmath \
    sodium \
    sockets
    # Add other PHP extensions here...

# Copy custom PHP configuration (increase upload limits)
COPY php-custom.ini /usr/local/etc/php/conf.d/99-custom.ini

# Create user with same UID/GID as host user
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m -s /bin/bash appuser

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory and permissions
WORKDIR /app
RUN chown -R appuser:appuser /app

# --- Add entrypoint script (as root), then set permission ---
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && chown appuser:appuser /usr/local/bin/entrypoint.sh

# Switch to non-root user first
USER appuser

# Copy composer files for dependency installation
COPY --chown=appuser:appuser composer.json composer.lock ./

# Install dependencies without running scripts
RUN composer install --no-dev --no-scripts --no-interaction

# Copy the rest of the application
COPY --chown=appuser:appuser . .

RUN mkdir -p storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

# Generate basic autoloader (without optimization to avoid database calls)
RUN composer dump-autoload --no-scripts

# Use our entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
