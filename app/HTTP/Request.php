<?php

namespace HTTP;

use \URL\IURL;

class Request extends Message
{
    private $method;

    private $uri;

    public function __construct($method, IURL $uri, HeaderCollection $headers, $body = null, $version = '1.1')
    {
        $this->method  = strtoupper(trim($method));
        $this->uri     = $uri;
        $this->headers = $headers ? $headers : $headerCollectionFactory->create();
        $this->body    = $body;
        $this->version = $version;
    }

    public function __toString()
    {
        $method = $this->method;
        $uri = $this->uri->toString(IURL::COMP_PATH | IURL::COMP_QUERY);
        $version = $this->version;
        if (isset($this->body)) {
            $body = (string) $this->body;
            if (!$this->headers->hasHeader('content-length')) {
                $this->setHeader('content-length', strlen($body));
            }
        } else {
            $body = '';
        }
        $headers = (string) $this->headers;

        return $method . ' ' . $uri . ' HTTP/' . $version . "\r\n"
             . $headers . "\r\n"
             . "\r\n"
             . $body;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = strtoupper(trim($method));
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri(IURL $uri)
    {
        $this->uri = $uri;
    }
}
