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

/**
 * Class SteeinConfigManager
 *
 * SteeinConfigManager loads the SDK configuration file and
 * hands out appropriate config params to other classes
 *
 * @package Steein\SDK\Core
 */
class SteeinConfigManager
{
    /**
     * Configuration Options
     *
     * @var array
     */
    private $configs = [
        //
    ];

    /**
     * Singleton Object
     *
     * @var $this
     */
    private static $instance;

    /**
     * Private Constructor
     */
    private function __construct()
    {
        if (defined('STEEIN_CONFIG_PATH')) {
            $configFile = constant('STEEIN_CONFIG_PATH') . '/sdk-config.ini';
        } else {
            $configFile = implode(DIRECTORY_SEPARATOR,
                array(dirname(__FILE__), "..", "config", "sdk-config.ini"));
        }
        if (\file_exists($configFile)) {
            $this->addConfigFromIni($configFile);
        }
    }

    /**
     * Returns the singleton object
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add Configuration from configuration.ini files
     *
     * @param string $fileName
     * @return $this
     */
    public function addConfigFromIni($fileName)
    {
        if ($configs = parse_ini_file($fileName)) {
            $this->addConfigs($configs);
        }
        return $this;
    }

    /**
     * If a configuration exists in both arrays,
     * then the element from the first array will be used and
     * the matching key's element from the second array will be ignored.
     *
     * @param array $configs
     * @return $this
     */
    public function addConfigs($configs = array())
    {
        $this->configs = $configs + $this->configs;
        return $this;
    }

    /**
     * Simple getter for configuration params
     * If an exact match for key is not found,
     * does a "contains" search on the key
     *
     * @param string $searchKey
     * @return array
     */
    public function get($searchKey)
    {
        if (array_key_exists($searchKey, $this->configs)) {
            return $this->configs[$searchKey];
        } else {
            $arr = array();
            if ($searchKey !== '') {
                foreach ($this->configs as $k => $v) {
                    if (strstr($k, $searchKey)) {
                        $arr[$k] = $v;
                    }
                }
            }

            return $arr;
        }
    }

    /**
     * Utility method for handling account configuration
     * return config key corresponding to the API userId passed in
     *
     * If $userId is null, returns config keys corresponding to
     * all configured accounts
     *
     * @param string|null $userId
     * @return array|string
     */
    public function getIniPrefix($userId = null)
    {
        if ($userId == null) {
            $arr = array();
            foreach ($this->configs as $key => $value) {
                $pos = strpos($key, '.');
                if (strstr($key, "acct")) {
                    $arr[] = substr($key, 0, $pos);
                }
            }
            return array_unique($arr);
        } else {
            $iniPrefix = array_search($userId, $this->configs);
            $pos = strpos($iniPrefix, '.');
            $acct = substr($iniPrefix, 0, $pos);

            return $acct;
        }
    }

    /**
     * returns the config file hashmap
     */
    public function getConfigHashmap()
    {
        return $this->configs;
    }

    /**
     * Disabling __clone call
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}