<?php

namespace Kalax2\Obs\Parser;

use Psr\Http\Message\ResponseInterface;

use Kalax2\Obs\Utils;

class ErrorParser implements ObsParserInterface
{
    public function __invoke(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();
        $response->getBody()->rewind();

        if (!empty($contents)) {
            if (str_starts_with($contents, '<?xml')) {
                return Utils::xmlToArray($contents);
            } else {
                $result = [];
                foreach (json_decode($contents, true) ?? [] as $key => $value) {
                    $result[str_replace('_', '', ucwords($key, '_'))] = $value;
                }
                return $result;
            }
        } else {
            $errorHeaders = [
                'x-obs-request-id',
                'x-obs-error-code',
                'x-obs-error-message',
            ];

            $result = [];

            foreach ($errorHeaders as $error) {
                if ($response->hasHeader($error)) {
                    $key = explode('-', preg_replace('/(x-obs-)(error-)?/', '', $error));
                    $key = implode(array_map('ucfirst', $key));
                    $result[$key] = $response->getHeaderLine($error);
                }
            }

            return $result;
        }
    }
}