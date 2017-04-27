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
namespace Steein\SDK\Http\Helpers;

use Steein\SDK\Authentication\AccessToken;
use Steein\SDK\Authentication\OAuth2Client;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Url\UrlDetectionInterface;

/**
 * Class RedirectOAuth
 *
 * @package Steein\SDK
 */
class RedirectOAuth
{
    /**
     * Клиентский сервис OAuth 2.0.
     *
     * @var OAuth2Client
     */
    protected $oAuth2Client;

    /**
     * Обработчик обнаружения URL.
     *
     * @var UrlDetectionInterface
     */
    protected $urlDetectionHandler;

    /**
     * Клиентский сервис OAuth 2.0.
     *
     * @param OAuth2Client $oAuth2Client
     */
    public function __construct(OAuth2Client $oAuth2Client)
    {
        $this->oAuth2Client = $oAuth2Client;
    }

    /**
     * Сохраняет состояние CSRF и возвращает URL, на который должен быть отправлен пользователь.
     *
     * @param string $redirectUrl Ссылка которая должна перенаправлять пользователей после входа в систему.
     * @param array  $scope       Список разрешений для запроса во время входа в систему.
     * @param array  $params      Массив параметров для генерации URL.
     * @param string $separator   Сепаратор для использования в http_build_query().
     *
     * @return string
     */
    private function makeUrl($redirectUrl, array $scope, array $params = [], $separator = '&')
    {
        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Возвращает URL-адрес, чтобы отправить пользователя для входа в Steein.
     *
     * @param string $redirectUrl Ссылка которая должна перенаправлять пользователей после входа в систему.
     * @param array  $scope       Список разрешений для запроса во время входа в систему.
     * @param string $separator   Сепаратор для использования в http_build_query().
     *
     * @return string
     */
    public function loginForm($redirectUrl, array $scope = [], $separator = '&')
    {
        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    /**
     * Принимает действительный код из редиректа входа и возвращает объект AccessToken.
     *
     * @param string|null $redirectUrl URL переадресации.
     *
     * @return AccessToken|null
     *
     * @throws SteeinSDKException
     */
    public function getAccessToken($redirectUrl = null)
    {
        if (!$code = $this->getCode()) {
            return null;
        }

        return $this->oAuth2Client->getAccessTokenFromCode($code, $redirectUrl);
    }

    /**
     * Возвращает код.
     *
     * @return string|null
     */
    protected function getCode()
    {
        return $this->getInput('code');
    }

    /**
     * Возвращает код ошибки.
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->getInput('error_code');
    }

    /**
     * Возвращает ошибку
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->getInput('error');
    }

    /**
     * Возвращает причину ошибки.
     *
     * @return string|null
     */
    public function getErrorReason()
    {
        return $this->getInput('error_reason');
    }

    /**
     * Возвращает описание ошибки.
     *
     * @return string|null
     */
    public function getErrorDescription()
    {
        return $this->getInput('error_description');
    }

    /**
     * Возвращает значение из параметра $_GET.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getInput($key)
    {
        //filter_input(INPUT_GET, $key, \FILTER_SANITIZE_STRING)
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }
}