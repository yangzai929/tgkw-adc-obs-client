<?php

namespace Kalax2\Obs\Trait;

use GuzzleHttp\Exception\GuzzleException;

use Kalax2\Obs\Exception\ObsException;
use Kalax2\Obs\ObsResponse;
use Kalax2\Obs\Parser\FileParser;
use Kalax2\Obs\Parser\ObsParser;
use Kalax2\Obs\Parser\ObsParserInterface;
use Kalax2\Obs\Utils;
use Psr\Http\Message\StreamInterface;

trait ObjectTrait
{
    abstract protected function request(string $method, string $uri, array $headers = [], mixed $body = null, ?ObsParserInterface $parser = null): ObsResponse;
    abstract protected function createUri(?string $bucket = null, ?string $region = null, string $object = '', string $query = ''): string;

    /**
     * PUT上传
     * @link https://support.huaweicloud.com/api-obs/obs_04_0080.html
     * @param string $object
     * @param string|StreamInterface|resource $body
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function putObject(string $object, mixed $body, array $headers = []): ObsResponse
    {
        $responseHeadersMap = [
            'x-obs-version-id' => 'VersionId',
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-data-encryption' => 'ServerSideDataEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' => 'ServerSideEncryptionCustomerKeyMD5',
            'x-obs-storage-class' => 'StorageClass'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object),
            headers: $headers,
            body: $body,
            parser: $parser
        );
    }

    /**
     * 复制对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0082.html
     * @param string $object
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function copyObject(string $object, array $headers): ObsResponse
    {
        $responseHeadersMap = [
            'x-obs-copy-source-version-id' => 'CopySourceVersionId',
            'x-obs-version-id' => 'VersionId',
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' => 'ServerSideEncryptionCustomerKeyMD5',
            'x-obs-storage-class' => 'StorageClass'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 获取对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0083.html
     * @param string $object
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function getObject(string $object, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);

        $responseHeadersMap = [
            'x-obs-expiration' => 'Expiration',
            'x-obs-website-redirect-location' => 'WebsiteRedirectLocation',
            'x-obs-delete-marker' => 'DeleteMarker',
            'x-obs-version-id' => 'VersionId',
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-data-encryption' => 'ServerSideDataEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' => 'ServerSideEncryptionCustomerKeyMD5',
            'x-obs-object-type' => 'ObjectType',
            'x-obs-next-append-position' => 'NextAppendPosition',
            'x-obs-tagging-count' => 'TaggingCount',
            'ETag' => 'ETag'
        ];

        $parser = new FileParser($responseHeadersMap);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 获取对象元数据
     * @link https://support.huaweicloud.com/api-obs/obs_04_0084.html
     * @param string $object
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function headObject(string $object, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);

        $responseHeadersMap = [
            'x-obs-expiration' => 'Expiration',
            'x-obs-website-redirect-location' => 'WebsiteRedirectLocation',
            'x-obs-version-id' => 'VersionId',
            'Access-Control-Allow-Origin' => 'AccessControlAllowOrigin',
            'Access-Control-Allow-Headers' => 'AccessControlAllowHeaders',
            'Access-Control-Max-Age' => 'AccessControlMaxAge',
            'Access-Control-Allow-Methods' => 'AccessControlAllowMethods',
            'Access-Control-Expose-Headers' => 'AccessControlExposeHeaders',
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-data-encryption' => 'ServerSideDataEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' => 'ServerSideEncryptionCustomerKeyMD5',
            'x-obs-storage-class' => 'StorageClass',
            'x-obs-restore' => 'Restore',
            'x-obs-object-type' => 'ObjectType',
            'x-obs-next-append-position' => 'NextAppendPosition',
            'x-obs-uploadId' => 'UploadId',
            'x-obs-tagging-count' => 'TaggingCount',
            'x-obs-object-lock-mode' => 'ObjectLockMode',
            'x-obs-object-lock-retain-until-date' => 'ObjectLockRetainUntilDate'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'HEAD',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 删除对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0085.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function deleteObject(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);

        $responseHeadersMap = [
            'x-obs-delete-marker' => 'DeleteMarker',
            'x-obs-version-id' => 'VersionId'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(object: $object, query: $query),
            parser: $parser
        );
    }

    /**
     * 批量删除对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0086.html
     * @param array|string $objects
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function deleteObjects(array|string $objects): ObsResponse
    {
        $objects = is_string($objects) ? $objects : Utils::arrayToXml($objects, 'Delete');
        $headers = ['Content-Type' => 'application/xml'];
        $parser = new ObsParser(xmlArrayNodes: ['Deleted', 'Error']);

        return $this->request(
            method: 'POST',
            uri: $this->createUri(query: 'delete'),
            headers: $headers,
            body: $objects,
            parser: $parser
        );
    }

    /**
     * 恢复归档或深度归档存储对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0087.html
     * @param string $object
     * @param array|string $restore
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function restoreObject(string $object, array|string $restore, array $query = []): ObsResponse
    {
        $restore = is_string($restore) ? $restore : Utils::arrayToXml($restore, 'RestoreRequest');
        $query = http_build_query($query);
        $query = 'restore&' . $query;
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'POST',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            body: $restore
        );
    }

    /**
     * 追加写对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0088.html
     * @param string $object
     * @param string|StreamInterface|resource $body
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function appendObject(string $object, mixed $body, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'append&' . $query;

        $responseHeadersMap = [
            'x-obs-version-id' => 'VersionId',
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-data-encryption' => 'ServerSideDataEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' => 'ServerSideEncryptionCustomerKeyMD5',
            'x-obs-next-append-position' => 'NextAppendPosition'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'POST',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            body: $body,
            parser: $parser
        );
    }

    /**
     * 设置对象ACL
     * @link https://support.huaweicloud.com/api-obs/obs_04_0089.html
     * @param string $object
     * @param array|string $acl
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function setObjectAcl(string $object, array|string $acl, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'acl&' . $query;
        $acl = is_string($acl) ? $acl : Utils::arrayToXml($acl, 'AccessControlPolicy');
        $headers = ['Content-Type' => 'application/xml'];

        $responseHeadersMap = [
            'x-obs-version-id' => 'VersionId'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            body: $acl,
            parser: $parser
        );
    }

    /**
     * 获取对象ACL
     * @link https://support.huaweicloud.com/api-obs/obs_04_0090.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function getObjectAcl(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'acl&' . $query;

        $responseHeadersMap = [
            'x-obs-version-id' => 'VersionId'
        ];

        $parser = new ObsParser($responseHeadersMap, ['Grant']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(object: $object, query: $query),
            parser: $parser
        );
    }

    /**
     * 修改对象元数据
     * @link https://support.huaweicloud.com/api-obs/obs_04_0091.html
     * @param string $object
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function setObjectMetadata(string $object, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'metadata&' . $query;

        $responseHeadersMap = [
            'x-obs-metadata-directive' => 'MetadataDirective',
            'Cache-Control' => 'CacheControl',
            'Content-Disposition' => 'ContentDisposition',
            'Content-Encoding' => 'ContentEncoding',
            'Content-Language' => 'ContentLanguage',
            'Expires' => 'Expires',
            'x-obs-website-redirect-location' => 'WebsiteRedirectLocation',
            'x-obs-storage-class' => 'StorageClass',
            'x-obs-expires' => 'x-obs-expires'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 修改写对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0092.html
     * @param string $object
     * @param StreamInterface|string|resource $body
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function modifyObject(string $object, mixed $body, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'modify&' . $query;

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            body: $body
        );
    }

    /**
     * 截断对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0093.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function truncateObject(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'truncate&' . $query;

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query)
        );
    }

    /**
     * 重命名对象
     * @link https://support.huaweicloud.com/api-obs/obs_04_0094.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function renameObject(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'rename&' . $query;

        return $this->request(
            method: 'POST',
            uri: $this->createUri(object: $object, query: $query)
        );
    }

    /**
     * 设置对象标签
     * @link https://support.huaweicloud.com/api-obs/obs_04_0172.html
     * @param string $object
     * @param array|string $tagging
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function setObjectTagging(string $object, array|string $tagging, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'tagging&' . $query;
        $tagging = is_string($tagging) ? $tagging : Utils::arrayToXml($tagging, 'Tagging');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            body: $tagging
        );
    }

    /**
     * 获取对象标签
     * @link https://support.huaweicloud.com/api-obs/obs_04_0164.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function getObjectTagging(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'tagging&' . $query;
        $parser = new ObsParser(xmlArrayNodes: ['Tag']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(object: $object, query: $query),
            parser: $parser
        );
    }

    /**
     * 删除对象标签
     * @link https://support.huaweicloud.com/api-obs/obs_04_0165.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function deleteObjectTagging(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'tagging&' . $query;

        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(object: $object, query: $query)
        );
    }

    /**
     * 配置对象级WORM保护策略
     * @link https://support.huaweicloud.com/api-obs/obs_04_0166.html
     * @param string $object
     * @param array|string $retention
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function setObjectRetention(string $object, array|string $retention, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'retention&' . $query;
        $retention = is_string($retention) ? $retention : Utils::arrayToXml($retention, 'Retention');
        $headers = ['Content-Type' => 'application/xml'];

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            body: $retention
        );
    }

    /**
     * 列举桶中已初始化多段任务
     * @link https://support.huaweicloud.com/api-obs/obs_04_0097.html
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function listMultipartUploads(array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'uploads&' . $query;
        $parser = new ObsParser(xmlArrayNodes: ['Upload']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(query: $query),
            parser: $parser
        );
    }

    /**
     * 初始化上传段任务
     * @link https://support.huaweicloud.com/api-obs/obs_04_0098.html
     * @param string $object
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function initiateMultipartUpload(string $object, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);
        $query = 'uploads&' . $query;

        $responseHeadersMap = [
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' =>  'ServerSideEncryptionCustomerKeyMD5'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'POST',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 上传段
     * @link https://support.huaweicloud.com/api-obs/obs_04_0099.html
     * @param string $object
     * @param StreamInterface|string|resource $body
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function uploadPart(string $object, mixed $body, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            body: $body
        );
    }

    /**
     * 拷贝段
     * @link https://support.huaweicloud.com/api-obs/obs_04_0100.html
     * @param string $object
     * @param array $query
     * @param array $headers
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function copyPart(string $object, array $query = [], array $headers = []): ObsResponse
    {
        $query = http_build_query($query);

        $responseHeadersMap = [
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm',
            'x-obs-server-side-encryption-customer-key-MD5' =>  'ServerSideEncryptionCustomerKeyMD5'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'PUT',
            uri: $this->createUri(object: $object, query: $query),
            headers: $headers,
            parser: $parser
        );
    }

    /**
     * 列举已上传未合并的段
     * @link https://support.huaweicloud.com/api-obs/obs_04_0101.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function listParts(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);
        $parser = new ObsParser(xmlArrayNodes: ['Part']);

        return $this->request(
            method: 'GET',
            uri: $this->createUri(object: $object, query: $query),
            parser: $parser
        );
    }

    /**
     * 合并段
     * @link https://support.huaweicloud.com/api-obs/obs_04_0102.html
     * @param string $object
     * @param array|string $completeMultipartUpload
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function completeMultipartUpload(string $object, array|string $completeMultipartUpload, array $query = []): ObsResponse
    {
        $completeMultipartUpload = is_string($completeMultipartUpload) ? $completeMultipartUpload : Utils::arrayToXml($completeMultipartUpload, 'CompleteMultipartUpload');
        $query = http_build_query($query);

        $responseHeadersMap = [
            'x-obs-version-id' => 'VersionId',
            'x-obs-server-side-encryption' => 'ServerSideEncryption',
            'x-obs-server-side-encryption-kms-key-id' => 'ServerSideEncryptionKmsKeyId',
            'x-obs-server-side-encryption-customer-algorithm' => 'ServerSideEncryptionCustomerAlgorithm'
        ];

        $parser = new ObsParser($responseHeadersMap);

        return $this->request(
            method: 'POST',
            uri: $this->createUri(object: $object, query: $query),
            body: $completeMultipartUpload,
            parser: $parser
        );
    }

    /**
     * 取消多段上传任务
     * @link https://support.huaweicloud.com/api-obs/obs_04_0103.html
     * @param string $object
     * @param array $query
     * @return ObsResponse
     * @throws ObsException|GuzzleException
     */
    public function abortMultipartUpload(string $object, array $query = []): ObsResponse
    {
        $query = http_build_query($query);

        return $this->request(
            method: 'DELETE',
            uri: $this->createUri(object: $object, query: $query)
        );
    }
}