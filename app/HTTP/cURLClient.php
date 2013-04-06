<?php

namespace HTTP;

class cURLClient extends Client
{
    private $caFile;

    public function __construct(ResponseFactory $responseFactory, $caFile = null)
    {
        parent::__construct($responseFactory);
        $this->caFile = $caFile;
    }

    public function send(Request $request)
    {
        $url = (string) $request->getUri();
        $method = $request->getMethod();
        $headers = $request->getHeaders()->toArray();
        $version = $request->getVersion() === '1.1' ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0;
        $body = (string) $request->getBody();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION,   $version);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $body);

        if (isset($this->caFile)) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->caFile);
        }

        $result = curl_exec($ch);
        if (!$result) {
            throw new \RuntimeException('Request sending failed: ' . curl_error($ch));
        }

        if (!preg_match('#^(?:\r?\n)*http/(1\.\d)[ \t]+(\d+)[ \t]+([^\r\n]+)\r?\n#i', $result, $matches)) {
            throw new \RuntimeException('Response invalid');
        }

        $version = $matches[1];
        $code = (int) $matches[2];
        $message = $matches[3];

        list($headers, $body) = preg_split('/\r?\n\r?\n/', substr($result, strlen($matches[0])), 2);

        $response = $this->responseFactory->create($code, $message, null, $body, $version);
        $headers = $this->parseHeaders($headers);

        foreach ($headers as $header) {
            $response->getHeaders()->addHeader($header['name'], $header['value']);
        }

        return $response;
    }
}
