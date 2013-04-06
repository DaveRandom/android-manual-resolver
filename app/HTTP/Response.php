<?php

namespace HTTP;

class Response extends Message
{
    private $code;

    private $message;

    public function __construct($code, $message, HeaderCollection $headers, $body = null, $version = '1.1')
    {
        $this->code    = $code;
        $this->message = $message;
        $this->headers = $headers;
        $this->body    = $body;
        $this->version = $version;
    }

    public function __toString()
    {
        $code = $this->code;
        $message = $this->message;
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

        return 'HTTP/' . $version . ' ' . $code . ' ' . $message . "\r\n"
             . $headers . "\r\n"
             . "\r\n"
             . $body;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = (string) $code;
    }

    public function getMessage()
    {
        return $this->uri;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
}
