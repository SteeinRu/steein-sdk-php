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
namespace Steein\SDK\Core\Http;

/**
 * Class RawResponse
 *
 * @package Steein\SDK
 */
class RawResponse
{
    /**
     * Заголовки ответов в виде ассоциативного массива.
     *
     * @var array
     */
    protected $headers;

    /**
     * Необработанный результат.
     *
     * @var string
     */
    protected $body;

    /**
     * Код ответа HTTP-статуса.
     *
     * @var int
     */
    protected $httpResponseCode;

    /**
     * Создает новый объект RawResponse.
     *
     * @param string|array $headers        Заголовки в виде строки или массива.
     * @param string       $body           Необработанный результат.
     * @param int          $httpStatusCode Код ответа HTTP (при отправке заголовков в виде разобранного массива).
     */
    public function __construct($headers, $body, $httpStatusCode = null)
    {
        if (is_numeric($httpStatusCode)) {
            $this->httpResponseCode = (int)$httpStatusCode;
        }

        if (is_array($headers)) {
            $this->headers = $headers;
        } else {
            $this->setHeadersFromString($headers);
        }

        $this->body = $body;
    }

    /**
     * Возвращаем заголовки ответа.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Возвращаем необработанный результат
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Возвращаем код ответа HTTP.
     *
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     * Устанавливаем код ответа HTTP из необработанного заголовка.
     *
     * @param string $rawResponseHeader
     */
    public function setHttpResponseCodeFromHeader($rawResponseHeader)
    {
        preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $rawResponseHeader, $match);
        $this->httpResponseCode = (int)$match[1];
    }

    /**
     * Разбераем стандартные заголовки и записываем как массив.
     *
     * @param string $rawHeaders Необработанные заголовки ответа.
     */
    protected function setHeadersFromString($rawHeaders)
    {
        // Нормализовать разрывы строк
        $rawHeaders = str_replace("\r\n", "\n", $rawHeaders);

        // Будут несколько заголовков, если за ними последует 301 или прокси, и т.Д.
        $headerCollection = explode("\n\n", trim($rawHeaders));
        // Мы просто хотим получить последний ответ (в конце)
        $rawHeader = array_pop($headerCollection);

        $headerComponents = explode("\n", $rawHeader);
        foreach ($headerComponents as $line) {
            if (strpos($line, ': ') === false) {
                $this->setHttpResponseCodeFromHeader($line);
            } else {
                list($key, $value) = explode(': ', $line, 2);
                $this->headers[$key] = $value;
            }
        }
    }
}