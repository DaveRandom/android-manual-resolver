<?php

namespace HTTP;

class Header
{
    private $name;

    private $value;

    public function __construct($name, $value)
    {
        $this->name = strtolower(trim($name));
        $this->value = $value;
    }

    public function __toString()
    {
        return preg_replace_callback('/(^|\b)[a-z]/', function($match) {
            return strtoupper($match[0]);
        }, $this->name) . ': ' . trim($this->value);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}
