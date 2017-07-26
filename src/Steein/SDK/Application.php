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

use Serializable;
use Steein\SDK\Authentication\AccessToken;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Core\SteeinConstants;

/**
 * Class Application
 *
 * @package Steein\SDK
 */
class Application implements Serializable
{
    /**
     * Параметр для client ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Параметр для client secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * Конструктор
     *
     * @param string $id
     * @param string $secret
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function __construct($id, $secret)
    {
        // Хранение этого для BS. Целые числа, превышающие PHP_INT_MAX, сделают is_int() return false
        if (!is_string($id) && !is_int($id)) {
            throw new SteeinSDKException('"client_id" должен быть строковым');
        }

        $this->id = (string) $id;
        $this->secret = $secret;
    }

    /**
     * Возвращает ID клиента.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Возвращает Secret ключ клиента
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Возвращает адрес системы
     *
     * @return string
    */
    public function baseUrl()
    {
        return SteeinConstants::REST_BASE_ENDPOINT;
    }

    /**
     * Возвращает токен ключ к приложению.
     *
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return new AccessToken($this->id . '|' . $this->secret);
    }

    /**
     * Сериализует объект Application в виде строки.
     *
     * @return string
     */
    public function serialize()
    {
        return implode('|', [$this->id, $this->secret]);
    }

    /**
     * Десериализация строки.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($id, $secret) = explode('|', $serialized);

        $this->__construct($id, $secret);
    }
}