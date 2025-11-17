<?php

namespace Kalax2\Obs\Middleware;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Utils;

class AddContentMd5Header
{
    public function __invoke(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Content-MD5', base64_encode(Utils::hash($request->getBody(), 'md5', true)));
    }
}