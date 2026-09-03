<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Services;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

/**
 * Registration CAPTCHA.
 *
 * The visual phrase is generated and kept ONLY in the server-side session;
 * the client never receives it. Each submitted answer is validated once and
 * then consumed, so a captured token cannot be replayed (one-time use).
 */
final class CaptchaService
{
    private const SESSION_KEY = '_captcha_phrase';

    /** Phrase length (characters). */
    private const LENGTH = 5;

    /** Characters too easy to confuse (0/O, 1/I/l) are excluded by PhraseBuilder. */
    private const CHARSET = 'abcdefghjkmnpqrstuvwxyz23456789';

    public function __construct()
    {
    }

    /**
     * Build a fresh CAPTCHA, remember its answer in the session and return
     * the rendered PNG image bytes.
     */
    public function generate(): string
    {
        $builder = new CaptchaBuilder(
            (new PhraseBuilder(self::LENGTH, self::CHARSET))->build()
        );

        $builder
            ->build(160, 60)
            ->setDistortion(true)
            ->setImageType('png');

        $_SESSION[self::SESSION_KEY] = $builder->getPhrase();

        return $builder->get();
    }

    /**
     * Validate a submitted answer against the stored phrase.
     *
     * The stored phrase is always removed so that each answer is valid only
     * once (prevents token replay). Case-insensitive, whitespace-trimmed.
     */
    public function validate(?string $input): bool
    {
        $stored = $_SESSION[self::SESSION_KEY] ?? null;

        unset($_SESSION[self::SESSION_KEY]);

        if ($stored === null) {
            return false;
        }

        if ($input === null) {
            return false;
        }

        $submitted = strtolower(trim($input));

        return $submitted !== '' && hash_equals(strtolower($stored), $submitted);
    }
}
