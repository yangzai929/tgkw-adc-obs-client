## 简介

华为云对象存储服务OBS PHP Client

:exclamation: 请务必先阅读官方文档：https://support.huaweicloud.com/obs/index.html :exclamation:

环境要求：
- php>=8.0
- ext-json
- ext-simplexml

## 使用方法
安装
```shell
composer require tgkw-adc/huaweicloud-obs-client
```
示例
```php
use TgkwAdc\Obs\ObsClient;
use TgkwAdc\Obs\Exception\ObsException;

$obsClient = new ObsClient(
    // Access Key
    accessKey: 'AccessKey', 
    // Secret Key
    secretKey: 'SecretKey', 
    // 地域，注意不是控制台的Endpoint域名
    // 请看地域列表：https://console.huaweicloud.com/apiexplorer/#/endpoint/OBS
    region: 'cn-north-1', 
    // 存储桶名称
    bucket: 'BucketName',
    // guzzleHttp的配置，可选
    guzzleConfig: []
)

try {
    // 获取桶元数据
    $response = $obsClient->getBucketMetadata();
    // 通过ObsResponse->getHeaders() 获取全部响应头
    $response->getHeaders();
    // 另外也可以通过数组方式访问响应头
    // 如果响应头的名称是"x-obs-"开头，则通过去掉"x-obs-"前缀剩余部分为PascalCase命名方式访问
    // 例如桶的地域信息响应头为“x-obs-bucket-location”，则通过一下方式可以访问
    $bucketLocation = $response['BucketLocation'];
    // 以"Access-Control-"开头的也以同样规则访问
    $allowOrigin = $response['AllowOrigin'];

    // 上传文件
    $response = $obsClient->putObject(
        object: 'README.md',
        body: fopen('./README.md', 'r'),
        headers: [
            'Content-Type' => 'text/plain; charset=utf-8'
        ]
    );
    
    // 设置对象标签
    // 请参考官方文档：https://support.huaweicloud.com/api-obs/obs_04_0172.html
    // 如果传入的是数组则不需要提供root节点，这里的root节点为<Tagging>
    $xml = [
        'TagSet' => [
            'Tag' => [
                [
                    'Key' => 'key1',
                    'Value' => 'value1'
                ],
                [
                    'Key' => 'key2',
                    'Value' => 'value3'
                ],
            ]
        ]
    ];
    
    // 另外也可以直接传入xml字符串
    $xml = <<<XML
<?xml version="1.0"?>
<Tagging>
	<TagSet>
		<Tag>
			<Key>key1</Key>
			<Value>value1</Value>
		</Tag>
		<Tag>
			<Key>key2</Key>
			<Value>value3</Value>
		</Tag>
	</TagSet>
</Tagging>
XML;
    $response = $obsClient->setObjectTagging('README.md', $xml);
    
    // 获取对象标签
    // 请参考官方文档：https://support.huaweicloud.com/api-obs/obs_04_0164.html
    $response = $obsClient->getObjectTagging('README.md');
    // 通过ObsResponse->getBody()->getContents()直接获取响应内容
    print_r($response->getBody()->getContents());
    /* 输出
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Tagging xmlns="http://obs.myhwclouds.com/doc/2015-06-30/">
            <TagSet>
                <Tag>
                    <Key>key1</Key>
                    <Value>value1</Value>
                </Tag>
                <Tag>
                    <Key>key2</Key>
                    <Value>value3</Value>
                </Tag>
            </TagSet>
        </Tagging>
     */
     // 可以通过数组的方式直接访问响应内容
     $response['TagSet']['Tag'][0]['Key'];
     // 也可以获取完整的响应内容的数组
     $response->getResult();
    
} catch (ObsException $e) {
    $e->getRequest();  // 获取请求对象，类型为RequestInterface
    $e->getResponse(); // 获取响应对象，类型为ObsResponse
    
    // 可以通过数组的方式访问响应内容
    // 具体错误内容请看官方文档：https://support.huaweicloud.com/api-obs/obs_04_0115.html
    $e->getResponse()['Code'];
    $e->getResponse()['Message'];
    
    // 获取全部响应内容，类型为array
    $e->getResponse()->getResult();
} catch (\Exception $e) {
    // 处理其他Exception
}
```

## API

