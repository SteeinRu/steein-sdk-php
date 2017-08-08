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

namespace Steein\SDK\Interfaces;

use Steein\SDK\Application;
use Steein\SDK\Authentication\AccessToken;
use Steein\SDK\Core\FileUpload\SteeinFile;
use Steein\SDK\Core\Http\Helpers\RedirectOAuth;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\SteeinClient;
use Steein\SDK\SteeinRequest;
use Steein\SDK\SteeinResponse;

/**
 * Interface SteeinInterface
 *
 * @package Steein
*/
interface SteeinInterface
{
    /**
     * Возращаем данные "Application"
     *
     * @return Application
     */
    public function getApp();

    /**
     * Возвращаем службу SteeinClient.
     *
     * @return SteeinClient
     */
    public function getClient();

    /**
     * Возвращает логин для перенаправления.
     *
     * @return RedirectOAuth
     */
    public function redirectOAuth();

    /**
     * Отправляет GET-запрос в Api и возвращает результат.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return SteeinResponse
     *
     * @throws SteeinSDKException
     */
    public function get($endpoint, $params = [], $eTag = null, $apiVersion = null);

    /**
     * Отправляет POST-запрос в Api и возвращает результат.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return SteeinResponse
     *
     * @throws SteeinSDKException
     */
    public function post($endpoint, $params = [], $eTag = null, $apiVersion = null);

    /**
     * Отправляет DELETE-запрос в Api и возвращает результат.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return SteeinResponse
     *
     * @throws SteeinSDKException
     */
    public function delete($endpoint, $params = [], $eTag = null, $apiVersion = null);

    /**
     * Создает новый объект SteeinRequest.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return SteeinRequest
     *
     * @throws SteeinSDKException
     */
    public function request($method, $endpoint, array $params = [], $eTag, $apiVersion = null);

    /**
     * Фабрика для создания Steein File.
     *
     * @param string $pathToFile
     *
     * @return SteeinFile
     *
     * @throws SteeinSDKException
     */
    public function fileToUpload($pathToFile);

    /**
     * Возвращает Api версию по умолчанию.
     *
     * @return string
     */
    public function getDefaultApiVersion();

    /**
     * Возвращает объект AccessToken по умолчанию.
     *
     * @return AccessToken|null
     */
    public function getDefaultAccessToken();
}