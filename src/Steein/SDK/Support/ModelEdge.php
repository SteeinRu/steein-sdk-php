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

use Steein\Common\Collections\Collection;
use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\SteeinRequest;
use Steein\SDK\Url\UrlManipulator;

/**
 * Class ModelEdge
 *
 * @package Steein\SDK
*/
class ModelEdge extends Collection
{
    /**
     * Исходный запрос, который сгенерировал эти данные.
     *
     * @var SteeinRequest
     */
    protected $request;

    /**
     * Массив метаданных Api, таких как разбиение на страницы и.т.д.
     *
     * @var array
     */
    protected $metaData = [];

    /**
     * Конечная точка родительского Api, создавшая список.
     *
     * @var string|null
     */
    protected $parentEdgeEndpoint;

    /**
     * Подкласс дочернего объекта Model.
     *
     * @var string|null
     */
    protected $subclassName;

    /**
     * Инициализация объекта API.
     *
     * @param SteeinRequest   $request            Исходный запрос, который сгенерировал эти данные.
     * @param array           $data               Массив Model
     * @param array           $metaData           Массив метаданных Api, таких как разбиение на страницы и т. Д.
     * @param string|null     $parentEdgeEndpoint Конечная точка родительского Api, создавшая список.
     * @param string|null     $subclassName       Подкласс дочернего объекта Model.
     */
    public function __construct(SteeinRequest $request, array $data = [], array $metaData = [], $parentEdgeEndpoint = null, $subclassName = null)
    {
        $this->request = $request;
        $this->metaData = $metaData;
        $this->parentEdgeEndpoint = $parentEdgeEndpoint;
        $this->subclassName = $subclassName;

        parent::__construct($data);
    }

    /**
     * Получает родительскую конечную точку Api, которая создала список.
     *
     * @return string|null
     */
    public function getParentModelEdge()
    {
        return $this->parentEdgeEndpoint;
    }

    /**
     * Возвращает имя подкласса, которое дочерний Model передается как.
     *
     * @return string|null
     */
    public function getSubClassName()
    {
        return $this->subclassName;
    }

    /**
     * Возвращает необработанные метаданные, связанные с этим ModelEdge.
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Возвращает следующий курсор, если он существует.
     *
     * @return string|null
     */
    public function getNextCursor()
    {
        return $this->getCursor('after');
    }

    /**
     * Возвращает предыдущий курсор, если он существует.
     *
     * @return string|null
     */
    public function getPreviousCursor()
    {
        return $this->getCursor('before');
    }

    /**
     * Возвращает курсор для определенного направления, если он существует.
     *
     * @param string $direction Направление страницы: after|before
     * @return string|null
     */
    public function getCursor($direction)
    {
        if (isset($this->metaData['paging']['cursors'][$direction])) {
            return $this->metaData['paging']['cursors'][$direction];
        }

        return null;
    }

    /**
     * Создает URL-адрес нумерации страниц на основе курсора.
     *
     * @param string $direction The direction of the page: next|previous
     *
     * @return string|null
     * @throws SteeinSDKException
     */
    public function getPaginationUrl($direction)
    {
        $this->validateForPagination();

        // У нас есть URL-адрес подкачки?
        if (!isset($this->metaData['paging'][$direction])) {
            return null;
        }

        $pageUrl = $this->metaData['paging'][$direction];

        return UrlManipulator::baseGraphUrlEndpoint($pageUrl);
    }

    /**
     * Проверяет, можно ли выполнить разбиение на страницы по этому запросу.
     *
     * @throws SteeinSDKException
     */
    public function validateForPagination()
    {
        if ($this->request->getMethod() !== 'GET') {
            throw new SteeinSDKException('Вы можете только разбивать страницы на запрос GET', 720);
        }
    }

    /**
     * Возвращает объект запроса, необходимый для выполнения следующего запроса предыдущей страницы.
     *
     * @param string $direction Направление страницы: next|previous
     *
     * @return SteeinRequest|null
     * @throws SteeinSDKException
     */
    public function getPaginationRequest($direction)
    {
        $pageUrl = $this->getPaginationUrl($direction);
        if (!$pageUrl) {
            return null;
        }

        $newRequest = clone $this->request;
        $newRequest->setEndpoint($pageUrl);

        return $newRequest;
    }

    /**
     * Возвращает объект запроса, необходимый для запроса «следующей» страницы.
     *
     * @return SteeinRequest|null
     * @throws SteeinSDKException
     */
    public function getNextPageRequest()
    {
        return $this->getPaginationRequest('next');
    }

    /**
     * Возвращает объект запроса, необходимый для создания запроса «предыдущей» страницы.
     *
     * @return SteeinRequest|null
     * @throws SteeinSDKException
     */
    public function getPreviousPageRequest()
    {
        return $this->getPaginationRequest('previous');
    }

    /**
     * Общее число результатов согласно Api, если оно существует.
     *
     * Это будет возвращено, если в запросе присутствует модификатор summary=true.
     *
     * @return int|null
     */
    public function getTotalCount()
    {
        if (isset($this->metaData['summary']['total_count'])) {
            return $this->metaData['summary']['total_count'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function maps(\Closure $callback)
    {
        return new static(
            $this->request,
            array_map($callback, $this->items, array_keys($this->items)),
            $this->metaData,
            $this->parentEdgeEndpoint,
            $this->subclassName
        );
    }
}