<?php
class Cache {
    private static $instance = null;
    private $cache_path;
    private $cache_time = 3600; // 1 hour default

    private function __construct() {
        $this->cache_path = __DIR__ . '/../cache/';
        if (!is_dir($this->cache_path)) {
            mkdir($this->cache_path, 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key) {
        $filename = $this->cache_path . md5($key) . '.cache';
        
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $cache = unserialize($content);
            
            if (time() < $cache['expire']) {
                return $cache['data'];
            }
            
            unlink($filename);
        }
        
        return false;
    }

    public function set($key, $data, $time = null) {
        $filename = $this->cache_path . md5($key) . '.cache';
        $cache = [
            'expire' => time() + ($time ?? $this->cache_time),
            'data' => $data
        ];
        
        return file_put_contents($filename, serialize($cache));
    }

    public function delete($key) {
        $filename = $this->cache_path . md5($key) . '.cache';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }

    public function clear() {
        $files = glob($this->cache_path . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
} 