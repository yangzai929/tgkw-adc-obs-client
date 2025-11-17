<?php

namespace Kalax2\Obs\Parser;

use Psr\Http\Message\ResponseInterface;

class LocationParser implements ObsParserInterface
{

    public function __invoke(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();
        $response->getBody()->rewind();

        preg_match('/>([a-z0-9-]+?)<\/Location>/i', $contents, $matches);

        return ['Location' => $matches[1]];
    }
}