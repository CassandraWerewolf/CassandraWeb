<?php
// Minimal replacement for PEAR Cache_Lite (abandoned; does not run on PHP 8).
// Implements only what this app uses: get(), save(), remove(), clean(),
// with the same file naming as Cache_Lite so existing cache folders still work.
// Loaded via include_path (see php/Dockerfile), so `require_once 'Cache/Lite.php'` keeps working.

class Cache_Lite
{
    private $cacheDir = '/tmp/';
    private $lifeTime = 3600;          // seconds; null = never expires
    private $fileNameProtection = true;
    private $hashedDirectoryLevel = 0;
    private $hashedDirectoryUmask = 0700;
    private $caching = true;
    private $id;
    private $group;

    public function __construct($options = array())
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key) && $key !== 'id' && $key !== 'group') {
                $this->$key = $value;
            }
        }
        $this->cacheDir = rtrim($this->cacheDir, '/') . '/';
    }

    public function get($id, $group = 'default', $doNotTestCacheValidity = false)
    {
        $this->id = $id;
        $this->group = $group;
        if (!$this->caching) {
            return false;
        }
        $file = $this->path($id, $group);
        if (!is_file($file)) {
            return false;
        }
        if (!$doNotTestCacheValidity && $this->lifeTime !== null
            && filemtime($file) < time() - $this->lifeTime) {
            return false;
        }
        $raw = @file_get_contents($file);
        if ($raw === false) {
            return false;
        }
        $data = @unserialize($raw, array('allowed_classes' => false));
        return ($data === false && $raw !== serialize(false)) ? false : $data;
    }

    public function save($data, $id = null, $group = 'default')
    {
        if (!$this->caching) {
            return true;
        }
        if ($id === null) {
            $id = $this->id;
            $group = $this->group;
        }
        $file = $this->path($id, $group);
        $dir = dirname($file);
        if (!is_dir($dir) && !@mkdir($dir, $this->hashedDirectoryUmask, true) && !is_dir($dir)) {
            return false;
        }
        $tmp = $file . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, serialize($data), LOCK_EX) === false) {
            return false;
        }
        return @rename($tmp, $file);
    }

    public function remove($id, $group = 'default')
    {
        $file = $this->path($id, $group);
        return is_file($file) ? @unlink($file) : true;
    }

    // clean(): remove every cache file; clean($group): only that group's files.
    public function clean($group = false, $mode = 'ingroup')
    {
        if (!is_dir($this->cacheDir)) {
            return true;
        }
        $prefix = 'cache_';
        if ($group !== false) {
            $prefix .= ($this->fileNameProtection ? md5($group) : $group) . '_';
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cacheDir, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if ($file->isFile() && strpos($file->getFilename(), $prefix) === 0) {
                @unlink($file->getPathname());
            }
        }
        return true;
    }

    // Same file layout as Cache_Lite::_setFileName().
    private function path($id, $group)
    {
        $name = $this->fileNameProtection
            ? 'cache_' . md5($group) . '_' . md5($id)
            : 'cache_' . $group . '_' . $id;
        $root = $this->cacheDir;
        if ($this->hashedDirectoryLevel > 0) {
            $hash = md5($name);
            for ($i = 0; $i < $this->hashedDirectoryLevel; $i++) {
                $root .= 'cache_' . substr($hash, 0, $i + 1) . '/';
            }
        }
        return $root . $name;
    }
}
