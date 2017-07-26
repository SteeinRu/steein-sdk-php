<?php
/**
 * You only need this file if you are not using composer.
 * Why are you not using composer?
 * https://getcomposer.org/
 */

if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    throw new Exception('The Steein SDK requires PHP version 5.5 or higher.');
}

require_once __DIR__ . '/functions.php';

/**
 * Register the autoloader for the Steein SDK classes.
 *
 * Based off the official PSR-4 autoloader example found here:
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'Steein\\SDK\\';

    // For backwards compatibility
    $customBaseDir = '';
    // @todo v6: Remove support for 'Steein_SDK_V4_SRC_DIR'
    if (defined('Steein_SDK_V4_SRC_DIR')) {
        $customBaseDir = Steein_SDK_V4_SRC_DIR;
    } elseif (defined('Steein_SDK_SRC_DIR')) {
        $customBaseDir = Steein_SDK_SRC_DIR;
    }
    // base directory for the namespace prefix
    $baseDir = $customBaseDir ?: __DIR__ . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relativeClass = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = rtrim($baseDir, '/') . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});