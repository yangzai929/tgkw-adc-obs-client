<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs\Exception;

use Exception;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use TgkwAdc\Obs\ObsResponse;

class ObsException extends Exception implements RequestExceptionInterface
{
    private ObsResponse $response;

    private RequestInterface $request;

    public function __construct(RequestException $exception)
    {
        $this->request = $exception->getRequest();
        $this->response = new ObsResponse($exception->getResponse());

        parent::__construct(
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getPrevious()
        );
    }

    public function getResponse(): ObsResponse
    {
        return $this->response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
