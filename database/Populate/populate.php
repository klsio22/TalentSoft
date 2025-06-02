<?php

/**
 * Populate.php - Simple migration script
 *
 * This file provides a simple entry point to run migrations.
 * For full data population, use populate_all.php instead.
 */

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;

echo "Running database migrations...\n";
Database::migrate();
echo "Migrations completed successfully.\n";
