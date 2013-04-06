<?php

namespace ShortURL;

use URL\IURLFactory,
    URL\IURL,
    HTTP\Client,
    HTTP\RequestFactory;

class TinyURL implements IURLShortener
{
    const API_URL = 'http://tinyurl.com/api-create.php';

    private $urlFactory;

    private $requestFactory;

    private $httpClient;

    private $urlMap;

    public function __construct(IURLFactory $urlFactory, RequestFactory $requestFactory, Client $httpClient, Map $urlMap)
    {
        $this->urlFactory     = $urlFactory;
        $this->requestFactory = $requestFactory;
        $this->httpClient     = $httpClient;
        $this->urlMap         = $urlMap;
    }

    private function createShortUrl(IURL $url)
    {
        $apiUrl = $this->urlFactory->create(static::API_URL . '?url=' . urlencode($url));
        $request = $this->requestFactory->create('GET', $apiUrl);

        $response = $this->httpClient->send($request);
        return $this->urlFactory->create($response->getBody());
    }

    public function shorten(IURL $url)
    {
        $start = microtime(true);

        if (!$result = $this->urlMap->getMapping($url)) {
            $result = $this->createShortUrl($url);
            $this->urlMap->setMapping($url, $result);
        }

        return $result;
    }
}
