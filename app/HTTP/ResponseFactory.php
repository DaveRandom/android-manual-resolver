<?php

namespace HTTP;

class ResponseFactory
{
    public function create($code, $message, HeaderCollection $headers = null, $body = null, $version = '1.1')
    {
        return new Response($code, $message, $headers ?: new HeaderCollection, $body, $version);
    }
}
