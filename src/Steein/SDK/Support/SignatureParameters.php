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

namespace Steein\SDK\Support;

use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\SteeinResponse;
use Steein\SDK\Support\Models\ModelEdge;

/**
 * Class SignatureParameters
 *
 * @package Steein\SDK
 */
abstract class SignatureParameters
{
    /**
     * Базовый класс объектов api.
     *
     * @const string
     */
    const BASE_CLASS = '\\Steein\\SDK\\Support\\Model';

    /**
     * Базовый класс ModelEdge.
     *
     * @const string
     */
    const BASE_EDGE_CLASS = '\\Steein\\SDK\\Support\\ModelEdge';

    /**
     * Префикс объекта api.
     *
     * @const string
     */
    const BASE_OBJECT_PREFIX = '\\Steein\\SDK\\Support\\Models\\';

    /**
     * Объект ответа от Api.
     *
     * @var SteeinResponse
     */
    protected $response;

    /**
     * Расшифрованное тело объекта SteeinResponse Api.
     *
     * @var array
     */
    protected $decodedBody;

    /**
     * Инициализация объекта API.
     *
     * @param SteeinResponse $response
     */
    public function __construct(SteeinResponse $response)
    {
        $this->response = $response;
        $this->decodedBody = $response->getDecodedBody();
    }

    /**
     * Проверяет декодированный объект.
     *
     * @throws SteeinSDKException
     */
    public function validateResponseAsArray()
    {
        if (!is_array($this->decodedBody)) {
            throw new SteeinSDKException('Невозможно получить ответ от Api как массива.', 620);
        }
    }

    /**
     * Безопасное создание экземпляра Model из $subclassName.
     *
     * @param array       $data         Массив данных для итерации.
     * @param string|null $subclassName Подкласс, в который нужно отдать эту коллекцию.
     *
     * @return Model
     *
     * @throws SteeinSDKException
     */
    public function safelyMakeApiNode(array $data, $subclassName = null)
    {
        $subclassName = $subclassName ?: static::BASE_CLASS;
        static::validateSubclass($subclassName);

        // Запомните идентификатор родительского узла
        $parentNodeId = isset($data['id']) ? $data['id'] : null;

        $items = [];

        foreach ($data as $k => $v)
        {
            // Средство массива может быть повторно использовано
            if (is_array($v)) {
                $graphObjectMap = $subclassName::getObjects();
                $objectSubClass = isset($graphObjectMap[$k])
                    ? $graphObjectMap[$k]
                    : null;

                // Может быть ModelEdge или Model
                $items[$k] = $this->castAsModelOrModelEdge($v, $objectSubClass, $k, $parentNodeId);
            } else {
                $items[$k] = $v;
            }
        }

        return new $subclassName($items);
    }

    /**
     * Получить метаданные из списка в ответе на Api.
     *
     * @param array $data
     * @return array
     */
    public function getMetaData(array $data)
    {
        unset($data['data']);
        return $data;
    }

    /**
     * Гарантирует, что рассматриваемый подкласс действителен.
     *
     * @param string $subclassName Подкласс Model для проверки.
     *
     * @throws SteeinSDKException
     */
    public static function validateSubclass($subclassName)
    {
        if ($subclassName == static::BASE_CLASS || is_subclass_of($subclassName, static::BASE_CLASS)) {
            return;
        }

        throw new SteeinSDKException('Данный подкласс "' . $subclassName . '" не действует. Невозможно преобразовать в объект, который не является подклассом Model.', 620);
    }

    /**
     * Принимает массив значений и определяет, как преобразовать каждый узел.
     *
     * @param array       $data         Массив данных для перебора.
     * @param string|null $subclassName Подкласс, в который нужно отдать эту коллекцию.
     * @param string|null $parentKey    The key of this data (Graph edge).
     * @param string|null $parentNodeId Идентификатор родительского узла Api.
     *
     * @return Model|ModelEdge
     *
     * @throws SteeinSDKException
     */
    public function castAsModelOrModelEdge(array $data, $subclassName = null, $parentKey = null, $parentNodeId = null)
    {
        if (isset($data['data']))
        {
            if (static::isCastableAsModelEdge($data['data'])) {
                return $this->safelyMakeModelEdge($data, $subclassName, $parentKey, $parentNodeId);
            }
            // Иногда Api ведет себя странно и возвращает Model под ключом "data"
            $data = $data['data'];
        }

        // Создаем Model
        return $this->safelyMakeModel($data, $subclassName);
    }

    /**
     * Определяет, должны ли данные передаваться как ModelEdge.
     *
     * @param array $data
     *
     * @return boolean
     */
    public static function isCastableAsModelEdge(array $data)
    {
        if ($data === []) {
            return true;
        }

        // Проверяет последовательный числовой массив, который будет ModelEdge
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Возвращает массив Model.
     *
     * @param array       $data         Массив данных для перебора.
     * @param string|null $subclassName Подкласс, в который нужно отдать эту коллекцию.
     * @param string|null $parentKey    The key of this data (Graph edge).
     * @param string|null $parentNodeId Идентификатор родительского узла Api.
     *
     * @return ModelEdge
     *
     * @throws SteeinSDKException
     */
    public function safelyMakeModelEdge(array $data, $subclassName = null, $parentKey = null, $parentNodeId = null)
    {
        if (!isset($data['data'])) {
            throw new SteeinSDKException('Невозможно преобразовать данные в ModelEdge. Ожидается "data" ключ.', 620);
        }

        $dataList = [];
        foreach ($data['data'] as $graphNode) {
            $dataList[] = $this->safelyMakeModel($graphNode, $subclassName);
        }

        $metaData = $this->getMetaData($data);

        $parentGraphEdgeEndpoint = $parentNodeId && $parentKey ? '/' . $parentNodeId . '/' . $parentKey : null;
        $className = static::BASE_EDGE_CLASS;

        return new $className($this->response->getRequest(), $dataList, $metaData, $parentGraphEdgeEndpoint, $subclassName);
    }

    /**
     * Безопасное создание экземпляра Model $subclassName.
     *
     * @param array       $data         Массив данных для перебора.
     * @param string|null $subclassName Подкласс, в который нужно отдать эту коллекцию.
     *
     * @return Model
     *
     * @throws SteeinSDKException
     */
    public function safelyMakeModel(array $data, $subclassName = null)
    {
        $subclassName = $subclassName ?: static::BASE_CLASS;
        static::validateSubclass($subclassName);

        // Запомните идентификатор родительского узла
        $parentNodeId = isset($data['id']) ? $data['id'] : null;

        $items = [];

        foreach ($data as $k => $v) {
            // Средство массива может быть повторно использовано
            if (is_array($v))
            {
                $graphObjectMap = $subclassName::getObjects();
                $objectSubClass = isset($graphObjectMap[$k]) ? $graphObjectMap[$k] : null;

                // Может быть ModelEdge или Model
                $items[$k] = $this->castAsModelOrModelEdge($v, $objectSubClass, $k, $parentNodeId);
            } else {
                $items[$k] = $v;
            }
        }

        return new $subclassName($items);
    }

    /**
     * Проверяет, что возвращаемые данные могут быть переданы как Model.
     *
     * @throws SteeinSDKException
     */
    public function validateResponseCastableAsModel()
    {
        if (isset($this->decodedBody['data']) && static::isCastableAsModelEdge($this->decodedBody['data'])) {
            throw new SteeinSDKException(
                'Невозможно преобразовать ответ от Api в Model, потому что ответ выглядит как ModelEdge. 
                Попробуйте использовать ApiFactory::makeModelEdge().', 620
            );
        }
    }

}