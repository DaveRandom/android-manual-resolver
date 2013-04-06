<?php

namespace HTTP;

use \URL\IURL;

class RequestFactory
{
    public function create($method, IURL $uri, HeaderCollection $headers = null, $body = null, $version = '1.1')
    {
        return new Request($method, $uri, $headers ?: new HeaderCollection, $body, $version);
    }
}
