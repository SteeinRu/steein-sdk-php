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

use Steein\Common\Collections\Collection;
use Steein\SDK\Authentication\AccessToken;
use Steein\SDK\Authentication\OAuth2Client;
use Steein\SDK\Bundler\FileUpload\SteeinFile;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Http\Helpers\RedirectOAuth;
use Steein\SDK\HttpClients\HttpClientsFactory;
use Steein\SDK\Interfaces\SteeinInterface;

/**
 * Class Steein
 *
 * @package Steein
 */
class Steein implements SteeinInterface
{
    /**
     * Экземпляр приложения Application.
     *
     * @var \Steein\SDK\Application
    */
    protected $app;

    /**
     * Экземпляр приложения SteeinClient.
     *
     * @var \Steein\SDK\SteeinClient
    */
    protected $client;

    /**
     * Токен ключ по умолчанию.
     *
     * @var \Steein\SDK\Authentication\AccessToken
     */
    protected $defaultAccessToken;

    /**
     * Версия которая будет использована в oAuth.
     *
     * @var string|null
     */
    protected $defaultApiVersion;

    /**
     * Экземпляр приложения "Collection" для работы с массивами данных.
     *
     * @var \Steein\Common\Collections\Collection
    */
    protected $collect;

    /**
     * Клиентский сервис OAuth 2.0.
     *
     * @var \Steein\SDK\Authentication\OAuth2Client
     */
    protected $oAuth2Client;

    /**
     * Сохраняет последний запрос, сделанный в Graph.
     *
     * @var \Steein\SDK\SteeinResponse
     */
    protected $lastResponse;

    /**
     * Конструктор основного суперкласса Steein.
     *
     * @param array $config
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function __construct($config = [])
    {
        if(empty($config)) {
            $config = $this->defaultConfig();
        }

        $this->collect = Collection::instance($this->defaultConfig());


        //Объединяем конфигурационные массивы
        $config = $this->collect->merge($config);

        if(!$config['client_id']) {
            throw new SteeinSDKException("Ключ \"client_id\" не указан");
        }

        if(!$config['client_secret']) {
            throw new SteeinSDKException("Секретный ключ \"client_secret\" ");
        }

        //Начинаем работу
        $this->app = new Application($config->get('client_id'), $config->get('client_secret'));
        $this->client = new SteeinClient(
            HttpClientsFactory::createHttpClient()
        );

        //Проверяем токен ключ
        if (isset($config['default_access_token'])) {
            $this->setDefaultAccessToken($config['default_access_token']);
        }

        $this->defaultApiVersion = '/api/'.$config['default_api_version'];
    }

    /**
     * Возращаем данные "Application"
     *
     * @return \Steein\SDK\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Возвращаем службу SteeinClient.
     *
     * @return \Steein\SDK\SteeinClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Возвращает логин для перенаправления.
     *
     * @return \Steein\SDK\Http\Helpers\RedirectOAuth
     */
    public function redirectOAuth()
    {
        return new RedirectOAuth($this->getOAuth2Client());
    }

    /**
     * Отправляет GET-запрос в Api и возвращает результат.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return \Steein\SDK\SteeinResponse
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function get($endpoint, $params = [], $eTag = null, $apiVersion = null)
    {
        return $this->sendRequest('GET', $endpoint, $params, $eTag, $apiVersion);
    }

    /**
     * Отправляет POST-запрос в Api и возвращает результат.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return \Steein\SDK\SteeinResponse
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function post($endpoint, $params = [], $eTag = null, $apiVersion = null)
    {
        return $this->sendRequest('POST', $endpoint, $params, $eTag, $apiVersion);
    }

    /**
     * Отправляет DELETE-запрос в Api и возвращает результат.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return \Steein\SDK\SteeinResponse
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function delete($endpoint, $params = [], $eTag = null, $apiVersion = null)
    {
        return $this->sendRequest('DELETE', $endpoint, $params, $eTag, $apiVersion);
    }

    /**
     * Отправляет запросы в Api и возвращает результаты.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return \Steein\SDK\SteeinResponse
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function sendRequest($method, $endpoint = null, $params = [], $eTag = null, $apiVersion = null)
    {
        $apiVersion = $apiVersion ?: $this->defaultApiVersion;
        $request = $this->request($method, $endpoint, $params, $eTag, $apiVersion);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Создает новый объект SteeinRequest.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return \Steein\SDK\SteeinRequest
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function request($method, $endpoint, array $params = [], $eTag, $apiVersion = null)
    {
        return new SteeinRequest(
            $this->app,
            $this->getDefaultAccessToken(),
            $method,
            $endpoint,
            $params,
            $eTag,
            $apiVersion
        );
    }

    /**
     * Фабрика для создания Steein File.
     *
     * @param string $pathToFile
     *
     * @return \Steein\SDK\Bundler\FileUpload\SteeinFile
     *
     * @throws \Steein\SDK\Exceptions\SteeinSDKException
     */
    public function fileToUpload($pathToFile)
    {
        return new SteeinFile($pathToFile);
    }

    /**
     * Возвращает клиентский сервис OAuth 2.0.
     *
     * @return \Steein\SDK\Authentication\OAuth2Client
     */
    public function getOAuth2Client()
    {
        if (!$this->oAuth2Client instanceof OAuth2Client) {
            $app = $this->getApp();
            $client = $this->getClient();
            $this->oAuth2Client = new OAuth2Client($app, $client, $this->getDefaultApiVersion());
        }

        return $this->oAuth2Client;
    }

    /**
     * Возвращает версию SteeinAPI.
     *
     * @return string
     */
    public function getDefaultApiVersion()
    {
        return $this->defaultApiVersion;
    }

    /**
     * Возвращает объект AccessToken.
     *
     * @return \Steein\SDK\Authentication\AccessToken
     */
    public function getDefaultAccessToken()
    {
        return $this->defaultAccessToken;
    }

    /**
     * Устанавливает токен доступа по умолчанию
     *
     * @param \Steein\SDK\Authentication\AccessToken
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function setDefaultAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->defaultAccessToken = new AccessToken($accessToken);
        }
        if ($accessToken instanceof AccessToken) {
            $this->defaultAccessToken = $accessToken;
        }

       // throw new \InvalidArgumentException('Токен ключ по умолчанию должен быть типа string или \Steein\SDK\Authentication\AccessToken');
    }

    /**
     * Конфигурационный настройки по умолчанию
     *
     * @return array
    */
    protected function defaultConfig()
    {
        return [
            'client_id'             =>  config('acct1.ClientId'),
            'client_secret'         =>  config('acct1.ClientSecret'),
            'default_api_version'   =>  config('acct1.VersionApi'),
            'default_access_token'  =>  null
        ];
    }
}