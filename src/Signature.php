<?php

namespace Kalax2\Obs;

class Signature
{
    public function __construct(private string $secretKey)
    {
    }

    public function create(string $method,
                           string $contentMd5,
                           string $contentType,
                           string $date,
                           array  $canonicalizedHeaders,
                           string $canonicalizedResource): string
    {
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

    public function createTemporarySignature(string $method,
                                             string $contentMd5,
                                             string $contentType,
                                             string $expires,
                                             array  $canonicalizedHeaders,
                                             string $canonicalizedResource): string
    {
        $signature = $this->create($method, $contentMd5, $contentType, $expires, $canonicalizedHeaders, $canonicalizedResource);

        return $signature;
    }
}