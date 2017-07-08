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

use Steein\SDK\Exceptions\ResponseException;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Interfaces\SteeinResponseInterface;
use Steein\SDK\Support\ModelFactory;
use Steein\SDK\Support\Models\FollowersModel;
use Steein\SDK\Support\Models\FollowingModel;
use Steein\SDK\Support\Models\PostModel;
use Steein\SDK\Support\Models\PostsModel;
use Steein\SDK\Support\Models\UserModel;

/**
 * Class SteeinResponse
 *
 * @package Steein\SDK
 */
class SteeinResponse implements SteeinResponseInterface
{
    /**
     * Состоянии HTTP запросов
     *
     * @var int
     */
    protected $httpStatusCode;

    /**
     * Возвращаем заголовки запросов
     *
     * @var array
     */
    protected $headers;

    /**
     * Необработанный текст результата.
     *
     * @var string
     */
    protected $body;

    /**
     * Декодированное полученного ответа.
     *
     * @var array
     */
    protected $decodedBody = [];

    /**
     * Исходный запрос, который вернул этот ответ.
     *
     * @var SteeinRequest
     */
    protected $request;

    /**
     * Исключение
     *
     * @var SteeinSDKException
     */
    protected $thrownException;

    /**
     * Экземпляр факторного класса для моделей
     *
     * @return \Steein\SDK\Support\ModelFactory
     */
    protected $factory;

    /**
     * Создает новый объект Response.
     *
     * @param SteeinRequest   $request
     * @param string|null     $body
     * @param int|null        $httpStatusCode
     * @param array|null      $headers
     */
    public function __construct(SteeinRequest $request, $body = null, $httpStatusCode = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = $headers;

        //Декодирование результата
        $this->decodeBody();

        //Объявляем фактроный класс
        $this->factory = new ModelFactory($this);
    }

    /**
     * Возвращает исходный запрос, который возвратил этот ответ.
     *
     * @return SteeinRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Возвращаем объект Application, используемый для этого ответа.
     *
     * @return Application
     */
    public function getApp()
    {
        return $this->request->getApp();
    }

    /**
     * Возвращаем Токен доступ.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->request->getAccessToken();
    }

    /**
     * Возвращает код статуса HTTP.
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Возвращает заголовки
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Возвращает результат работы.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Возвращает декодированный результат.
     *
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * Возвращает ETag.
     *
     * @return string|null
     */
    public function getETag()
    {
        return isset($this->headers['ETag']) ? $this->headers['ETag'] : null;
    }

    /**
     * Получаем версию Api, которая вернула этот ответ.
     *
     * @return string|null
     */
    public function getApiVersion()
    {
        return isset($this->headers['Steein-API-Version']) ? $this->headers['Steein-API-Version'] : null;
    }

    /**
     * Returns true if Graph returned an error message.
     *
     * @return boolean
     */
    public function isError()
    {
        return isset($this->decodedBody['error']);
    }

    /**
     * Получаем исключение
     *
     * @throws SteeinSDKException
     */
    public function throwException()
    {
        throw $this->thrownException;
    }

    /**
     * Создает исключение, которое будет выбрано позднее.
     */
    public function makeException()
    {
        $this->thrownException = ResponseException::create($this);
    }

    /**
     * Возвращает исключение
     *
     * @return null|ResponseException|SteeinSDKException
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * Преобразуйте необработанный отклик в массив, если это возможно.
     *
     * Api будет возвращаен в 2 типах:
     * - JSON(P)
     *      Большинство ответов от Api - JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Случается на `/oauth/token` конечная точка при обмене для токена
     */
    public function decodeBody()
    {
        $this->decodedBody = json_decode($this->body, true);

        if ($this->decodedBody === null) {
            $this->decodedBody = [];
            parse_str($this->body, $this->decodedBody);
        } elseif (is_bool($this->decodedBody)) {
            $this->decodedBody = ['success' => $this->decodedBody];
        } elseif (is_numeric($this->decodedBody)) {
            $this->decodedBody = ['id' => $this->decodedBody];
        }

        if (!is_array($this->decodedBody)) {
            $this->decodedBody = [];
        }

        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * Создать экземпляр AbstractApi из ответа.
     *
     * @param string|null $subclassName Подкласс AbstractApi для преобразования в.
     *
     * @return \Steein\SDK\Support\Model
     * @throws SteeinSDKException
     */
    public function getApiObject($subclassName = null)
    {
        return $this->factory->makeObject($subclassName);
    }

    /**
     * Инициализация коллекции UserModel.
     *
     * @return Support\Model|UserModel
     */
    public function getUserModel()
    {
        return $this->factory->makeModel(UserModel::class);
    }

    /***
     * Инициализация коллекции FollowersModel.
     *
     * @return Support\Model|FollowersModel
     */
    public function getFollowersModel()
    {
        return $this->factory->makeModel(FollowersModel::class);
    }

    /***
     * Инициализация коллекции FollowingModel.
     *
     * @return Support\Model|FollowingModel
     */
    public function getFollowingModel()
    {
        return $this->factory->makeModel(FollowingModel::class);
    }

    /***
     * Инициализация коллекции PostModel.
     *
     * @return Support\Model|PostModel
     */
    public function getPostModel()
    {
        return $this->factory->makeModel(PostModel::class);
    }

    /***
     * Инициализация коллекции PostsModel.
     *
     * @return Support\Model|PostsModel
     */
    public function getPostsModel()
    {
        return $this->factory->makeModel(PostsModel::class);
    }

    /***
     * Инициализация коллекции CommentModel.
     *
     * @return Support\Model|CommentModel
     */
    public function getCommentModel()
    {
        return $this->factory->makeModel(CommentModel::class);
    }

}
