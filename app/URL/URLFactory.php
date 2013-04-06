<?php

namespace URL;

class URLFactory implements IURLFactory
{
    public function create($urlStr)
    {
        return new URL($urlStr);
    }
}
