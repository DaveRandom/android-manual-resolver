<?php

namespace ShortURL;

use URL\IURLFactory,
    URL\IURL,
    Cache\ICacheable;

class Map implements ICacheable
{
    private $urlFactory;

    private $changed = false;

    private $map;

    public function __construct(IURLFactory $urlFactory)
    {
        $this->urlFactory = $urlFactory;
    }

    public function generate()
    {
        $this->map = array();
    }

    public function isChanged()
    {
        return $this->changed;
    }

    public function serialize()
    {
        return json_encode($this->map);
    }

    public function unserialize($data)
    {
        $this->map = json_decode($data, true);
    }

    public function getMapping(IURL $longUrl)
    {
        return isset($this->map[(string) $longUrl]) ? $this->urlFactory->create($this->map[(string) $longUrl]) : null;
    }

    public function setMapping(IURL $longUrl, IURL $shortUrl)
    {
        $this->map[(string) $longUrl] = (string) $shortUrl;
        $this->changed = true;
    }
}
