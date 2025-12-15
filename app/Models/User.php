<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */


namespace App\Models;

use HivePHP\Database;

class User
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function register(
        string $name,
        string $surname,
        string $email,
        string $password)
    {

    }

    public function getDb(): Database
    {
        return $this->db;
    }
}