<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */


namespace App\Controllers;

use HivePHP\Database;

abstract class BaseController
{
    protected array $config;
    protected Database $db;

    public function __construct(array $config, Database $db)
    {
        $this->config = $config;
        $this->db = $db;
    }
}