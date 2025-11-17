<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs\Middleware;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

class AddContentMd5Header
{
    public function __invoke(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Content-MD5', base64_encode(Utils::hash($request->getBody(), 'md5', true)));
    }
}
