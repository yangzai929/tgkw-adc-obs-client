<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use InvalidArgumentException;
use TgkwAdc\Obs\Exception\ObsException;
use TgkwAdc\Obs\Middleware\AddAuthorizationHeader;
use TgkwAdc\Obs\Middleware\AddContentMd5Header;
use TgkwAdc\Obs\Middleware\Http3xxError;
use TgkwAdc\Obs\Parser\ObsParserInterface;
use TgkwAdc\Obs\Trait\BucketTrait;
use TgkwAdc\Obs\Trait\ObjectTrait;
use TgkwAdc\Obs\Trait\WebsiteTrait;

class ObsClient
{
    use BucketTrait;
    use WebsiteTrait;
    use ObjectTrait;

    private Client $httpClient;

    public function __construct(
        private string $accessKey,
        private string $secretKey,
        private string $region,
        private string $bucket,
        array $guzzleConfig = []
    ) {
        if (preg_match('/[a-z]+-[a-z]+-\d+/i', $this->region) !== 1) {
            throw new InvalidArgumentException('Invalid OBS Region: ' . $this->region);
        }

        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest(new AddContentMd5Header()));
        $stack->push(Middleware::mapRequest(new AddAuthorizationHeader($this->accessKey, $this->secretKey)));
        $stack->push(new Http3xxError());

        $guzzleDefaultConfig = [
            'handler' => $stack,
            'allow_redirects' => false,
        ];
        $guzzleConfig = array_merge($guzzleConfig, $guzzleDefaultConfig);

        $this->httpClient = new Client($guzzleConfig);
    }

    public function createTemporaryUrl(string $object, int $expires, string $domain = ''): string
    {
        $signature = new Signature($this->secretKey);
        $canonicalResource = '/' . $this->bucket . '/' . trim($object, '/');
        $expiresString = (string) $expires;

        $query = [
            'AccessKeyId' => $this->accessKey,
            'Expires' => $expires,
            'Signature' => $signature->createTemporarySignature(
                method: 'GET',
                contentMd5: '',
                contentType: '',
                expires: $expiresString,
                canonicalizedHeaders: [],
                canonicalizedResource: $canonicalResource
            ),
        ];

        if (empty($domain)) {
            return $this->createUri($this->bucket, $this->region, $object, http_build_query($query));
        }
        return 'https://' . $domain . '/' . trim($object, '/') . '?' . http_build_query($query);
    }

    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * @throws GuzzleException|ObsException
     */
    protected function request(
        string $method,
        string $uri,
        array $headers = [],
        mixed $body = null,
        ?ObsParserInterface $parser = null
    ): ObsResponse {
        try {
            $response = $this->httpClient->request($method, $uri, [
                'headers' => $headers,
                'body' => $body,
            ]);

            return new ObsResponse($response, $parser);
        } catch (RequestException $e) {
            throw new ObsException($e);
        }
    }

    protected function createUri(
        ?string $bucket = null,
        ?string $region = null,
        string $object = '',
        string $query = ''
    ): string {
        return Utils::createUri($bucket ?? $this->bucket, $region ?? $this->region, $object, $query);
    }
}
