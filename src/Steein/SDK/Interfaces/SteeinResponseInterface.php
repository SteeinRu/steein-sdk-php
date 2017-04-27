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
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\SteeinRequest;

/**
 * Interface SteeinResponse
 *
 * @package Steein\SDK
 */
interface SteeinResponseInterface
{
    /**
     * Возвращает исходный запрос, который возвратил этот ответ.
     *
     * @return SteeinRequest
     */
    public function getRequest();

    /**
     * Возвращаем объект Application, используемый для этого ответа.
     *
     * @return Application
     */
    public function getApp();

    /**
     * Возвращаем Токен доступ.
     *
     * @return string|null
     */
    public function getAccessToken();

    /**
     * Возвращает код статуса HTTP.
     *
     * @return int
     */
    public function getHttpStatusCode();

    /**
     * Возвращает заголовки
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Возвращает результат работы.
     *
     * @return string
     */
    public function getBody();

    /**
     * Возвращает декодированный результат.
     *
     * @return array
     */
    public function getDecodedBody();

    /**
     * Возвращает ETag.
     *
     * @return string|null
     */
    public function getETag();

    /**
     * Создать экземпляр AbstractApi из ответа.
     *
     * @param string|null $subclassName Подкласс AbstractApi для преобразования в.
     *
     * @return \Steein\SDK\Support\Model
     * @throws SteeinSDKException
     */
    public function getApiObject($subclassName = null);

    /**
     * Удобный способ для создания коллекции UserModel.
     *
     * @return \Steein\SDK\Support\Models\UserModel
     * @throws SteeinSDKException
     */
    public function getUserModel();
}