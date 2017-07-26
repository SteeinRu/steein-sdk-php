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

use Steein\SDK\Authentication\AccessToken;
use Steein\SDK\Core\FileUpload\SteeinFile;
use Steein\SDK\Core\SteeinConstants;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Core\Http\RequestBodyMultipart;
use Steein\SDK\Core\Http\RequestBodyUrlEncoded;
use Steein\SDK\Url\UrlManipulator;

/**
 * Class SteeinRequest
 *
 * @package Steein\SDK
*/
class SteeinRequest
{
    /**
     * Экземпляр приложения "Application"
     *
     * @var Application
     */
    protected $app;

    /**
     * Токен доступ, который будет использоваться.
     *
     * @var string|null
     */
    protected $accessToken;

    /**
     * HTTP-метод запроса.
     *
     * @var string
     */
    protected $method;

    /**
     * Конечная точка запроса(ссылки).
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Заголовки, которых будут отправлять с запросами
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Параметры, которые будут отправляться
     *
     * @var array
     */
    protected $params = [];

    /**
     * Файлы для отправки.
     *
     * @var array
     */
    protected $files = [];

    /**
     * ETag, чтобы отправить с этим запросом.
     *
     * @var string
     */
    protected $eTag;

    /**
     * Актуальная версия API
     *
     * @var string
     */
    protected $apiVersion;

    /**
     * Создает новый объект запроса.
     *
     * @param Application|null        $app
     * @param AccessToken|string|null $accessToken
     * @param string|null             $method
     * @param string|null             $endpoint
     * @param array|null              $params
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     */
    public function __construct(Application $app, $accessToken, $method, $endpoint, $params, $eTag, $apiVersion = null)
    {
        $this->setApp($app);
        $this->setAccessToken($accessToken);
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
        $this->setETag($eTag);
        $this->apiVersion = $apiVersion ?: null;
    }

    /**
     * Устанавливает токен ключ
     *
     * @param AccessToken|string|null
     *
     * @return SteeinRequest
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        if ($accessToken instanceof AccessToken) {
            $this->accessToken = $accessToken->getValue();
        }

        return $this;
    }

    /**
     * Устанавливает токен ключ с одним собранным из URL или POST-параметрами.
     *
     * @param string $accessToken The access token.
     *
     * @return SteeinRequest
     * @throws SteeinSDKException
     */
    public function setAccessTokenFromParams($accessToken)
    {
        $existingAccessToken = $this->getAccessToken();
        if (!$existingAccessToken) {
            $this->setAccessToken($accessToken);
        } elseif ($accessToken !== $existingAccessToken) {
            throw new SteeinSDKException('Ошибка доступа к токенам. Токен доступа, указанный в запросе SteeinRequest, и токен, указанный в параметрах URL или POST, не совпадают.');
        }

        return $this;
    }

    /**
     * Возвращает токен ключ
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Возвращает токен ключ для этого запроса как объект AccessToken.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenEntity()
    {
        return $this->accessToken ? new AccessToken($this->accessToken) : null;
    }

    /**
     * Установливаем объект Application, используемый для этого запроса.
     *
     * @param Application|null $app
     */
    public function setApp(Application $app = null)
    {
        $this->app = $app;
    }

    /**
     * Возвращает данные "Application"
     *
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Удаляет все файлы из очереди выгрузки.
     */
    public function resetFiles()
    {
        $this->files = [];
    }

    /**
     * Получаем список загружаемых файлов
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Дайте нам знать, есть ли файл с этим запросом.
     *
     * @return boolean
     */
    public function containsFileUploads()
    {
        return !empty($this->files);
    }

    /**
     * Возвращает body запрос как multipart/form-data.
     *
     * @return RequestBodyMultipart
     */
    public function getMultipartBody()
    {
        $params = $this->getPostParams();
        return new RequestBodyMultipart($params, $this->files);
    }

