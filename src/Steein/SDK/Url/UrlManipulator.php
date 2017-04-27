<?php
/**
 * Copyright (c) 2017 Steein, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Steein.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Steein\SDK\Url;

/**
 * Class UrlManipulator
 *
 * @package Steein\SDK
*/
class UrlManipulator
{
    /**
     * Удалить параметры из URL.
     *
     * @param string $url            URL для фильтрации.
     * @param array  $paramsToFilter Параметры для фильтрации по URL.
     *
     * @return string
     */
    public static function removeParamsFromUrl($url, array $paramsToFilter)
    {
        $parts = parse_url($url);

        $query = '';
        if (isset($parts['query']))
        {
            $params = [];
            parse_str($parts['query'], $params);
            // Удалить параметры запроса
            foreach ($paramsToFilter as $paramName) {
                unset($params[$paramName]);
            }
            if (count($params) > 0) {
                $query = '?' . http_build_query($params, null, '&');
            }
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = isset($parts['path']) ? $parts['path'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $host . $port . $path . $query . $fragment;
    }

    /**
     * Изящно добавляет params к URL.
     *
     * @param string $url       URL, который будет получать параметры.
     * @param array  $newParams Параметры, добавляемые к URL-адресу.
     *
     * @return string
     */
    public static function appendParamsToUrl($url, array $newParams = [])
    {
        if (empty($newParams)) {
            return $url;
        }

        if (strpos($url, '?') === false) {
            return $url . '?' . http_build_query($newParams, null, '&');
        }

        list($path, $query) = explode('?', $url, 2);
        $existingParams = [];
        parse_str($query, $existingParams);

        // Использовать параметры из исходного URL поверх $newParams
        $newParams = array_merge($newParams, $existingParams);

        // Сортировка для предикативного заказа
        ksort($newParams);

        return $path . '?' . http_build_query($newParams, null, '&');
    }

    /**
     * Возвращает параметры из URL-адреса в виде массива.
     *
     * @param string $url URL-адрес для анализа параметров.
     *
     * @return array
     */
    public static function getParamsAsArray($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if (!$query) {
            return [];
        }
        $params = [];
        parse_str($query, $params);

        return $params;
    }

    /**
     * Добавляет параметры первого URL ко второму URL.
     * Любые параметры, которые уже существуют во втором URL, останутся нетронутыми.
     *
     * @param string $urlToStealFrom URL-адрес извлекает параметры из.
     * @param string $urlToAddTo     URL, который получит новые параметры.
     *
     * @return string
     */
    public static function mergeUrlParams($urlToStealFrom, $urlToAddTo)
    {
        $newParams = static::getParamsAsArray($urlToStealFrom);
        // Ничего нового не добавьте, верните как есть.
        if (!$newParams) {
            return $urlToAddTo;
        }

        return static::appendParamsToUrl($urlToAddTo, $newParams);
    }

    /**
     * Проверьте наличие префикса "/" и добавьте его, если он не существует.
     *
     * @param string|null $string
     *
     * @return string|null
     */
    public static function forceSlashPrefix($string)
    {
        if (!$string) {
            return $string;
        }

        return strpos($string, '/') === 0 ? $string : '/' . $string;
    }

    /**
     * Обрезает имя хоста и версию Api с URL-адреса.
     *
     * @param string $urlToTrim URL нужен для операции.
     *
     * @return string
     */
    public static function baseGraphUrlEndpoint($urlToTrim)
    {
        return '/' . preg_replace('/^https:\/\/.+\.steein\.ru(\/v.+?)?\//', '', $urlToTrim);
    }
}
