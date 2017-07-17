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
namespace Steein\SDK\Authentication;

use Steein\SDK\Application;
use Steein\SDK\Exceptions\ResponseException;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Steein;
use Steein\SDK\SteeinClient;
use Steein\SDK\SteeinRequest;
use Steein\SDK\SteeinResponse;

/**
 * Class OAuth2Client (Авторизация через систему oAuth2)
 *
 * @package Steein\SDK
 */
class OAuth2Client
{

    /**
     * Экземпляр приложения Application
     *
     * @var Application
     */
    protected $app;

    /**
     * Экземпляр приложения SteeinClient
     *
     * @var SteeinClient
     */
    protected $client;

    /**
     * Версия Steein API
     *
     * @var string
     */
    protected $version;

    /**
     * Отправка запросов
     *
     * @var SteeinRequest | null
     */
    protected $lastRequest;

    /**
     * Конструктор
     *
     * @param Application    $app     Экземпляр приложения Application
     * @param SteeinClient   $client  Экземпляр приложения SteeinClient
     * @param string|null    $version Версия Steein API
     */
    public function __construct(Application $app, SteeinClient $client, $version = null)
    {
        $this->app = $app;
        $this->client = $client;
    }

    /**
     * Возвращает последний отправленный запрос SteeinRequest.
     *
     * @return SteeinRequest|null
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Создает авторизационный URL, чтобы начать процесс аутентификации пользователя.
     *
     * @param string $redirectUrl Callback URL  для перенаправления на.
     * @param array  $scope       Массив разрешений для запроса.
     * @param array  $params      Массив параметров для генерации URL.
     * @param string $separator   Разделитель для http_build_query().
     *
     * @return string
     */
    public function getAuthorizationUrl($redirectUrl, $scope = [], $params = [], $separator = '&')
    {
        $params += [
            'client_id'     =>  $this->app->getId(),
            'redirect_uri'  =>  $redirectUrl,
            'response_type' =>  'code',
            'scope'         =>  $this->formatScopes($scope)
        ];

        return $this->app->baseUrl().'/oauth/authorize?'.http_build_query($params, null, $separator);
    }


    /**
     * Получить действительный токен ключ из "code".
     *
     * @param string $code
     * @param string $redirectUri
     *
     * @return AccessToken
     * @throws SteeinSDKException
     */
    public function getAccessTokenFromCode($code, $redirectUri = '')
    {
        $params = [
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Отправляем запрос к конечной точки системы OAuth
     *
     * @param array $params
     *
     * @return AccessToken
     * @throws SteeinSDKException
     */
    protected function requestAnAccessToken($params = [])
    {
        $response = $this->sendRequestWithClientParams('/oauth/token', $params);
        $data = $response->getDecodedBody();

        if (!isset($data['access_token'])) {
            throw new SteeinSDKException('Токен ключ не был возвращен.', 401);
        }

        // Система oAuth возвращает два разных имени ключа для времени истечения
        $expiresAt = 0;

        if (isset($data['expires'])) {
            $expiresAt = time() + $data['expires'];
        } elseif (isset($data['expires_in'])) {
            // Время истечения в секундах будет возвращено как "expires_in".
            $expiresAt = time() + $data['expires_in'];
        }

        return new AccessToken($data['access_token'], $expiresAt);
    }

    /**
     * Отправляем запрос в "oAuth" с токеном доступа к приложению.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     *
     * @return SteeinResponse
     * @throws ResponseException
     */
    protected function sendRequestWithClientParams($endpoint, array $params, $accessToken = null)
    {
        $params += $this->getClientParams();
        $params += [
            'grant_type' => 'authorization_code'
        ];

        $accessToken = $accessToken ?: $this->app->getAccessToken();

        $this->lastRequest = new SteeinRequest($this->app, $accessToken, 'POST', $endpoint, $params, null);
        return $this->client->sendRequest($this->lastRequest);
    }

    /**
     * Возвращает параметры client_* для запросов OAuth.
     *
     * @return array
     */
    protected function getClientParams()
    {
        return [
            'client_id'     => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
        ];
    }

    /**
     * Отформатируйте данные "scopes".
     *
     * @param array $scope
     * @return mixed
     */
    protected function formatScopes($scope = [])
    {
        $implode = \implode(',', $scope);
        return \str_replace(',',' ',$implode);
    }
}