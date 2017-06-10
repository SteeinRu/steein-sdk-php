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

/**
 * Class Model
 *
 * @package Steein\SDK
*/
class Model extends Collection
{
    /**
     * Имена объектов
     *
     * @var array
     */
    protected static $objects = [
        //
    ];

    /**
     * Инициализация Модели.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($this->castItems($data));
    }

    /**
     * Итерации по массиву и определение типов каждого узла,
     * возвращает все элементы в виде массива.
     *
     * @param array $data Массив для итерации.
     *
     * @return array
     */
    public function castItems(array $data)
    {
        $items = [];
        foreach ($data as $k => $v) {
            if ($this->shouldCastAsDateTime($k) && (is_numeric($v) || $this->isIso8601DateString($v))) {
                $items[$k] = $this->castToDateTime($v);
            }  else {
                $items[$k] = $v;
            }
        }

        return $items;
    }

    /**
     * Отменяет автоматическое преобразование типов данных.
     * В основном обратная функция castItems().
     *
     * @return array
     */
    public function uncastItems()
    {
        $items = $this->toArray();

        return array_map(function ($v) {
            if ($v instanceof \DateTime) {
                return $v->format(\DateTime::ISO8601);
            }

            return $v;
        }, $items);
    }

    /**
     * Обнаруживает строку в формате ISO 8601.
     *
     * @param string $string
     *
     * @return boolean
     *
     * @see http://www.cl.cam.ac.uk/~mgk25/iso-time.html
     * @see http://en.wikipedia.org/wiki/ISO_8601
     */
    public function isIso8601DateString($string)
    {
        // This insane regex was yoinked from here:
        // http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
        // ...and I'm all like:
        // http://thecodinglove.com/post/95378251969/when-code-works-and-i-dont-know-why
        $crazyInsaneRegexThatSomehowDetectsIso8601 = '/^([\+-]?\d{4}(?!\d{2}\b))'
            . '((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?'
            . '|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d'
            . '|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])'
            . '((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d'
            . '([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

        return preg_match($crazyInsaneRegexThatSomehowDetectsIso8601, $string) === 1;
    }

    /**
     * Определяет, следует ли преобразовывать значение из Model в DateTime.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function shouldCastAsDateTime($key)
    {
        return in_array($key, [
            'created_at',
            'updated_at',
            'start_time',
            'end_time',
            'backdated_time',
            'issued_at',
            'expires_at',
        ], true);
    }

    /**
     * Преобразует значение даты из Model в DateTime.
     *
     * @param int|string $value
     *
     * @return \DateTime
     */
    public function castToDateTime($value)
    {
        if (is_int($value)) {
            $dt = new \DateTime();
            $dt->setTimestamp($value);
        } else {
            $dt = new \DateTime($value);
        }

        return $dt;
    }

    /**
     * Получаем список объектов
     *
     * @return array
     */
    public static function getObjects()
    {
        return static::$objects;
    }
}
