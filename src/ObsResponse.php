<?php

namespace Kalax2\Obs;

use ArrayAccess;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

use Kalax2\Obs\Parser\ErrorParser;
use Kalax2\Obs\Parser\ObsParser;
use Kalax2\Obs\Parser\ObsParserInterface;

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

    private function parse(?ObsParserInterface $parser): void
    {
        if (is_null($parser)) {
            $parser = $this->getStatusCode() === 200 ? new ObsParser() : new ErrorParser();
        }

        $this->result = $parser($this);
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
}