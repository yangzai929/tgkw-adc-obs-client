<?php

namespace Kalax2\Obs\Parser;

use Psr\Http\Message\ResponseInterface;

class FileParser implements ObsParserInterface
{
    public function __construct(private array $headersMap = [])
    {
        $this->headersMap = array_merge($this->headersMap, ['x-obs-request-id' => 'RequestId']);
    }

    public function __invoke(ResponseInterface $response): array
    {
        $result = [];

        foreach ($this->headersMap as $key => $value) {
            if ($response->hasHeader($key)) {
                $result[$value] = $response->getHeaderLine($key);
            }
        }

        return $result;
    }
}