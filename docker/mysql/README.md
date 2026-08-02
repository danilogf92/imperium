# MySQL 8.4 con Docker

Este servicio reemplaza el MySQL de XAMPP y conserva los datos en un volumen de Docker.

## Iniciar

Desde la raíz del proyecto:

```powershell
docker compose -f docker/mysql/docker-compose.yml up -d
```

Comprobar el estado:

```powershell
docker compose -f docker/mysql/docker-compose.yml ps
```

Ver los logs:

```powershell
docker compose -f docker/mysql/docker-compose.yml logs -f mysql
```

## Conexión

- Host desde Windows: `127.0.0.1`
- Puerto: `3306`
- Base de datos: `mi_base`
- Usuario: `root`
- Contraseña: `root`

## phpMyAdmin

Abre <http://localhost:8080> e inicia sesión con:

- Servidor: `mysql`
- Usuario: `root`
- Contraseña: `root`

Para Laravel, usa estos valores en el `.env` del proyecto:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_base
DB_USERNAME=root
DB_PASSWORD=root
```

Después de cambiar el `.env`, limpia la configuración almacenada:

```powershell
php artisan config:clear
```

## Detener

```powershell
docker compose -f docker/mysql/docker-compose.yml down
```

El comando anterior conserva los datos. Para borrar también la base de datos, ejecuta explícitamente `down -v`.

Los datos se guardan en el volumen persistente `mysql_mysql_data`. Para crear además un respaldo SQL externo al volumen:

```powershell
docker exec daimperium-mysql sh -c "mysqldump -uroot -proot --single-transaction --routines --triggers --events daimperium > /tmp/daimperium-backup.sql"
docker cp daimperium-mysql:/tmp/daimperium-backup.sql docker/mysql/backups/daimperium-backup.sql
```

> Antes de iniciar, detén MySQL en XAMPP para evitar que ambos servicios intenten usar el puerto `3306`.
