<?php

namespace Kalax2\Obs;

use InvalidArgumentException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

use Kalax2\Obs\Exception\ObsException;
use Kalax2\Obs\Middleware\AddAuthorizationHeader;
use Kalax2\Obs\Middleware\AddContentMd5Header;
use Kalax2\Obs\Middleware\Http3xxError;
use Kalax2\Obs\Parser\ObsParserInterface;
use Kalax2\Obs\Trait\BucketTrait;
use Kalax2\Obs\Trait\ObjectTrait;
use Kalax2\Obs\Trait\WebsiteTrait;

class ObsClient
{
    use BucketTrait, WebsiteTrait, ObjectTrait;

    private Client $httpClient;

    public function __construct(private string $accessKey,
                                private string $secretKey,
                                private string $region,
                                private string $bucket,
                                array          $guzzleConfig = [])
    {
        if (preg_match('/[a-z]+-[a-z]+-\d+/i', $this->region) !== 1) {
            throw new InvalidArgumentException('Invalid OBS Region: ' . $this->region);
        }

        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest(new AddContentMd5Header()));
        $stack->push(Middleware::mapRequest(new AddAuthorizationHeader($this->accessKey, $this->secretKey)));
        $stack->push(new Http3xxError());

        $guzzleDefaultConfig = [
            'handler' => $stack,
            'allow_redirects' => false
        ];
        $guzzleConfig = array_merge($guzzleConfig, $guzzleDefaultConfig);

        $this->httpClient = new Client($guzzleConfig);
    }

    /**
     * @throws ObsException|GuzzleException
     */
    protected function request(string              $method,
                               string              $uri,
                               array               $headers = [],
                               mixed               $body = null,
                               ?ObsParserInterface $parser = null): ObsResponse
    {
        try {
            $response = $this->httpClient->request($method, $uri, [
                'headers' => $headers,
                'body' => $body
            ]);

            return new ObsResponse($response, $parser);
        } catch (RequestException $e) {
            throw new ObsException($e);
        }
    }

    protected function createUri(?string $bucket = null,
                                 ?string $region = null,
                                 string  $object = '',
                                 string  $query = ''): string
    {
        return Utils::createUri($bucket ?? $this->bucket, $region ?? $this->region, $object, $query);
    }

    public function createTemporaryUrl(string $object, int $expires, string $domain = ''): string
    {
        $signature = new Signature($this->secretKey);
        $query = [
            'AccessKeyId' => $this->accessKey,
            'Expires' => $expires,
            'Signature' => $signature->createTemporarySignature(
                method: 'GET',
                contentMd5: '',
                contentType: '',
                expires: $expires,
                canonicalizedHeaders: [],
                canonicalizedResource: '/' . (empty($domain) ? $this->bucket : $domain) . '/' . trim($object, '/')
            )
        ];

        if (empty($domain)) {
            return $this->createUri($this->bucket, $this->region, $object, http_build_query($query));
        } else {
            return 'https://' . $domain . '/' . trim($object, '/') . '?' . http_build_query($query);
        }
    }

    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }
}
