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
namespace Steein\SDK\Exceptions;

use Steein\SDK\SteeinResponse;

/**
 * Class ResponseException
 *
 * Класс исключений, который будет контролировать
 * все полученные данные с сервера или с другие потоков..
 *
 * @package Steein\SDK
*/
class ResponseException extends SteeinSDKException
{
    /**
     * Ответ, вызвавший исключение.
     *
     * @var SteeinResponse
     */
    protected $response;

    /**
     * Декодированный результат.
     *
     * @var array
     */
    protected $responseData;

    /**
     * Создаем систему исключений
     *
     * @param SteeinResponse     $response          Ответ, вызвавший исключение.
     * @param SteeinSDKException $previousException Более подробное исключение.
     */
    public function __construct(SteeinResponse $response, SteeinSDKException $previousException = null)
    {
        $this->response = $response;
        $this->responseData = $response->getDecodedBody();

        $errorMessage = $this->get('message', 'Неизвестная ошибка из Api.');
        $errorCode = $this->get('code', -1);

        parent::__construct($errorMessage, $errorCode, $previousException);
    }

    /**
     * Фабрика для создания соответствующего исключения на основе результатов от Api.
     *
     * @param SteeinResponse $response Ответ, вызвавший исключение.
     *
     * @return ResponseException
     */
    public static function create(SteeinResponse $response)
    {
        $data = $response->getDecodedBody();

        if (!isset($data['error']['code']) && isset($data['code'])) {
            $data = ['error' => $data];
        }

        $code = isset($data['error']['code']) ? $data['error']['code'] : null;
        $message = isset($data['error']['message']) ? $data['error']['message'] : 'Неизвестная ошибка из Api.';

        if (isset($data['error']['error_subcode']))
        {
            switch ($data['error']['error_subcode'])
            {
                // Другие проблемы с аутентификацией
                case 458:
                case 459:
                case 460:
                case 463:
                case 464:
                case 467:
                    return new static($response, new AuthenticationException($message, $code));
            }
        }

        switch ($code) {
            // Статус входа или токен устарел, отозван или недействителен
            case 100:
            case 102:
            case 190:
                return new static($response, new AuthenticationException($message, $code));

            // Проблема сервера
            case 1:
            case 2:
                return new static($response, new ServerException($message, $code));

            // Дросселирование API
            case 4:
            case 17:
            case 341:
                return new static($response, new ThrottleException($message, $code));

            // Повторяющаяся запись
            case 506:
                return new static($response, new ClientException($message, $code));
        }

        // Отсутствующие разрешения
        if ($code == 10 || ($code >= 200 && $code <= 299)) {
            return new static($response, new AuthorizationException($message, $code));
        }

        // OAuth ошибка аутентификации
        if (isset($data['error']['type']) && $data['error']['type'] === 'OAuthException') {
            return new static($response, new AuthenticationException($message, $code));
        }

        // Все остальные
        return new static($response, new DefaultException($message, $code));
    }

    /**
     * Проверяет isset и возвращает это значение или значение по умолчанию.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function get($key, $default = null)
    {
        if (isset($this->responseData['error'][$key])) {
            return $this->responseData['error'][$key];
        }

        return $default;
    }

    /**
     * Возвращает код статуса HTTP
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->response->getHttpStatusCode();
    }

    /**
     * Возвращает код дополнительной ошибки.
     *
     * @return int
     */
    public function getSubErrorCode()
    {
        return $this->get('error_subcode', -1);
    }

    /**
     * Возвращает тип ошибки
     *
     * @return string
     */
    public function getErrorType()
    {
        return $this->get('type', '');
    }

    /**
     * Возвращает исходный отклик
     *
     * @return string
     */
    public function getRawResponse()
    {
        return $this->response->getBody();
    }

    /**
     * Возвращает декодированный ответ
     *
     * @return array
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * Возвращает объект ответа
     *
     * @return SteeinResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}