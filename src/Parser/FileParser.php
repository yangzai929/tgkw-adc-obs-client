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
