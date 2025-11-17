<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs\Trait;

use GuzzleHttp\Exception\GuzzleException;
use TgkwAdc\Obs\Exception\ObsException;
use TgkwAdc\Obs\ObsResponse;
use TgkwAdc\Obs\Parser\ObsParser;
use TgkwAdc\Obs\Parser\ObsParserInterface;
use TgkwAdc\Obs\Utils;

trait WebsiteTrait
{
    /**
     * 设置桶的网站配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0071.html
     * @throws GuzzleException|ObsException
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
     * 获取桶的网站配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0072.html
     * @throws GuzzleException|ObsException
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
     * 删除桶的网站配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0073.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketWebsite(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'website')
        );
    }

    /**
     * 设置桶的CORS配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0074.html
     * @throws GuzzleException|ObsException
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
     * 获取桶的CORS配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0075.html
     * @throws GuzzleException|ObsException
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
     * 删除桶的CORS配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0076.html
     * @throws GuzzleException|ObsException
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
     * @see https://support.huaweicloud.com/api-obs/obs_04_0077.html
     * @see https://support.huaweicloud.com/api-obs/obs_04_0078.html
     * @throws GuzzleException|ObsException
     */
    public function options(array $headers, string $object = ''): ObsResponse
    {
        $responseHeadersMap = [
            'Access-Control-Allow-Origin' => 'AccessControlAllowOrigin',
            'Access-Control-Allow-Headers' => 'AccessControlAllowHeaders',
            'Access-Control-Max-Age' => 'AccessControlMaxAge',
            'Access-Control-Allow-Methods' => 'AccessControlAllowMethods',
            'Access-Control-Expose-Headers' => 'AccessControlExposeHeaders',
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'OPTIONS',
            uri: $this->createUri(object: $object),
            headers: $headers,
            parser: $parser
        );
    }

    abstract protected function request(string $method, string $uri, array $headers = [], mixed $body = null, ?ObsParserInterface $parser = null): ObsResponse;

    abstract protected function createUri(?string $bucket = null, ?string $region = null, string $object = '', string $query = ''): string;
}
