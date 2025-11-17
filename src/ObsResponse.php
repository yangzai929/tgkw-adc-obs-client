<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs;

use ArrayAccess;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use TgkwAdc\Obs\Parser\ErrorParser;
use TgkwAdc\Obs\Parser\ObsParser;
use TgkwAdc\Obs\Parser\ObsParserInterface;

class ObsResponse extends Response implements ArrayAccess
{
    private array $result = [];

    public function __construct(ResponseInterface $rawResponse, ?ObsParserInterface $parser = null)
    {
        parent::__construct(
            $rawResponse->getStatusCode(),
            $rawResponse->getHeaders(),
            $rawResponse->getBody(),
            $rawResponse->getProtocolVersion(),
            $rawResponse->getReasonPhrase()
        );

        $this->parse($parser);
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->result);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->result[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    private function parse(?ObsParserInterface $parser): void
    {
        if (is_null($parser)) {
            $parser = $this->getStatusCode() === 200 ? new ObsParser() : new ErrorParser();
        }

        $this->result = $parser($this);
    }
}
