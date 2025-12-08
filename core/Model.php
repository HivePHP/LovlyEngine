<?php
declare(strict_types=1);

namespace HivePHP;

abstract class Model
{
    protected Database $db;

    public function __construct(protected Container $container)
    {
        $this->db = $container->get('db');
    }
}