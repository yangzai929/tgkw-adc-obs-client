<?php

namespace Kalax2\Obs\Parser;

use Psr\Http\Message\ResponseInterface;

interface ObsParserInterface
{
    public function __invoke(ResponseInterface $response): array;
}