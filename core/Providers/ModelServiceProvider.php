<?php
declare(strict_types=1);

namespace HivePHP\Providers;

use ReflectionClass;

class ModelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modelsDir = ROOT . '/app/Models';

        if (!is_dir($modelsDir)) {
            return;
        }

        foreach (glob($modelsDir . '/*.php') as $file) {
            require_once $file;

            $class = $this->resolveClassName($file);

            if (!$class || !class_exists($class)) {
                continue;
            }

            // Проверяем, что это "обычная модель", а не абстрактная
            $ref = new ReflectionClass($class);
            if ($ref->isAbstract()) {
                continue;
            }

            // Создаём модель, передаём контейнер
            $instance = new $class($this->container);

            // Регистрируем в контейнере по имени класса
            $this->container->set($class, $instance);

            // И по короткому имени: User::class => "user"
            $short = strtolower($ref->getShortName());
            $this->container->set($short . '_model', $instance);
        }
    }

    private function resolveClassName(string $filePath): ?string
    {
        $fileName = basename($filePath, '.php');

        // Проверяем на валидность имени класса
        if (!preg_match('/^[A-Za-z0-9_]+$/', $fileName)) {
            return null;
        }

        return "App\\Models\\{$fileName}";
    }
}
