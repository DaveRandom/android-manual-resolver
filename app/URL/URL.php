<?php

namespace URL;

class URL implements IURL
{
    private $scheme;

    private $user;

    private $pass;

    private $host;

    private $port;

    private $path = '/';

    private $query;

    private $fragment;

    private function escapePath($path)
    {
        return implode('/', array_map('rawurlencode', preg_split('#[/\\\\]+#', $path)));
    }

    public function __construct($urlStr)
    {
        if (substr($urlStr, 0, 2) === '//' || substr($urlStr, 0, 3) === '://') {
            // this is a work-around for older versions of PHP not handling schemaless URLs correctly
            $parsed = parse_url('noscheme' . ($urlStr[0] !== ':' ? ':' : '') . $urlStr);
            unset($parsed['scheme']);
        } else {
            $parsed = parse_url($urlStr);
        }

        if (!$parsed) {
            throw new \InvalidArgumentException('Invalid URL string: Parsing failed');
        }

        foreach ($parsed as $name => $value) {
            call_user_func(array($this, 'set' . $name), in_array($name, array('user', 'pass', 'path', 'fragment')) ? urldecode($value) : $value);
        }

        if (isset($this->query)) {
        }
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString($components = IURL::COMP_ALL)
    {
        $result = '';

        if (isset($this->host) && ($components & IURL::COMP_HOST)) {
            if (isset($this->scheme) && ($components & IURL::COMP_SCHEME)) {
                $result .= $this->scheme . ':';
            }
            $result .= '//';

            if (isset($this->user) && ($components & IURL::COMP_AUTH)) {
                $result .= rawurlencode($this->user);
                if (isset($this->pass)) {
                    $result .= ':' . rawurlencode($this->pass);
                }
                $result .= '@';
            }

            $result .= $this->host;
            if (isset($this->port)) {
                $result .= ':' . $this->port;
            }
        }

        if (isset($this->path) && ($components & IURL::COMP_PATH)) {
            $path = $result !== '' && $this->path[0] !== '/' ? '/' . $this->path : $this->path;
            $result .= $this->escapePath($path);
        } else {
            $result .= '/';
        }

        if (isset($this->query) && ($components & IURL::COMP_QUERY)) {
            $result .= '?' . http_build_query($this->query);
        }

        if (isset($this->fragment) && ($components & IURL::COMP_FRAGMENT)) {
            $result .= '#' . rawurlencode($this->fragment);
        }

        return trim($result);
    }

    public function __set($name, $value)
    {
        if (!method_exists($this, 'set' . $name)) {
            throw new \LogicException(get_class($this) . ' does not have a property ' . $name);
        }

        call_user_func(array($this, 'set' . $name), $value);
    }

    public function __get($name)
    {
        if (!method_exists($this, 'get' . $name)) {
            throw new \LogicException(get_class($this) . ' does not have a property ' . $name);
        }
        
        return call_user_func(array($this, 'get' . $name));
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function setScheme($value)
    {
        if ($value !== null) {
            if (!preg_match('/^[a-z][a-z0-9.+\-]*$/i', $value)) {
                throw new \InvalidArgumentException('Invalid URI scheme');
            }

            $value = strtolower($value);
        }

        $this->scheme = $value;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($value)
    {
        if ($value !== null) {
            $value = (string) $value;

            if ($value === '') {
                $value = null;
            }
        }

        $this->user = $value;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function setPass($value)
    {
        if ($value !== null) {
            $value = (string) $value;

            if ($value === '') {
                $value = null;
            }
        }

        $this->pass = $value;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($value)
    {
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $value = '[' . $value . ']';
        } else if (strlen($value) < 254 && preg_match('/^(?:[a-z0-9][a-z0-9\-]*(?<!-)\.)*(?=.*?[a-z].*)[a-z0-9][a-z0-9\-]*(?<!-)$/i', $value)) {
            $value = strtolower($value);
        } else if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $value !== null) {
            throw new \InvalidArgumentException('Invalid host');
        }

        $this->host = $value;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($value)
    {
        if (((int) $value) != $value) {
            throw new \InvalidArgumentException('Invalid port: Not a valid integer');
        } else if ($value < 1 || $value > 65535) {
            throw new \InvalidArgumentException('Invalid port: Outside allowable range 1 - 65535');
        }

        $this->port = (int) $value;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($value)
    {
        if ($value !== null) {
            $value = (string) $value;
        } else {
            $value = '/';
        }

        $this->path = $value;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($value)
    {
        if (is_scalar($value)) {
            parse_str($value, $value);
            if ($value && function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                // undo magic_quotes
                array_walk_recursive($value, function(&$value) {
                    $value = preg_replace('#\\\\([\'"\\\\\\x00])#', '$1', $value);
                });
            }
        } else if (is_object($value) && !get_object_vars($value)) {
            $value = null;
        }

        if (!$value) {
            $value = null;
        }

        $this->query = $value;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function setFragment($value)
    {
        if ($value !== null) {
            $value = (string) $value;
        }

        $this->fragment = $value;
    }
}
