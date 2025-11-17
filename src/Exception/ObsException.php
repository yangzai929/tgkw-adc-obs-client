<?php

namespace Kalax2\Obs\Exception;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;

use Kalax2\Obs\ObsResponse;

class ObsException extends \Exception implements RequestExceptionInterface
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