<?php
declare(strict_types=1);

namespace HivePHP;

class Container
{
    private array $items = [];

    public function set(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            throw new \RuntimeException("Container: key '$key' not found.");
        }

        return $this->items[$key];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }
}
