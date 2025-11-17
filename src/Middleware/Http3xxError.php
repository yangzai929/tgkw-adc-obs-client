<?php

namespace Kalax2\Obs\Middleware;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Http3xxError
{
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) use ($request) {
                    if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
                        throw RequestException::create($request, $response);
                    }
                    return $response;
                }
            );
        };
    }
}