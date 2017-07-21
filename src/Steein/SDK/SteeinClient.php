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
namespace Steein\SDK;

use Steein\SDK\Core\SteeinConstants;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Interfaces\HttpClients\HttpClientInterface;

/**
 * Class SteeinClient
 *
 * @package Steein
 */
class SteeinClient
{
    /**
     * Тайм-аут в секундах для обычного запроса.
     *
     * @const int
     */
    const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * Тайм-аут в секундах для запроса, который содержит загрузку файлов.
     *
     * @const int
     */
    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * Переключитесь, чтобы использовать бета-версию API.
     *
     * @var bool
     */
    protected $enableBetaMode = false;

    /**
     * Обработчик HTTP-клиента
     *
     * @var HttpClientInterface.
     */
    protected $httpClientHandler;

    /**
     * Количество вызовов, которые были сделаны в Api.
     *
     * @var int
     */
    public static $requestCount = 0;

    /**
     * Создает новый объект SteeinClient.
     *
     * @param HttpClientInterface|null $httpClientHandler
     * @param boolean                  $enableBeta
     */
    public function __construct(HttpClientInterface $httpClientHandler = null, $enableBeta = false)
    {
        $this->httpClientHandler = $httpClientHandler;
        //Не советуется включает этот способ (Могут возникнуть непредвиденные ошибки)
        $this->enableBetaMode = $enableBeta;
    }

    /**
     * Устанавливает обработчик HTTP-клиента.
     *
     * @param HttpClientInterface $httpClientHandler
     */
    public function setHttpClientHandler(HttpClientInterface $httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;
    }

    /**
     * Возвращает обработчик HTTP-клиента.
     *
     * @return HttpClientInterface
     */
    public function getHttpClientHandler()
    {
        return $this->httpClientHandler;
    }

    /**
     * Включить бета-режим.
     *
     * @param boolean $betaMode
     */
    public function enableBetaMode($betaMode = true)
    {
        $this->enableBetaMode = $betaMode;
    }

    /**
     * Подготавливает запрос на отправку в обработчик клиента.
     *
     * @param SteeinRequest $request
     *
     * @return array
     */
    public function prepareRequestMessage(SteeinRequest $request)
    {
        $url = SteeinConstants::REST_BASE_ENDPOINT . $request->getUrl();

        //Если мы отправляем файлы, они должны быть отправлены как multipart/form-data
        if ($request->containsFileUploads())
        {
            $requestBody = $request->getMultipartBody();

            $request->setHeaders([
                'Content-Type'  => 'multipart/form-data; boundary=' . $requestBody->getBoundary(),
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$request->getAccessToken(),
            ]);
        } else
        {
            $requestBody = $request->getUrlEncodedBody();
            $request->setHeaders([
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$request->getAccessToken(),
            ]);
        }

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody->getBody()
        ];
    }

    /**
     * Делает запрос в Api и возвращает результат.
     *
     * @param SteeinRequest $request
     *
     * @return SteeinResponse
     *
     * @throws SteeinSDKException
     */
    public function sendRequest(SteeinRequest $request)
    {
        if (get_class($request) === 'Steein\SDK\SteeinRequest') {
            $request->validateAccessToken();
        }

        list($url, $method, $headers, $body) = $this->prepareRequestMessage($request);

        // Поскольку загрузка файлов может занять некоторое время, нам нужно уделять больше времени загрузкам
        $timeOut = static::DEFAULT_REQUEST_TIMEOUT;

        if ($request->containsFileUploads()) {
            $timeOut = static::DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT;
        }

        // Должно быть исключение исключения SteeinSDKException при ошибке HTTP-клиента.
        $rawResponse = $this->httpClientHandler->send($url, $method, $body, $headers, $timeOut);

        //Увеличиваем счетчик вызовов
        static::$requestCount++;


        $returnResponse = new SteeinResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getHttpResponseCode(),
            $rawResponse->getHeaders()
        );

        //Если возникли ошибку
        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }
}