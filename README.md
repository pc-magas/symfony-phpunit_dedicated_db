# Reference for using DB upon specific tests Symfony

Snipet and reference for using DB upon Symfony in Unit tests.

## Config DB

### Docker Config

Upon docker compose config:

```
networks:
      default:
        aliases:
          - test_db
        ipv4_address: ${IP_BASE}.3
```

A full mariadb service is:

```
services:
  mariadb:
    image: mariadb:10.4
    command:
      --max_allowed_packet=64M
      --optimizer_use_condition_selectivity=1
      --optimizer_switch="rowid_filter=off"
    networks:
      default:
        aliases:
          - test_db
    env_file: env/mysql_maria.env
    volumes:
      - ./volumes/db:/var/lib/mysql
      - ./provision/db/maria/allow_user_to_create_db.sh:/docker-entrypoint-initdb.d/allow_user_to_create_db.sh
```

Afterwards Create the script `./provision/db/maria/allow_user_to_create_db.sh` with the following content:

```bash
#!/usr/bin/env bash

echo "Allow mysql user $MYSQL_USER to create seperate db:"
docker_process_sql -uroot -p"$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT CREATE, DROP ON *.* TO '$MYSQL_USER'@'%';
    FLUSH PRIVILEGES;
EOSQL
```

The script allows to create a separate database for testing purposes, which is useful to avoid conflicts with the main database used in development or production. The script grants the necessary permissions to the MySQL user specified in the `env/mysql_maria.env` file, allowing it to create and drop databases as needed for testing.

If upon `env/mysql_maria.env` contain:

```
MYSQL_ROOT_PASSWORD=symfonyusr
MYSQL_USER=symfonyusr
MYSQL_DATABASE=symfonyusr
MYSQL_PASSWORD=symfonyusr
```

Then upon `phpunit.xml` you should have:

```xml
<php>
    <env name="TEST_DB_USERNAME" value="symfonyusr" />
    <env name="TEST_DB_PASSWORD" value="symfonyusr" />
    <env name="TEST_DB_HOST" value="test_db" />
</php>
```

The `TEST_DB_HOST` should be the same as the alias of the network in docker compose config:

```
  networks:
      default:
        aliases:
          - test_db
```

Whilst `TEST_DB_USERNAME` and `TEST_DB_PASSWORD` should be the same as the ones in `env/mysql_maria.env`:

```
MYSQL_ROOT_PASSWORD=symfonyusr
MYSQL_USER=symfonyusr
```

