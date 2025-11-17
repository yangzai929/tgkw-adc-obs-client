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

interface ObsParserInterface
{
    public function __invoke(ResponseInterface $response): array;
}
