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

namespace Steein\SDK\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Steein\SDK\Core\SteeinConfigManager;

class SteeinLogger extends AbstractLogger
{

    /**
     * @var array Indexed list of all log levels.
     */
    private $loggingLevels = array(
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG
    );

    /**
     * Configured Logging Level
     *
     * @var LogLevel $loggingLevel
     */
    private $loggingLevel;

    /**
     * Configured Logging File
     *
     * @var string
     */
    private $loggerFile;

    /**
     * Log Enabled
     *
     * @var bool
     */
    private $isLoggingEnabled;

    /**
     * Logger Name. Generally corresponds to class name
     *
     * @var string
     */
    private $loggerName;

    public function __construct($className)
    {
        $this->loggerName = $className;
        $this->initialize();
    }

    public function initialize()
    {
        $config = SteeinConfigManager::getInstance()->getConfigHashmap();
        if (!empty($config)) {
            $this->isLoggingEnabled = (array_key_exists('log.LogEnabled', $config) && $config['log.LogEnabled'] == '1');
            if ($this->isLoggingEnabled) {
                $this->loggerFile = ($config['log.FileName']) ? $config['log.FileName'] : ini_get('error_log');
                $loggingLevel = strtoupper($config['log.LogLevel']);
                $this->loggingLevel = (isset($loggingLevel) && defined("\\Psr\\Log\\LogLevel::$loggingLevel")) ?
                    constant("\\Psr\\Log\\LogLevel::$loggingLevel") :
                    LogLevel::INFO;
            }
        }
    }

    public function log($level, $message, array $context = array())
    {
        if ($this->isLoggingEnabled) {
            // Checks if the message is at level below configured logging level
            if (array_search($level, $this->loggingLevels) <= array_search($this->loggingLevel, $this->loggingLevels)) {
                error_log("[" . date('d-m-Y H:i:s') . "] " . $this->loggerName . " : " . strtoupper($level) . ": $message\n", 3, $this->loggerFile);
            }
        }
    }
}