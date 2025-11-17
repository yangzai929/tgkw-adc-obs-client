<?php

namespace Kalax2\Obs\Middleware;

use Psr\Http\Message\RequestInterface;

use Kalax2\Obs\Signature;

class AddAuthorizationHeader
{
    const ALLOWED_RESOURCE_PARAMETER_NAMES = [
        'CDNNotifyConfiguration',
        'acl',
        'append',
        'attname',
        'backtosource',
        'cors',
        'customdomain',
        'delete',
        'deletebucket',
        'directcoldaccess',
        'encryption',
        'inventory',
        'length',
        'lifecycle',
        'location',
        'logging',
        'metadata',
        'modify',
        'name',
        'notification',
        'partNumber',
        'policy',
        'position',
        'quota',
        'rename',
        'replication',
        'restore',
        'storageClass',
        'storagePolicy',
        'storageinfo',
        'tagging',
        'torrent',
        'truncate',
        'uploadId',
        'uploads',
        'versionId',
        'versioning',
        'versions',
        'website',
        'x-obs-security-token',
        'object-lock',
        'retention',
        'mirrorBackToSource',
        'disPolicy',
        'obscompresspolicy',

        'response-cache-control',
        'response-content-disposition',
        'response-content-encoding',
        'response-content-language',
        'response-content-type',
        'response-expires',

        'x-image-process',
        'x-image-save-bucket',
        'x-image-save-object'
    ];

    private Signature $signature;

    public function __construct(private string $accessKey, private string $secretKey)
    {
        $this->signature = new Signature($this->secretKey);
    }

    public function __invoke(RequestInterface $request): RequestInterface
    {
        $method = $request->getMethod();
        $contentMd5 = $request->getHeaderLine('Content-MD5');
        $contentType = $request->getHeaderLine('Content-type');
        $date = gmdate('D, d M Y H:i:s \G\M\T');

        $bucket = preg_match('/^(.+?)\.obs\.[a-z]+-[a-z]+-\d+\.myhuaweicloud\.com$/i', $request->getUri()->getHost(), $match) === 1 ? $match[1] : '';

        $resourceParameters = [];
        $query = explode('&', $request->getUri()->getQuery());
        ksort($query);
        foreach ($query as $item) {
            $item = explode('=', $item);
            if (in_array($item[0], self::ALLOWED_RESOURCE_PARAMETER_NAMES)) {
                $resourceParameters[] = count($item) > 1 ? $item[0] . '=' . urldecode($item[1]) : $item[0];
            }
        }

        $canonicalizedResource = empty($resourceParameters) ? '' : '?' . implode('&', $resourceParameters);
        $canonicalizedResource = "/{$bucket}{$request->getUri()->getPath()}{$canonicalizedResource}";
        $canonicalizedResource = str_replace('//', '/', $canonicalizedResource);

        $canonicalizedHeaders = [];
        foreach ($request->getHeaders() as $key => $value) {
            if (str_starts_with($key, 'x-obs-')) {
                $canonicalizedHeaders[$key] = implode(',', $value);
            }
        }
        ksort($canonicalizedHeaders);

        $signature = $this->signature->create($method, $contentMd5, $contentType, $date, $canonicalizedHeaders, $canonicalizedResource);
        $request = $request->withHeader('Date', $date);
        $request = $request->withHeader('Authorization', "OBS {$this->accessKey}:{$signature}");
        return $request;
    }
}
