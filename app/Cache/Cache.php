<?php

namespace Cache;

class Cache
{
    private $cacheable;

    private $file;

    private $ttl;

    public function __construct(ICacheable $cacheable, $file, $ttl)
    {
        $this->cacheable = $cacheable;
        $this->file = $file;
        $this->ttl = $ttl;
    }

    public function __destruct()
    {
        if ($this->cacheable->isChanged()) {
            $fp = fopen($this->file, 'w+');
            flock($fp, LOCK_EX);
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $this->cacheable->serialize());
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    public function retrieve()
    {
        $exists = is_file($this->file);
        if ($exists) {
            $fp = fopen($this->file, 'r+');
            flock($fp, LOCK_SH);
        } else {
            $fp = fopen($this->file, 'w+');
            flock($fp, LOCK_EX);
        }

        if (!$exists || ($this->ttl > 0 && filemtime($this->file) + $this->ttl < time())) {
            $this->cacheable->generate();

            if ($exists) {
                flock($fp, LOCK_EX);
            }
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $this->cacheable->serialize());
        } else {
            $this->cacheable->unserialize(stream_get_contents($fp));
        }

        flock($fp, LOCK_UN);
    }
}