### 桶相关
```php
// 基本功能
listBuckets(array $headers = [])
createBucket(string $bucket, string $region, array $headers = [])
listObjects(array $query = [])
listVersions(array $query = [])
getBucketMetadata(array $headers = [])
getBucketLocation()
getBucketStorageInfo()
deleteBucket()

// 策略
setBucketPolicy(array|string $policy)
getBucketPolicy()
deleteBucketPolicy()

// ACL
setBucketAcl(array|string $acl, array $headers = [])
getBucketAcl()

// 日志
setBucketLogging(array|string $logging)
getBucketLogging()

// 生命周期
setBucketLifecycle(array|string $lifecycle)
getBucketLifecycle()
deleteBucketLifecycle()

// 多版本
setBucketVersioning(array|string $versioning)
getBucketVersioning()

// 存储类型
setBucketStorageClass(array|string $storageClass)
getBucketStorageClass()

// 跨区域复制
setBucketReplication(array|string $replication)
getBucketReplication()
deleteBucketReplication()

// 标签
setBucketTagging(array|string $tagging)
getBucketTagging()
deleteBucketTagging()

// 配额
setBucketQuota(array|string $quota)
getBucketQuota()

// 清单
setBucketInventory(array|string $inventory, array $query)
getBucketInventory(array $query)
listBucketInventory()
deleteBucketInventory(array $query)

// 自定义域名
setBucketCustomDomain(array $query)
getBucketCustomDomain()
deleteBucketCustomDomain(array $query)

// 加密
setBucketEncryption(array|string $encryption)
getBucketEncryption()
deleteBucketEncryption()

// 归档对象直读策略
setBucketDirectColdAccess(array|string $directColdAccess)
getBucketDirectColdAccess()
deleteBucketDirectColdAccess()

// 镜像回源规则
setBucketMirrorBackToSource(array|string $mirrorBackToSource)
getBucketMirrorBackToSource()
deleteBucketMirrorBackToSource()

// DIS通知策略
setBucketDisPolicy(array|string $disPolicy)
getBucketDisPolicy()
deleteBucketDisPolicy()

// 在线解压策略
setBucketObsCompressPolicy(array|string $obsCompressPolicy)
getBucketObsCompressPolicy()
deleteBucketObsCompressPolicy()

// 桶级默认WORM策略
setBucketObjectLock(array|string $objectLock)
getBucketObjectLock()
```
### 对象相关
```php
// 基本功能
putObject(string $object, string|Psr\Http\Message\StreamInterface|resource $body, array $headers = [])
copyObject(string $object, array $headers)
getObject(string $object, array $query = [], array $headers = [])
setObjectMetadata(string $object, array $query = [], array $headers = [])
headObject(string $object, array $query = [], array $headers = [])
deleteObject(string $object, array $query = [])
deleteObjects(array|string $objects)
restoreObject(string $object, array|string $restore, array $query = [])
appendObject(string $object, string|Psr\Http\Message\StreamInterface|resource $body, array $query = [], array $headers = [])
modifyObject(string $object, mixed $body, array $query = [])
truncateObject(string $object, array $query = [])
renameObject(string $object, array $query = [])

// 临时链接，$domain可以设置为自定义域名
createTemporaryUrl(string $object, int $expires, string $domain = '')

// ACL
setObjectAcl(string $object, array|string $acl, array $query = [])
getObjectAcl(string $object, array $query = [])

// 标签
setObjectTagging(string $object, array|string $tagging, array $query = [])
getObjectTagging(string $object, array $query = [])
deleteObjectTagging(string $object, array $query = [])

// 对象级WORM保护策略
setObjectRetention(string $object, array|string $retention, array $query = [])

// 分段上传
listMultipartUploads(array $query = [])
initiateMultipartUpload(string $object, array $query = [], array $headers = [])
uploadPart(string $object, mixed $body, array $query = [], array $headers = [])
copyPart(string $object, array $query = [], array $headers = [])
listParts(string $object, array $query = [])
completeMultipartUpload(string $object, array|string $completeMultipartUpload, array $query = [])
abortMultipartUpload(string $object, array $query = [])
```
### 静态网站
```php
setBucketWebsite(array|string $website)
getBucketWebsite()
deleteBucketWebsite()
options(array $headers, string $object = '')

// CORS
setBucketCors(array|string $cors)
getBucketCors()
deleteBucketCors()
```
