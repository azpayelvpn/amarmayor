# DEPLOYMENT, BACKUP & DISASTER RECOVERY SPECIFICATION

**System:** Amar Mayor (Mymensingh City Corporation)  
**Environment Requirements:** Linux (Ubuntu 22.04 LTS / Debian 12), PHP 8.2+ (FPM + OPcache), MySQL 8.4+ InnoDB, Redis 7+ (Optional Cache/Session Acceleration), Nginx 1.24+

---

## 1. Nginx Reverse Proxy & FastCGI Configuration

```nginx
server {
    listen 80;
    server_name amarmayor.mymensinghcity.gov.bd;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name amarmayor.mymensinghcity.gov.bd;

    ssl_certificate /etc/letsencrypt/live/amarmayor.mymensinghcity.gov.bd/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/amarmayor.mymensinghcity.gov.bd/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/amarmayor/backend/public;
    index index.php index.html;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Restrict uploads directory from executing scripts
    location /uploads/ {
        types {
            image/jpeg jpg jpeg;
            image/png png;
            image/webp webp;
        }
        default_type application/octet-stream;
        location ~ \.(php|phtml|phar)$ {
            deny all;
        }
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 60s;
    }

    location ~ /\. {
        deny all;
    }
}
```

---

## 2. Process Supervision (Supervisor Configuration)

File: `/etc/supervisor/conf.d/amarmayor-workers.conf`

```ini
[program:amarmayor-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/amarmayor/backend/bin/worker.php
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/amarmayor/worker.log

[program:amarmayor-scheduler]
command=php /var/www/amarmayor/backend/bin/scheduler.php
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/amarmayor/scheduler.log
```

---

## 3. Database Backup & Disaster Recovery

### Daily Automated Backup Script (`/usr/local/bin/amarmayor-backup.sh`)
```bash
#!/bin/bash
set -e
BACKUP_DIR="/var/backups/amarmayor"
DATE=$(date +"%Y%m%d_%H%M%S")
mkdir -p "${BACKUP_DIR}"

# 1. Dump MySQL Database
mysqldump --single-transaction --quick --routines --triggers -u amarmayor_backup -p"${DB_BACKUP_PASS}" amarmayor | gzip > "${BACKUP_DIR}/db_backup_${DATE}.sql.gz"

# 2. Archive Evidence Uploads
tar -czf "${BACKUP_DIR}/media_backup_${DATE}.tar.gz" -C /var/www/amarmayor/backend/public uploads/

# 3. Retain last 30 days locally
find "${BACKUP_DIR}" -type f -mtime +30 -delete
```

### Full System Recovery Procedure
1. Install base OS, PHP 8.2-FPM, MySQL 8.4, and Nginx.
2. Restore database:
   ```bash
   gunzip < db_backup_YYYYMMDD_HHMMSS.sql.gz | mysql -u root -p amarmayor
   ```
3. Restore media assets:
   ```bash
   tar -xzf media_backup_YYYYMMDD_HHMMSS.tar.gz -C /var/www/amarmayor/backend/public/
   ```
4. Copy `.env` with secure keys and verify permissions (`chown -R www-data:www-data /var/www/amarmayor`).
5. Restart PHP-FPM and Nginx; start background worker processes via `supervisorctl restart all`.
