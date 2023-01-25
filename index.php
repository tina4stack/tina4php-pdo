
<?php

require "./vendor/autoload.php";

$DB = new \Tina4\DataPDO("sqlite:test.db");

$DB->exec("CREATE TABLE IF NOT EXISTS `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

$DB->exec("insert into users (name, email, password) values ('John Smith', 'andre@test.com', 'none')");

$users = $DB->fetch(["select * from users where id = ?", 1]);

print_r ($users);

$users = $DB->fetch("select * from users");


print_r ($users);