    /**
     * Получаем версию Api
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Проверка, что токен доступа.
     *
     * @throws SteeinSDKException
     */
    public function validateAccessToken()
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new SteeinSDKException('Вы должны указать токен ключ.');
        }
    }

    /**
     * Устанавливаем новый тип метода [GET,POST,DELETE]
     *
     * @param string
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Возвращает метод HTTP.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Проверяем, установлен ли HTTP-метод.
     *
     * @throws SteeinSDKException
     */
    public function validateMethod()
    {
        if (!$this->method) {
            throw new SteeinSDKException('Метод HTTP не указан.');
        }

        if (!in_array($this->method, ['GET', 'POST', 'DELETE'])) {
            throw new SteeinSDKException('Указан недопустимый метод HTTP.');
        }
    }

    /**
     * Устанавливаем новую конечную ссылочную точку
     *
     * @param string
     *
     * @return SteeinRequest
     * @throws SteeinSDKException
     */
    public function setEndpoint($endpoint)
    {
        // Убераем токен доступ от конечной точки, чтобы синхронизировать
        $params = UrlManipulator::getParamsAsArray($endpoint);
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Очищаем секретные данные токена и приложения от конечной точки.
        $filterParams = ['access_token'];
        $this->endpoint = UrlManipulator::removeParamsFromUrl($endpoint, $filterParams);

        return $this;
    }

    /**
     * Возвращает конечную ссылочную точку доступа
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Создавать и возвращать заголовки для этого запроса.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = static::getDefaultHeaders();

        if ($this->eTag) {
            $headers['If-None-Match'] = $this->eTag;
        }

        return array_merge($this->headers, $headers);
    }

    /**
     * Устанавливаем новый заголовок для запросов
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Устанавливает значение eTag.
     *
     * @param string $eTag
     */
    public function setETag($eTag)
    {
        $this->eTag = $eTag;
    }

    /**
     * Установливаем параметры для запроса.
     *
     * @param array $params
     *
     * @return SteeinRequest
     *
     * @throws SteeinSDKException
     */
    public function setParams(array $params = [])
    {
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        unset($params['access_token']);

        $params = $this->sanitizeFileParams($params);
        $this->dangerouslySetParams($params);

        return $this;
    }

    /**
     * Итерация по параметрам и выгрузка файлов.
     *
     * @param array $params
     *
     * @return array
     */
    public function sanitizeFileParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($value instanceof SteeinFile) {
                $this->addFile($key, $value);
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * Задайте параметры для этого запроса, не фильтруя их в первую очередь.
     *
     * @param array $params
     *
     * @return SteeinRequest
     */
    public function dangerouslySetParams(array $params = [])
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Добавляем файл для загрузки.
     *
     * @param string       $key
     * @param SteeinFile $file
     */
    public function addFile($key, SteeinFile $file)
    {
        $this->files[$key] = $file;
    }

    /**
     * Сгенерируйте и возвратите параметры для запроса.
     *
     * @return array
     */
    public function getParams()
    {
        $params = $this->params;

        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $params['access_token'] = $accessToken;
        }

        return $params;
    }

    /**
     * Возвращать параметры только для запросов POST.
     *
     * @return array
     */
    public function getPostParams()
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }

        return [];
    }

    /**
     * Возвращает "body" запрос в URL-кодировке.
     *
     * @return RequestBodyUrlEncoded
     */
    public function getUrlEncodedBody()
    {
        $params = $this->getPostParams();
        return new RequestBodyUrlEncoded($params);
    }

    /**
     * Создаем и возврщаем URL-адрес для запроса.
     *
     * @return string
     */
    public function getUrl()
    {
        $this->validateMethod();

        $apiVersion = UrlManipulator::forceSlashPrefix($this->apiVersion);
        $endpoint   = UrlManipulator::forceSlashPrefix($this->getEndpoint());

        $url = $apiVersion.$endpoint;

        if ($this->getMethod() !== 'POST') {
            $params = $this->getParams();
            $url = UrlManipulator::appendParamsToUrl($url, $params);
        }

        return $url;
    }

    /**
     * Возвращайте заголовки по умолчанию, которые должен использовать каждый запрос.
     *
     * @return array
     */
    public static function getDefaultHeaders()
    {
        return [
            'User-Agent' => SteeinConstants::SDK_NAME . SteeinConstants::SDK_VERSION,
            'Accept-Encoding' => '*',
        ];
    }
}