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
namespace Steein\SDK\Bundler\FileUpload;

use Steein\SDK\Exceptions\SteeinSDKException;

/**
 * Class SteeinFile
 *
 * @package Steein
 */
class SteeinFile
{
    /**
     * Путь к файлу в системе.
     *
     * @var string
     */
    protected $path;

    /**
     * Максимальные байты для чтения. По умолчанию -1 (читать весь оставшийся буфер).
     *
     * @var int
     */
    private $maxLength;

    /**
     * Ищите заданное смещение перед чтением.
     * Если это число отрицательное, поиск не произойдет, и чтение начнется с текущей позиции.
     *
     * @var int
     */
    private $offset;

    /**
     * Поток, указывающий на файл.
     *
     * @var resource
     */
    protected $stream;

    /**
     * Создает конструктор SteeinFile.
     *
     * @param string $filePath
     * @param int $maxLength
     * @param int $offset
     *
     * @throws SteeinSDKException
     */
    public function __construct($filePath, $maxLength = -1, $offset = -1)
    {
        $this->path = $filePath;
        $this->maxLength = $maxLength;
        $this->offset = $offset;
        $this->open();
    }

    /**
     * Закрывает поток при его уничтожении.
     *
     * @var mixed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Открывает поток для файла.
     *
     * @throws SteeinSDKException
     */
    public function open()
    {
        if (!$this->isRemoteFile($this->path) && !is_readable($this->path)) {
            throw new SteeinSDKException('Не удалось создать объект SteeinFile. Невозможно прочитать ресурс: ' . $this->path . '.');
        }

        $this->stream = fopen($this->path, 'r');

        if (!$this->stream) {
            throw new SteeinSDKException('Не удалось создать объект SteeinFile. Не удалось открыть ресурс: ' . $this->path . '.');
        }
    }

    /**
     * Остановка потока файлов.
     *
     * @var mixed
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * Возвращает содержимое файла.
     *
     * @return string
     */
    public function getContents()
    {
        return stream_get_contents($this->stream, $this->maxLength, $this->offset);
    }

    /**
     * Возвращает имя файла.
     *
     * @return string
     */
    public function getFileName()
    {
        return basename($this->path);
    }

    /**
     * Возвращает путь к файлу.
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->path;
    }

    /**
     * Возвращает размер файла.
     *
     * @return int
     */
    public function getSize()
    {
        return filesize($this->path);
    }

    /**
     * Возвращает mimetype файла.
     *
     * @return string
     */
    public function getMimetype()
    {
        return Mimetypes::getInstance()->fromFilename($this->path) ?: 'text/plain';
    }

    /**
     * Возвращает true, если путь к файлу удален.
     *
     * @param string $pathToFile
     *
     * @return boolean
     */
    protected function isRemoteFile($pathToFile)
    {
        return preg_match('/^(https?|ftp):\/\/.*/', $pathToFile) === 1;
    }
}