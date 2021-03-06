<?php

/**
 * Annotations
 *
 * Allows the creation of custom annotations in PHP.
 *
 * @category  Library
 * @package   Annotations
 * @author    Axel Nana <ax.lnana@outlook.com>
 * @copyright 2011-2015 Rasmus Schultz <rasmus@mindplay.dk>, 2018 Aliens Group, Inc.
 * @license   LGPL <http://github.com/ElementaryFramework/Annotations/blob/master/LICENSE>
 * @version   0.0.1
 *
 *
 * This file was originally a part of the php-annotation framework.
 *
 * (c) Rasmus Schultz <rasmus@mindplay.dk>
 *
 * <https://github.com/mindplay-dk/php-annotations>
 */

namespace ElementaryFramework\Annotations;

use ElementaryFramework\Annotations\Exceptions\AnnotationException;

/**
 * This class is responsible for storing and updating parsed annotation-data in PHP files.
 */
class AnnotationCache
{
    /**
     * @var string The PHP opening tag (used when writing cache files)
     */
    const PHP_TAG = "<?php\n\n";

    /**
     * @var int The file mode used when creating new cache files
     */
    private $_fileMode;

    /**
     * @var string Absolute path to a folder where cache files will be created
     */
    private $_root;

    /**
     * Initializes the file cache-provider
     *
     * @param string $root absolute path to the root-folder where cache-files will be stored
     * @param int $fileMode file creation mode; defaults to 0777
     */
    public function __construct(string $root, int $fileMode = 0777)
    {
        $this->_root = $root;
        $this->_fileMode = $fileMode;
    }

    /**
     * Check if annotation-data for the key has been stored.
     *
     * @param string $key cache key
     *
     * @return bool true if data with the given key has been stored; otherwise false
     */
    public function exists(string $key): bool
    {
        return \file_exists($this->_getPath($key));
    }

    /**
     * Caches the given data with the given key.
     *
     * @param string $key  The cache key.
     * @param string $code The source-code to be cached
     *
     * @throws AnnotationException if file could not be written
     */
    public function store(string $key, string $code)
    {
        $path = $this->_getPath($key);

        $content = self::PHP_TAG . $code . "\n";

        if (@\file_put_contents($path, $content, LOCK_EX) === false) {
            throw new AnnotationException("Unable to write cache file: {$path}");
        }

        if (@\chmod($path, $this->_fileMode) === false) {
            throw new AnnotationException("Unable to set permissions of cache file: {$path}");
        }
    }

    /**
     * Fetches data stored for the given key.
     *
     * @param string $key The cache key.
     *
     * @return mixed The cached data.
     */
    public function fetch(string $key)
    {
        return include $this->_getPath($key);
    }

    /**
     * Returns the timestamp of the last cache update for the given key.
     *
     * @param string $key The cache key.
     *
     * @return int unix timestamp.
     */
    public function getTimestamp(string $key): int
    {
        return \filemtime($this->_getPath($key));
    }

    /**
     * Maps a cache-key to the absolute path of a PHP file.
     *
     * @param string $key The cache key.
     *
     * @return string The absolute path of the PHP file.
     */
    private function _getPath(string $key): string
    {
        return $this->_root . DIRECTORY_SEPARATOR . $key . '.annotations.php';
    }

    /**
     * Returns absolute path of the folder where cache files are created.
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->_root;
    }
}
