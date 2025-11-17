<?php

namespace Kalax2\Obs\Trait;

use GuzzleHttp\Exception\GuzzleException;

use Kalax2\Obs\Exception\ObsException;
use Kalax2\Obs\ObsResponse;
use Kalax2\Obs\Parser\ObsParser;
use Kalax2\Obs\Parser\ObsParserInterface;
use Kalax2\Obs\Utils;

trait WebsiteTrait
{
    abstract protected function request(string $method, string $uri, array $headers = [], mixed $body = null, ?ObsParserInterface $parser = null): ObsResponse;
    abstract protected function createUri(?string $bucket = null, ?string $region = null, string $object = '', string $query = ''): string;

    /**
     * 设置桶的网站配置
     * @link https://support.huaweicloud.com/api-obs/obs_04_0071.html
     * @param array|string $website
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function setBucketWebsite(array|string $website): ObsResponse
    {
        $website = is_string($website) ? $website : Utils::arrayToXml($website, 'WebsiteConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'website'),
            headers: $headers,
            body: $website
        );
    }

    /**
     * 获取桶的网站配置
     * @link https://support.huaweicloud.com/api-obs/obs_04_0072.html
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function getBucketWebsite(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['RoutingRule']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'website'),
            parser: $parser
        );
    }

    /**
     * 删除桶的网站配置
     * @link https://support.huaweicloud.com/api-obs/obs_04_0073.html
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function deleteBucketWebsite(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'website')
        );
    }

    /**
     * 设置桶的CORS配置
     * @link https://support.huaweicloud.com/api-obs/obs_04_0074.html
     * @param array|string $cors
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function setBucketCors(array|string $cors): ObsResponse
    {
        $cors = is_string($cors) ? $cors : Utils::arrayToXml($cors, 'CORSConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'cors'),
            headers: $headers,
            body: $cors
        );
    }

    /**
     * 获取桶的CORS配置
     * @link https://support.huaweicloud.com/api-obs/obs_04_0075.html
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function getBucketCors(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['CORSRule']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'cors'),
            parser: $parser
        );
    }

    /**
     * 删除桶的CORS配置
     * @link https://support.huaweicloud.com/api-obs/obs_04_0076.html
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function deleteBucketCors(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'cors')
        );
    }

    /**
     * OPTIONS桶和OPTIONS对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0077.html
     * @link https://support.huaweicloud.com/api-obs/obs_04_0078.html
     * @param array $headers
     * @param string $object
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function options(array $headers, string $object = ''): ObsResponse
    {
        $responseHeadersMap = [
            'Access-Control-Allow-Origin' => 'AccessControlAllowOrigin',
            'Access-Control-Allow-Headers' => 'AccessControlAllowHeaders',
            'Access-Control-Max-Age' => 'AccessControlMaxAge',
            'Access-Control-Allow-Methods' => 'AccessControlAllowMethods',
            'Access-Control-Expose-Headers' => 'AccessControlExposeHeaders'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'OPTIONS',
            uri: $this->createUri(object: $object),
            headers: $headers,
            parser: $parser
        );
    }
}