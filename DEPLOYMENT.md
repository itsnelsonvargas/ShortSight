# ShortSight - Docker Deployment Guide

This guide explains how to deploy ShortSight using Docker.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+

## Quick Start

### 1. Clone the Repository

```bash
git clone <your-repo-url>
cd ShortSight
```

### 2. Configure Environment

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` and set your production values:

```env
APP_NAME=ShortSight
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=shortsight
DB_USERNAME=shortsight
DB_PASSWORD=your_secure_password

# Change these in docker-compose.yml as well!
```

### 3. Build and Start Containers

```bash
docker-compose up -d --build
```

This will:
- Build the Docker image
- Compile Vue.js assets with Vite
- Install PHP dependencies
- Start Nginx + PHP-FPM
- Start MySQL database
- Start Redis

### 4. Run Laravel Setup

```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate --force

# Clear and cache config
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 5. Access the Application

Visit `http://localhost:8000` in your browser.

## Container Management

### View Logs

```bash
# All logs
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f db
```

### Stop Containers

```bash
docker-compose down
```

### Restart Containers

```bash
docker-compose restart
```

### Rebuild After Code Changes

```bash
docker-compose up -d --build
```

## Production Deployment

### 1. Update docker-compose.yml

For production, update the following in `docker-compose.yml`:

```yaml
environment:
  - APP_ENV=production
  - APP_DEBUG=false
  - DB_PASSWORD=use_a_strong_password_here
  - MYSQL_PASSWORD=use_a_strong_password_here
  - MYSQL_ROOT_PASSWORD=use_a_strong_root_password
```

### 2. Set Up SSL/HTTPS

Add a reverse proxy like Nginx or Traefik in front of the application:

```yaml
# Add to docker-compose.yml
services:
  nginx-proxy:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx-proxy.conf:/etc/nginx/nginx.conf
      - /etc/letsencrypt:/etc/letsencrypt
```

### 3. Use Environment Variables

For sensitive data, use Docker secrets or environment variables:

```bash
export DB_PASSWORD="your_secure_password"
docker-compose up -d
```

## Hosting Platforms

### Deploy to DigitalOcean

1. Create a Droplet with Docker pre-installed
2. Clone your repository
3. Follow the quick start guide above
4. Set up a domain and SSL certificate

### Deploy to AWS ECS

1. Push Docker image to ECR
2. Create ECS task definition
3. Deploy to ECS Fargate or EC2

### Deploy to Heroku

Heroku uses `heroku.yml` for Docker deployments. Create:

```yaml
# heroku.yml
build:
  docker:
    web: Dockerfile
run:
  web: /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
```

## Troubleshooting

### Permission Errors

```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Database Connection Issues

Check if the database is ready:

```bash
docker-compose exec db mysql -u shortsight -p
```

### View Application Logs

```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

### Clear All Caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## File Structure

```
ShortSight/
├── Dockerfile              # Main Docker image definition
├── docker-compose.yml      # Multi-container orchestration
├── .dockerignore          # Files to exclude from build
├── docker/
│   ├── nginx/
│   │   └── default.conf   # Nginx web server config
│   └── supervisor/
│       └── supervisord.conf # Process manager config
└── DEPLOYMENT.md          # This file
```

## Security Considerations

1. **Change Default Passwords**: Never use default passwords in production
2. **Use HTTPS**: Always use SSL/TLS in production
3. **Firewall Rules**: Limit access to only necessary ports
4. **Regular Updates**: Keep Docker images and dependencies updated
5. **Environment Variables**: Never commit `.env` files to version control
6. **Database Backups**: Set up automated backups

## Performance Optimization

1. **Enable OPcache**: Already configured in the Docker image
2. **Use Redis**: For sessions and cache
3. **CDN**: Serve static assets through a CDN
4. **Database Indexing**: Optimize database queries

## Support

For issues or questions:
- Open an issue on GitHub
- Check Laravel documentation: https://laravel.com/docs
- Check Vue.js documentation: https://vuejs.org/guide/

---

**Note**: This Docker setup is production-ready but should be customized based on your specific hosting requirements.
