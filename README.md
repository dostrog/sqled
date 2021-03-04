# Sqled

Concatenate multiple SQL-files and apply with transaction. Bundled daily in advance (optional).

_This is a test assigment project (B1)_ [Description on Google Docs](https://docs.google.com/document/d/1dC0PrfmIbhP3EtG-3gwdto5vrv83m1DAmjSiNOSCAaQ/edit?usp=sharing)

Used technologies:

- [PHP 7.4](https://www.php.net)
- [Laravel Zero - Micro-framework for console applications](https://laravel-zero.com)

------

## Installation

Project is written on 100% PHP and may be started from within project folder (`php sqled`) or from PHAR archive as a standalone application.

- Copy PHAR archive to the localhost from [https://github.com/dostrog/sqled/tree/develop/builds](https://github.com/dostrog/awsync/tree/develop/builds)
- Create `.env` file in the same folder where PHAR archive is

Example of .env file
```shell
DB_CONNECTION=sqlite
#DB_DATABASE=/absolute/path/to/database.sqlite

#DB_CONNECTION=mysql
#DB_HOST=127.0.0.1
#DB_PORT=3306
#DB_DATABASE=sqled
#DB_USERNAME=sqled
#DB_PASSWORD=sqled
#DB_SOCKET=/tmp/mysql.sock
```

### Demo

- Create test . **NB! existing data may be overwritten! by using commands `polygon`**
    ```shell
    $ php sqled polygon
    Create set of SQL-script files for testing ("./assets/change")...

    File populated: 11 
    ```

    It will populate test sql-files in (`./assets/change`). There will be files with simple SQL script. Some of them with errors to test transaction. To populate only correct SQL files use `--onlyCorrect` option.

    ```shell
    $ tree -L 3
    .
    ├── assets
    │   ├── change
    │   │   ├── applied
    │   │   ├── change_20170908_a.sql
    │   │   ├── change_20170908_b.sql
    │   │   ├── change_20170908_j.sql
    │   │   ├── change_20171010_j.sql
    │   │   ├── change_20180909_2.sql
    │   │   ├── change_20180909_a.sql
    │   │   ├── change_20180909_j.sql
    │   │   ├── change_20200909_0.sql
    │   │   ├── change_20200909_1.sql
    │   │   ├── change_20200909_3.sql
    │   │   └── change_20200909_j.sql
    │   └── sqled.sqlite
    └── sqled
    
    3 directories, 13 files
    ```
    
- Run command within terminal. (Log file `sqled-YYYY-MM-DD.log` will be created in the `./logs/` directory.) 

    ```shell
    $ php sqled seed

    Execute SQL scripts from files.

    11/11 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
    ```
    ```shell
    $ tree -L 5
    .
    ├── assets
    │   ├── change
    │   │   └── applied
    │   │       ├── 20170908
    │   │       │   ├── change_20170908_a.sql
    │   │       │   ├── change_20170908_b.sql
    │   │       │   └── change_20170908_j.sql
    │   │       ├── 20171010
    │   │       │   └── change_20171010_j.sql
    │   │       ├── 20180909
    │   │       │   ├── change_20180909_2.sql
    │   │       │   ├── change_20180909_a.sql
    │   │       │   └── change_20180909_j.sql
    │   │       └── 20200909
    │   │           ├── change_20200909_0.sql
    │   │           ├── change_20200909_1.sql
    │   │           ├── change_20200909_3.sql
    │   │           └── change_20200909_j.sql
    │   └── sqled.sqlite
    ├── logs
    │   └── sqled-2021-03-01.log
    └── sqled
    ```

- with using `--daily` option bundles will be created and applied as one-for-day.

## Using

Project is written on 100% PHP and may be started from within project folder or from PHAR archive as a standalone application. 

```shell
$ php sqled seed
```

## Help

```shell

$ php sqled

  Sqled  1.0.0

  USAGE: sqled <command> [options] [arguments]

  migrate          Run the database migrations
  polygon          Create set of SQL-script files for testing ("./assets/change")
  seed             Execute SQL scripts from files.

  db:wipe          Drop all tables, views, and types

  make:migration   Create a new migration file

  migrate:fresh    Drop all tables and re-run all migrations
  migrate:install  Create the migration repository
  migrate:refresh  Reset and re-run all migrations
  migrate:reset    Rollback all database migrations
  migrate:rollback Rollback the last database migration
  migrate:status   Show the status of each migration
```
