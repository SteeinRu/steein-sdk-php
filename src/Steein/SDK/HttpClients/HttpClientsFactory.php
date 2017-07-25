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
namespace Steein\SDK\HttpClients;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Steein\SDK\Exceptions\HttpClientException;
use Steein\SDK\Interfaces\HttpClients\HttpClientInterface;

/**
 * Class HttpClientsFactory
 *
 * @package Steein\SDK
*/
class HttpClientsFactory
{
    private function __construct()
    {
        //
    }

    /**
     * Генерация клиента HTTP.
     *
     * @param HttpClientInterface|Client|string|null $handler
     * @return HttpClientInterface
     * @throws Exception
     * @throws HttpClientException
     */
    public static function createHttpClient($handler = 'curl')
    {
        if (!$handler) {
            return self::detectDefaultClient();
        }

        if ($handler instanceof HttpClientInterface) {
            return $handler;
        }

        if ('curl' === $handler) {
            if (!extension_loaded('curl')) {
                throw new Exception('The cURL extension must be loaded in order to use the "curl" handler.');
            }

            return new SteeinCurlHttpClient();
        }

        if ('guzzle' === $handler && !class_exists('\GuzzleHttp\Client')) {
            throw new HttpClientException('HTTP-клиент Guzzle должен быть включен.');
        }

        if ($handler instanceof Client) {
            return new GuzzleHttpClient($handler);
        }

        if ('guzzle' === $handler) {
            return new GuzzleHttpClient();
        }

        throw new InvalidArgumentException('Обработчик клиента HTTP должен быть установлен как "guzzle", быть экземпляром GuzzleHttp\Client или экземпляром HttpClientInterface');
    }

    /**
     * Определить HTTP-клиента по умолчанию.
     *
     * @return GuzzleHttpClient|SteeinCurlHttpClient
     * @throws HttpClientException
     */
    private static function detectDefaultClient()
    {
        if (extension_loaded('curl')) {
            return new SteeinCurlHttpClient();
        }

        if (class_exists('GuzzleHttp\Client')) {
            return new GuzzleHttpClient();
        }
        else {
            throw new HttpClientException('GuzzleHttp\Client пакет не найден');
        }
    }
}