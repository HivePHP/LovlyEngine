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

use HivePHP\Validation\Rules\{EmailRule};
use HivePHP\Validation\Rules\AlphaRule;
use HivePHP\Validation\Rules\DateValidRule;
use HivePHP\Validation\Rules\InRule;
use HivePHP\Validation\Rules\IntegerRule;
use HivePHP\Validation\Rules\MaxRule;
use HivePHP\Validation\Rules\MinRule;
use HivePHP\Validation\Rules\NullableRule;
use HivePHP\Validation\Rules\RequiredRule;
use HivePHP\Validation\Rules\SameRule;
use HivePHP\Validation\Rules\StringRule;

final class RuleParser
{
    public static function parse(string $rules): array
    {
        $result = [];

        foreach (explode('|', $rules) as $rule) {
            [$name, $args] = array_pad(explode(':', $rule, 2), 2, null);
            $params = $args ? explode(',', $args) : [];

            $result[] = match ($name) {
                'required'   => new RequiredRule(),
                'string'     => new StringRule(),
                'integer'    => new IntegerRule(),
                'min'        => new MinRule((int)$params[0]),
                'max'        => new MaxRule((int)$params[0]),
                'email'      => new EmailRule(),
                'alpha'      => new AlphaRule(),
                'in'         => new InRule($params),
                'same'       => new SameRule($params[0]),
                'date_valid' => new DateValidRule($params),
                'nullable'   => new NullableRule(),
                default      => throw new \RuntimeException("Unknown rule {$name}")
            };
        }

        return $result;
    }
}