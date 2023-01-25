#PDO
##Installing

```
composer install tina4stack/tina4php-pdo
```

for development version as we test

```
composer install tina4stack/tina4php-pdo:dev-main
```

Example of using the PDO driver

``` 
global $DBA;
$DBA = new \Tina4\DataPDO("sqlite:test.db");

global $DBA;
$DBA = \Tina4\DataPDO("dblib:host=".$_ENV["DB_HOST"].":".$_ENV["DB_PORT"].";dbname=".$_ENV["DB_NAME"],$_ENV["DB_USER"],$_ENV["DB_PASSWORD"]);

```