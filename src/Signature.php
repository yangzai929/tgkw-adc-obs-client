<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs;

class Signature
{
    public function __construct(private string $secretKey)
    {
    }

    public function create(
        string $method,
        string $contentMd5,
        string $contentType,
        string $date,
        array $canonicalizedHeaders,
        string $canonicalizedResource
    ): string {
        $signature = "{$method}\n";
        $signature .= "{$contentMd5}\n";
        $signature .= "{$contentType}\n";
        $signature .= "{$date}\n";

        foreach ($canonicalizedHeaders as $key => $value) {
            $signature .= "{$key}:{$value}\n";
        }

        $signature .= "{$canonicalizedResource}";

        return base64_encode(hash_hmac('sha1', $signature, $this->secretKey, true));
    }

    public function createTemporarySignature(
        string $method,
        string $contentMd5,
        string $contentType,
        string $expires,
        array $canonicalizedHeaders,
        string $canonicalizedResource
    ): string {
        return $this->create($method, $contentMd5, $contentType, $expires, $canonicalizedHeaders, $canonicalizedResource);
    }
}
