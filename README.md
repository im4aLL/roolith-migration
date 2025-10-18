# roolith-migration

Simple migration and seeding tool for PHP applications.

## Installation

You can install roolith-migration using Composer:

```bash
composer require roolith/migration
```

## Usage

Create a PHP file `migration.php` and add the following code:

```php
use Roolith\Migration\Migration;

require __DIR__ . "/../vendor/autoload.php";

$migration = new Migration();
$migration
    ->settings([
        "folder" => __DIR__ . "/migrations",
        "database" => [
            "host" => "localhost",
            "name" => "db_name",
            "user" => "user",
            "pass" => "pass",
        ],
    ])
    ->run($argv);
```

## Command

Assuming your filename is `migration.php`, you can run the migration command as follows:

```bash
php migration.php migration:create migration_name
php migration.php migration:run # it will run all pending migrations
php migration.php migration:run migration_name
php migration.php migration:rollback migration_name
php migration.php migration:status
```

For seeding data, you can use the following command:

```bash
php migration.php seed:create seed_name
php migration.php seed:run # it will run all pending seeds
php migration.php seed:run seed_name
```

## Notes

- it will create migrations table if not exists
- it will create migrations folder if not exists
- You can change the name of the folder by passing settings
