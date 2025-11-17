<?php

namespace Kalax2\Obs\Parser;

use Psr\Http\Message\ResponseInterface;

class StorageClassParser implements ObsParserInterface
{

    public function __invoke(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();
        $response->getBody()->rewind();

        preg_match('/>(\w+?)<\/StorageClass>/i', $contents, $matches);

        return ['StorageClass' => $matches[1]];
    }
}