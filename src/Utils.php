<?php

declare(strict_types=1);
/**
 * This file is part of tgkw-adc.
 *
 * @link     https://www.tgkw.com
 * @document https://hyperf.wiki
 */

namespace TgkwAdc\Obs;

use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class Utils
{
    public static function arrayToXml(array $array, string $root): string
    {
        return ArrayToXml::convert($array, $root);
    }

    public static function xmlToArray(string $str, $forceArray = []): array
    {
        $xml = new SimpleXMLElement($str);

        $fn = function ($xml, $forceArray) use (&$fn) {
            $array = [];

            foreach ($xml->children() as $element) {
                $tagName = $element->getName();

                if (in_array($tagName, $forceArray) || count($xml->{$tagName}) > 1) {
                    if (! isset($array[$tagName])) {
                        $array[$tagName] = [];
                    }
                    $array[$tagName][] = $fn($element, $forceArray);
                } else {
                    $array[$tagName] = $fn($element, $forceArray);
                }
            }

            if (empty($array)) {
                return trim((string) $xml);
            }

            return $array;
        };

        return $fn($xml, $forceArray);
    }

    public static function createUri(
        string $bucket = '',
        string $region = '',
        string $object = '',
        string $query = ''
    ): string {
        $uri = 'https://';
        $uri .= $bucket ? $bucket . '.' : '';
        $uri .= 'obs.' . ($region ? $region . '.' : '') . 'myhuaweicloud.com/';
        $uri .= ltrim($object, '/');
        $uri .= $query ? '?' . $query : '';

        return $uri;
    }
}
