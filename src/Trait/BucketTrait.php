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
use TgkwAdc\Obs\Parser\LocationParser;
use TgkwAdc\Obs\Parser\ObsParser;
use TgkwAdc\Obs\Parser\ObsParserInterface;
use TgkwAdc\Obs\Parser\StorageClassParser;
use TgkwAdc\Obs\Utils;

trait BucketTrait
{
    /**
     * 获取桶列表.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0020.html
     * @throws GuzzleException|ObsException
     */
    public function listBuckets(array $headers = []): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['Bucket']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(bucket: ''),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 创建桶.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0021.html
     * @throws GuzzleException|ObsException
     */
    public function createBucket(string $bucket, string $region, array $headers = []): ObsResponse
    {
        $body = Utils::arrayToXml(['Location' => $region], 'CreateBucketConfiguration');
        $headers['Content-Type'] = 'application/xml';

        return $this->request(
            method: 'PUT',
            uri: $this->createUri($bucket, $region),
            headers: $headers,
            body: $body
        );
    }

    /**
     * 获取桶列表.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0022.html
     * @throws GuzzleException|ObsException
     */
    public function listObjects(array $query = [], bool $isVersionsQuery = false): ObsResponse
    {
        $query = http_build_query($query);
        $query = ($isVersionsQuery ? 'versions&' : '') . $query;
        $parser = new ObsParser(xmlArrayNodes: ['Contents', 'CommonPrefixes', 'Version']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: $query),
            parser: $parser
        );
    }

    /**
     * 列举桶内多版本对象
     * @see https://support.huaweicloud.com/api-obs/obs_04_0022.html
     * @throws GuzzleException|ObsException
     */
    public function listVersions(array $query = []): ObsResponse
    {
        return $this->listObjects($query, true);
    }

    /**
     * 获取桶元数据.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0023.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketMetadata(array $headers = []): ObsResponse
    {
        $responseHeadersMap = [
            'x-obs-bucket-location' => 'BucketLocation',
            'x-obs-storage-class' => 'StorageClass',
            'x-obs-version' => 'Version',
            'x-obs-fs-file-interface' => 'FsFileInterface',
            'x-obs-epid' => 'Epid',
            'x-obs-az-redundancy' => 'AzRedundancy',
            'Access-Control-Allow-Origin' => 'AccessControlAllowOrigin',
            'Access-Control-Allow-Headers' => 'AccessControlAllowHeaders',
            'Access-Control-Max-Age' => 'AccessControlMaxAge',
            'Access-Control-Allow-Methods' => 'AccessControlAllowMethods',
            'Access-Control-Expose-Headers' => 'AccessControlExposeHeaders',
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'HEAD',
            uri: $this->createUri(),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 获取桶元数据.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0023.html
     * @throws GuzzleException|ObsException
     */
    public function headBucket(): ObsResponse
    {
        return $this->getBucketMetadata();
    }

    /**
     * 获取桶位置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0024.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketLocation(): ObsResponse
    {
        $parser = new LocationParser();

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'location'),
            parser: $parser
        );
    }

    /**
     * 删除桶.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0025.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucket(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri()
        );
    }

    /**
     * 设置桶策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0027.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketPolicy(array|string $policy): ObsResponse
    {
        $policy = is_string($policy) ? $policy : json_encode($policy);
        $headers = ['Content-Type' => 'application/json'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'policy'),
            headers: $headers,
            body: $policy
        );
    }

    /**
     * 获取桶策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0028.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketPolicy(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'policy')
        );
    }

    /**
     * 删除桶策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0029.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketPolicy(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'policy')
        );
    }

    /**
     * 设置桶ACL.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0030.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketAcl(array|string $acl, array $headers = []): ObsResponse
    {
        $acl = is_string($acl) ? $acl : Utils::arrayToXml($acl, 'AccessControlPolicy');
        $headers['Content-Type'] = 'application/xml';

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'acl'),
            headers: $headers,
            body: $acl
        );
    }

    /**
     * 获取桶ACL.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0031.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketAcl(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['Grant']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'acl'),
            parser: $parser
        );
    }

    /**
     * 设置桶日志管理配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0032.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketLogging(array|string $logging): ObsResponse
    {
        $logging = is_string($logging) ? $logging : Utils::arrayToXml($logging, 'BucketLoggingStatus');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'logging'),
            headers: $headers,
            body: $logging
        );
    }

    /**
     * 获取桶日志管理配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0033.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketLogging(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['Grant']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'logging'),
            parser: $parser
        );
    }

    /**
     * 设置桶的生命周期配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0034.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketLifecycle(array|string $lifecycle): ObsResponse
    {
        $lifecycle = is_string($lifecycle) ? $lifecycle : Utils::arrayToXml($lifecycle, 'LifecycleConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'lifecycle'),
            headers: $headers,
            body: $lifecycle
        );
    }

    /**
     * 获取桶的生命周期配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0035.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketLifecycle(): ObsResponse
    {
        $paser = new ObsParser(xmlArrayNodes: ['Rule', 'Transition', 'NoncurrentVersionTransition']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'lifecycle'),
            parser: $paser
        );
    }

    /**
     * 删除桶的生命周期配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0036.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketLifecycle(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'lifecycle')
        );
    }

    /**
     * 设置桶的多版本状态
     * @see https://support.huaweicloud.com/api-obs/obs_04_0037.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketVersioning(array|string $versioning): ObsResponse
    {
        $versioning = is_string($versioning) ? $versioning : Utils::arrayToXml($versioning, 'VersioningConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'versioning'),
            headers: $headers,
            body: $versioning
        );
    }

    /**
     * 获取桶的多版本状态
     * @see https://support.huaweicloud.com/api-obs/obs_04_0038.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketVersioning(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'versioning')
        );
    }

    /**
     * 设置桶默认存储类型.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0044.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketStorageClass(array|string $storageClass): ObsResponse
    {
        $storageClass = is_string($storageClass) ? $storageClass : Utils::arrayToXml($storageClass, 'StorageClass');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'storageClass'),
            headers: $headers,
            body: $storageClass
        );
    }

    /**
     * 获取桶默认存储类型.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0045.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketStorageClass(): ObsResponse
    {
        $parser = new StorageClassParser();

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'storageClass'),
            parser: $parser
        );
    }

    /**
     * 设置桶的跨区域复制配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0046.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketReplication(array|string $replication): ObsResponse
    {
        $replication = is_string($replication) ? $replication : Utils::arrayToXml($replication, 'ReplicationConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'replication'),
            headers: $headers,
            body: $replication
        );
    }

    /**
     * 获取桶的跨区域复制配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0047.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketReplication(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['Rule']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'replication'),
            parser: $parser
        );
    }

    /**
     * 删除桶的跨区域复制配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0048.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketReplication(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'replication')
        );
    }

    /**
     * 设置桶标签.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0049.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketTagging(array|string $tagging): ObsResponse
    {
        $tagging = is_string($tagging) ? $tagging : Utils::arrayToXml($tagging, 'Tagging');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'tagging'),
            headers: $headers,
            body: $tagging
        );
    }

    /**
     * 获取桶标签.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0050.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketTagging(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['Tag']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'tagging'),
            parser: $parser
        );
    }

    /**
     * 删除桶标签.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0051.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketTagging(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'tagging')
        );
    }

    /**
     * 设置桶配额.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0052.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketQuota(array|string $quota): ObsResponse
    {
        $quota = is_string($quota) ? $quota : Utils::arrayToXml($quota, 'Quota');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'quota'),
            headers: $headers,
            body: $quota
        );
    }

    /**
     * 获取桶配额.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0053.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketQuota(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'quota')
        );
    }

    /**
     * 获取桶存量信息.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0054.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketStorageInfo(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'storageinfo')
        );
    }

    /**
     * 设置桶清单.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0055.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketInventory(array|string $inventory, array $query): ObsResponse
    {
        $inventory = is_string($inventory) ? $inventory : Utils::arrayToXml($inventory, 'InventoryConfiguration');
        $headers = ['Content-Type' => 'application/xml'];
        $query = http_build_query($query);
        $query = 'inventory&' . $query;

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: $query),
            headers: $headers,
            body: $inventory
        );
    }

    /**
     * 获取桶清单.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0056.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketInventory(array $query): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'inventory&' . $query;

        $parser = new ObsParser(xmlArrayNodes: ['Field', 'InventoryConfiguration']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: $query),
            parser: $parser
        );
    }

    /**
     * 列举桶清单.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0057.html
     * @throws GuzzleException|ObsException
     */
    public function listBucketInventory(): ObsResponse
    {
        return $this->getBucketInventory([]);
    }

    /**
     * 删除桶清单.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0058.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketInventory(array $query): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'inventory&' . $query;

        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: $query)
        );
    }

    /**
     * 设置桶的自定义域名.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0059.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketCustomDomain(array $query): ObsResponse
    {
        $query = http_build_query($query);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: $query)
        );
    }

    /**
     * 获取桶的自定义域名.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0060.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketCustomDomain(): ObsResponse
    {
        $parser = new ObsParser(xmlArrayNodes: ['Domains']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'customdomain'),
            parser: $parser
        );
    }

    /**
     * 删除桶的自定义域名.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0061.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketCustomDomain(array $query): ObsResponse
    {
        $query = http_build_query($query);

        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: $query)
        );
    }

    /**
     * 设置桶的加密配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0062.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketEncryption(array|string $encryption): ObsResponse
    {
        $encryption = is_string($encryption) ? $encryption : Utils::arrayToXml($encryption, 'ServerSideEncryptionConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'encryption'),
            headers: $headers,
            body: $encryption
        );
    }

    /**
     * 获取桶的加密配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0063.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketEncryption(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'encryption')
        );
    }

    /**
     * 删除桶的加密配置.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0064.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketEncryption(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'encryption')
        );
    }

    /**
     * 设置桶归档对象直读策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0065.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketDirectColdAccess(array|string $directColdAccess): ObsResponse
    {
        $directColdAccess = is_string($directColdAccess) ? $directColdAccess : Utils::arrayToXml($directColdAccess, 'DirectColdAccessConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'directcoldaccess'),
            headers: $headers,
            body: $directColdAccess
        );
    }

    /**
     * 获取桶归档对象直读策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0066.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketDirectColdAccess(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'directcoldaccess')
        );
    }

    /**
     * 删除桶归档对象直读策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0067.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketDirectColdAccess(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'directcoldaccess')
        );
    }

    /**
     * 设置镜像回源规则.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0119.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketMirrorBackToSource(array|string $mirrorBackToSource): ObsResponse
    {
        $mirrorBackToSource = is_string($mirrorBackToSource) ? $mirrorBackToSource : json_encode($mirrorBackToSource);
        $headers = ['Content-Type' => 'application/json'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'mirrorBackToSource'),
            headers: $headers,
            body: $mirrorBackToSource
        );
    }

    /**
     * 获取镜像回源规则.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0120.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketMirrorBackToSource(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'mirrorBackToSource')
        );
    }

    /**
     * 删除镜像回源规则.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0121.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketMirrorBackToSource(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'mirrorBackToSource')
        );
    }

    /**
     * 设置DIS通知策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0139.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketDisPolicy(array|string $disPolicy): ObsResponse
    {
        $disPolicy = is_string($disPolicy) ? $disPolicy : json_encode($disPolicy);
        $headers = ['Content-Type' => 'application/json'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'disPolicy'),
            headers: $headers,
            body: $disPolicy
        );
    }

    /**
     * 获取DIS通知策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0140.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketDisPolicy(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'disPolicy')
        );
    }

    /**
     * 删除DIS通知策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0141.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketDisPolicy(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'disPolicy')
        );
    }

    /**
     * 设置在线解压策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0148.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketObsCompressPolicy(array|string $obsCompressPolicy): ObsResponse
    {
        $obsCompressPolicy = is_string($obsCompressPolicy) ? $obsCompressPolicy : json_encode($obsCompressPolicy);
        $headers = ['Content-Type' => 'application/json'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'obscompresspolicy'),
            headers: $headers,
            body: $obsCompressPolicy
        );
    }

    /**
     * 获取在线解压策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0149.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketObsCompressPolicy(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'obscompresspolicy')
        );
    }

    /**
     * 删除在线解压策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0150.html
     * @throws GuzzleException|ObsException
     */
    public function deleteBucketObsCompressPolicy(): ObsResponse
    {
        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(query: 'obscompresspolicy')
        );
    }

    /**
     * 配置桶级默认WORM策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0167.html
     * @throws GuzzleException|ObsException
     */
    public function setBucketObjectLock(array|string $objectLock): ObsResponse
    {
        $objectLock = is_string($objectLock) ? $objectLock : Utils::arrayToXml($objectLock, 'ObjectLockConfiguration');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(query: 'object-lock'),
            headers: $headers,
            body: $objectLock
        );
    }

    /**
     * 获取桶级默认WORM策略.
     * @see https://support.huaweicloud.com/api-obs/obs_04_0168.html
     * @throws GuzzleException|ObsException
     */
    public function getBucketObjectLock(): ObsResponse
    {
        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: 'object-lock')
        );
    }

    abstract protected function request(string $method, string $uri, array $headers = [], mixed $body = null, ?ObsParserInterface $parser = null): ObsResponse;

    abstract protected function createUri(?string $bucket = null, ?string $region = null, string $object = '', string $query = ''): string;
}
