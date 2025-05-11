<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;
use Database\Seeds\DatabaseSeeder;

Database::migrate();
DatabaseSeeder::run();
