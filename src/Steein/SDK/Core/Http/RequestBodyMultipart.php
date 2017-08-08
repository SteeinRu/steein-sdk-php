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

use Steein\SDK\Core\FileUpload\SteeinFile;
use Steein\SDK\Interfaces\Http\RequestBodyInterface;

/**
 * Class RequestBodyMultipart
 *
 * @package Steein\SDK
 */
class RequestBodyMultipart implements RequestBodyInterface
{
    /**
     * Граница
     *
     * @var string
     */
    private $boundary;

    /**
     * Параметры для отправки с этим запросом.
     *
     * @var array
     */
    private $params;

    /**
     * Файлы для отправки с этим запросом.
     *
     * @var array
     */
    private $files = [];

    /**
     * @param array  $params   Параметры для отправки с этим запросом.
     * @param array  $files    Файлы для отправки с этим запросом.
     * @param string $boundary Укажите конкретную границу.
     */
    public function __construct(array $params = [], array $files = [], $boundary = null)
    {
        $this->params = $params;
        $this->files = $files;
        $this->boundary = $boundary ?: uniqid();
    }

    /***
     * @inheritdoc
     */
    public function getBody()
    {
        $body = '';

        // Скомпилируйте нормальные параметры
        $params = $this->getNestedParams($this->params);
        foreach ($params as $k => $v) {
            $body .= $this->getParamString($k, $v);
        }

        // Компилировать файлы
        foreach ($this->files as $k => $v) {
            $body .= $this->getFileString($k, $v);
        }

        $body .= "--{$this->boundary}--\r\n";
        return $body;
    }

    /**
     * Возвращает границу
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Возращает строку, необходимую для передачи файла.
     *
     * @param string       $name
     * @param SteeinFile   $file
     *
     * @return string
     */
    private function getFileString($name, SteeinFile $file)
    {
        return sprintf(
            "--%s\r\nContent-Disposition: form-data; name=\"%s\"; filename=\"%s\"%s\r\n\r\n%s\r\n",
            $this->boundary,
            $name,
            $file->getFileName(),
            $this->getFileHeaders($file),
            $file->getContents()
        );
    }

    /**
     * Получите строку, необходимую для передачи поля POST.
     *
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    private function getParamString($name, $value)
    {
        return sprintf(
            "--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r\n",
            $this->boundary,
            $name,
            $value
        );
    }

    /**
     * Возвращает params как массив вложенных параметров.
     *
     * @param array $params
     *
     * @return array
     */
    private function getNestedParams(array $params)
    {
        $query = http_build_query($params, null, '&');
        $params = explode('&', $query);
        $result = [];

        foreach ($params as $param) {
            list($key, $value) = explode('=', $param, 2);
            $result[urldecode($key)] = urldecode($value);
        }

        return $result;
    }

    /**
     * Возвращает необходимые заголовки перед передачей содержимого файла POST.
     *
     * @param SteeinFile $file
     *
     * @return string
     */
    protected function getFileHeaders(SteeinFile $file)
    {
        return "\r\nContent-Type: {$file->getMimetype()}";
    }
}
