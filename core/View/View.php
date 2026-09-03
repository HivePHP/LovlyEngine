<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\View;

use Twig\Environment;

final class View
{
    /** @var array<string, mixed> */
    private array $shared = [];

    public function __construct(
        private readonly Environment $twig
    ) {}

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    /**
     * Share many values at once.
     *
     * @param array<string, mixed> $values
     */
    public function shareMany(array $values): void
    {
        $this->shared = array_merge($this->shared, $values);
    }

    public function render(string $template, array $data = []): string
    {
        return $this->twig->render(
            $template . '.twig',
            array_merge($this->shared, $data)
        );
    }
}
