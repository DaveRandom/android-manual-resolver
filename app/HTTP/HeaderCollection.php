<?php

namespace HTTP;

class HeaderCollection
{
    private $headers = array();

    public function __toString()
    {
        return implode("\r\n", $this->toArray());
    }

    public function toArray()
    {
        $result = array();

        foreach ($this->headers as $header) {
            foreach ($header as $item) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }

    public function addHeader($header, $value = null)
    {
        if (!($header instanceof Header)) {
            $header = new Header((string) $header, $value);
        }

        if (!$this->hasHeader($header)) {
            if (!$this->hasHeader($header->getName())) {
                $this->headers[$header->getName()] = array();
            }

            $this->headers[$header->getName()][] = $header;
        }
    }

    public function removeHeader($header, $value = null)
    {
        $count = 0;

        if ($header instanceof Header) {
            if ($this->hasHeader($header->getName())) {
                foreach ($this->headers[$header->getName()] as $i => $value) {
                    if ($header === $value) {
                        unset($this->headers[$header->getName()][$i]);
                        $count++;
                    }
                }
            }
        } else if ($this->hasHeader($name = strtolower($header))) {
            if (isset($value)) {
                foreach ($this->headers[$name] as $i => $header) {
                    if ($header->getValue() === $value) {
                        unset($this->headers[$name][$i]);
                        $count++;
                    }
                }
            } else {
                $count = count($this->headers[$name]);
                unset($this->headers[$name]);
            }
        }

        return $count;
    }

    public function getHeaderByName($name)
    {
        return $this->hasHeader($name) ? $this->headers[strtolower($name)] : null;
    }

    public function hasHeader($header)
    {
        if ($header instanceof Header) {
            if (isset($this->headers[strtolower($header->getName())])) {
                foreach ($this->headers[strtolower($header->getName())] as $i => $value) {
                    if ($header === $value) {
                        return true;
                    }
                }
            }
        } else {
            return isset($this->headers[strtolower($header)]);
        }

        return false;
    }
}
