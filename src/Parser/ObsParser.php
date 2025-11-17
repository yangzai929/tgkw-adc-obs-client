<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs\Parser;

use Psr\Http\Message\ResponseInterface;
use TgkwAdc\Obs\Utils;

class ObsParser implements ObsParserInterface
{
    public function __construct(private array $headersMap = [], private array $xmlArrayNodes = [])
    {
        $this->headersMap = array_merge($this->headersMap, ['x-obs-request-id' => 'RequestId']);
    }

    public function __invoke(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();
        $response->getBody()->rewind();
        $result = [];

        if (! empty($contents)) {
            if (str_starts_with($contents, '<?xml')) {
                $result = Utils::xmlToArray($contents, $this->xmlArrayNodes);
            } else {
                $result = array_merge($result, json_decode($contents, true) ?? []);
            }
        }

        foreach ($this->headersMap as $key => $value) {
            if ($response->hasHeader($key)) {
                $result[$value] = $response->getHeaderLine($key);
            }
        }

        return $result;
    }
}
