<?php

namespace HTTP;

abstract class Client
{
    protected $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    protected function parseHeaders($header)
    {
        $expr =
        '!
          ^
          ([^()<>@,;:\\"/[\]?={} \t]+)          # Header name
          [ \t]*:[ \t]*
          (
            (?:
              (?:                               # First line of value
                (?:"(?:[^"\\\\]|\\\\.)*"|\S+)   # Quoted string or unquoted token
                [ \t]*                          # LWS
              )*
              (?:                               # Folded lines
                \r?\n
                [ \t]+                          # ...must begin with LWS
                (?:
                  (?:"(?:[^"\\\\]|\\\\.)*"|\S+) # ...followed by quoted string or unquoted tokens
                  [ \t]*                        # ...and maybe some more LWS
                )*
              )*
            )?
          )
          \r?$
        !smx';
        preg_match_all($expr, $header, $matches);

        $result = array();

        for ($i = 0; isset($matches[0][$i]); $i++) {
            $result[] = array(
                'name'  => strtolower($matches[1][$i]),
                'value' => preg_replace('/\s+("(?:[^"\\\\]|\\\\.)*"|\S+)/s', ' $1', trim($matches[2][$i]))
            );
        }

        return $result;
    }

    abstract public function /* Response */ send(Request $request);
}
