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
