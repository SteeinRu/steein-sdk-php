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
namespace Steein\SDK\Url;

/**
 * Class UrlDetectionHandler
 *
 * @package Steein\SDK
 */
class UrlDetectionHandler implements UrlDetectionInterface
{
    /**
     * Возвращает активный URL.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getHttpScheme() . '://' . $this->getHostName() . $this->getServerVar('REQUEST_URI');
    }

    /**
     * Получить действующую в настоящее время схему URL.
     *
     * @return string
     */
    protected function getHttpScheme()
    {
        return $this->isBehindSsl() ? 'https' : 'http';
    }

    /**
     * Пытается определить, работает ли сервер за SSL.
     *
     * @return boolean
     */
    protected function isBehindSsl()
    {
        // Сначала проверьте наличие прокси-сервера
        $protocol = $this->getHeader('X_FORWARDED_PROTO');
        if ($protocol) {
            return $this->protocolWithActiveSsl($protocol);
        }

        $protocol = $this->getServerVar('HTTPS');
        if ($protocol) {
            return $this->protocolWithActiveSsl($protocol);
        }

        return (string)$this->getServerVar('SERVER_PORT') === '443';
    }

    /**
     * Определяет активное значение протокола SSL.
     *
     * @param string $protocol
     *
     * @return boolean
     */
    protected function protocolWithActiveSsl($protocol)
    {
        $protocol = strtolower((string)$protocol);

        return in_array($protocol, ['on', '1', 'https', 'ssl'], true);
    }

    /**
     * Пытается определить имя хоста сервера.
     *
     * Some elements adapted from
     *
     * @see https://github.com/symfony/HttpFoundation/blob/master/Request.php
     *
     * @return string
     */
    protected function getHostName()
    {
        // Сначала проверьте наличие прокси-сервера
        $header = $this->getHeader('X_FORWARDED_HOST');
        if ($header && $this->isValidForwardedHost($header)) {
            $elements = explode(',', $header);
            $host = $elements[count($elements) - 1];
        } elseif (!$host = $this->getHeader('HOST')) {
            if (!$host = $this->getServerVar('SERVER_NAME')) {
                $host = $this->getServerVar('SERVER_ADDR');
            }
        }

        // обрезать и удалять номер порта с хоста
        // host в нижнем регистре согласно RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        //Номер порта
        $scheme = $this->getHttpScheme();
        $port = $this->getCurrentPort();
        $appendPort = ':' . $port;

        //Не добавляйте номер порта, если нормальный порт.
        if (($scheme == 'http' && $port == '80') || ($scheme == 'https' && $port == '443')) {
            $appendPort = '';
        }

        return $host . $appendPort;
    }

    /**
     * Возвращает активный Порт сервера
     *
     * @return string
    */
    protected function getCurrentPort()
    {
        // Сначала проверьте наличие прокси-сервера
        $port = $this->getHeader('X_FORWARDED_PORT');
        if ($port) {
            return (string)$port;
        }

        $protocol = (string)$this->getHeader('X_FORWARDED_PROTO');
        if ($protocol === 'https') {
            return '443';
        }

        return (string)$this->getServerVar('SERVER_PORT');
    }

    /**
     * Возвращает значение из глобальной переменной $_SERVER
     *
     * @param string $key
     *
     * @return string
     */
    protected function getServerVar($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
    }

    /**
     * Возвращает значение из заголовков HTTP-запроса.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getHeader($key)
    {
        return $this->getServerVar('HTTP_' . $key);
    }

    /**
     * Проверяет, является ли значение в X_FORWARDED_HOST допустимым именем хоста
     * Может предотвратить непреднамеренные перенаправления
     *
     * @param string $header
     *
     * @return boolean
     */
    protected function isValidForwardedHost($header)
    {
        $elements = explode(',', $header);
        $host = $elements[count($elements) - 1];

        return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $host) //Проверка правильности символов
            && 0 < strlen($host) && strlen($host) < 254 //Проверка общей длины
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $host); //Длина каждой этикетки
    }
}