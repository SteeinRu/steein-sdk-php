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

namespace Steein\SDK\Core;

use Psr\Log\LoggerInterface;
use Steein\SDK\Log\SteeinLogFactory;

/**
 * Simple Logging Manager.
 * This does an error_log for now
 * Potential frameworks to use are PEAR logger, log4php from Apache
 */
class SteeinLoggingManager
{
    /**
     * @var array of logging manager instances with class name as key
     */
    private static $instances = [];

    /**
     * The logger to be used for all messages
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Logger Name
     *
     * @var string
     */
    private $loggerName;

    /**
     * Returns the singleton object
     *
     * @param string $loggerName
     * @return $this
     */
    public static function getInstance($loggerName = __CLASS__)
    {
        if (array_key_exists($loggerName, SteeinLoggingManager::$instances)) {
            return SteeinLoggingManager::$instances[$loggerName];
        }
        $instance = new self($loggerName);
        SteeinLoggingManager::$instances[$loggerName] = $instance;
        return $instance;
    }

    /**
     * Default Constructor
     *
     * @param string $loggerName Generally represents the class name.
     */
    private function __construct($loggerName)
    {
        $config = SteeinConfigManager::getInstance()->getConfigHashmap();
        // Checks if custom factory defined, and is it an implementation of @SteeinLogFactory
        $factory = array_key_exists('log.AdapterFactory', $config) && in_array('Steein\SDK\Log\SteeinLogFactory',
            class_implements($config['log.AdapterFactory'])) ? $config['log.AdapterFactory'] : '\Steein\SDK\Log\SteeinDefaultLogFactory';

        /** @var SteeinLogFactory $factoryInstance */
        $factoryInstance = new $factory();
        $this->logger = $factoryInstance->getLogger($loggerName);
        $this->loggerName = $loggerName;
    }

    /**
     * Log Error
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->logger->error($message);
    }

    /**
     * Log Warning
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->logger->warning($message);
    }

    /**
     * Log Info
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->logger->info($message);
    }

    /**
     * Log Fine
     *
     * @param string $message
     */
    public function fine($message)
    {
        $this->info($message);
    }

    /**
     * Log Debug
     *
     * @param string $message
     */
    public function debug($message)
    {
        $config = SteeinConfigManager::getInstance()->getConfigHashmap();
        // Disable debug in live mode.
        if (array_key_exists('mode', $config) && $config['mode'] != 'live') {
            $this->logger->debug($message);
        }
    }
}