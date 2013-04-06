<?php

namespace HTTP;

abstract class Message
{
    protected $version;

    protected $headers;

    protected $body;

    abstract public function __toString();

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = trim($version);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }
}
