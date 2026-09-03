<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Validation;

interface RuleInterface
{
    public function check(mixed $value, array $data): bool;
    public function message(string $field): string;
}