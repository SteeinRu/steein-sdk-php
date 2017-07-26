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
namespace Steein\SDK\Core\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Steein\SDK\Core\SteeinConstants;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Core\Http\RawResponse;
use Steein\SDK\Interfaces\HttpClients\HttpClientInterface;

/**
 * Class GuzzleHttpClient
 *
 * @package Steein\SDK
*/
class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * Клиент GuzzleHttp.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    /**
     * Конструктор для клиента Guzzle
     *
     * @param \GuzzleHttp\Client|null
     */
    public function __construct($guzzleClient = null)
    {
        $this->guzzleClient = new Client();
    }

    /**
     * @inheritdoc
     */
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        //Опции
        $options = [
            'headers'           => $headers,
            'body'              => $body,
            'timeout'           => $timeOut,
            'connect_timeout'   => 10,
            'verify'            => __DIR__.'/certs/DigiCertHighAssuranceEVRootCA.pem'
        ];

        if(SteeinConstants::DEBUG == true) {
            $options['verify'] = false;
        }

        $request =  new Request($method, $url, $options['headers'], $options['body']);

        try
        {
            $rawResponse = $this->guzzleClient->send($request, $options);

        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();

            if ($rawResponse instanceof ResponseInterface) {
                throw new SteeinSDKException($e->getMessage(), $e->getCode());
            }
        }

        $rawHeaders = $this->getHeadersAsString($rawResponse);
        $rawBody = $rawResponse->getBody();
        $httpStatusCode = $rawResponse->getStatusCode();

        return new RawResponse($rawHeaders, $rawBody, $httpStatusCode);
    }

    /**
     * Возвращает массив заголовков Guzzle в виде строки.
     *
     * @param ResponseInterface $response The Guzzle response.
     *
     * @return string
     */
    public function getHeadersAsString(ResponseInterface $response)
    {
        $headers = $response->getHeaders();
        $rawHeaders = [];
        foreach ($headers as $name => $values) {
            $rawHeaders[] = $name . ": " . implode(", ", $values);
        }

        return implode("\r\n", $rawHeaders);
    }
}