<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Support;

use Closure;
use ReflectionClass;
use RuntimeException;

final class Container
{
    private array $instances = [];

    public function set(string $id, mixed $value): void
    {
        if (!$value instanceof Closure && !is_object($value)) {
            throw new RuntimeException(
                'Container accepts only objects or closures'
            );
        }

        $this->instances[$id] = $value;
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            $entry = $this->instances[$id];

            if ($entry instanceof Closure) {
                $this->instances[$id] = $entry($this);
            }

            return $this->instances[$id];
        }

        if (!class_exists($id)) {
            throw new RuntimeException("Class [$id] not found");
        }

        $ref  = new ReflectionClass($id);
        $ctor = $ref->getConstructor();

        if (!$ctor) {
            return $this->instances[$id] = new $id();
        }

        $args = [];

        foreach ($ctor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type || $type->isBuiltin()) {
                throw new RuntimeException(
                    "Cannot resolve parameter \${$param->getName()} of {$id}"
                );
            }

            $args[] = $this->get($type->getName());
        }

        return $this->instances[$id] = $ref->newInstanceArgs($args);
    }
}
