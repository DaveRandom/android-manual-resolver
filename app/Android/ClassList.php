<?php

namespace Android;

use Cache\ICacheable,
    HTTP\Client,
    HTTP\RequestFactory,
    URL\IURL,
    URL\IURLFactory,
    URL\Resolver as URLResolver,
    ShortURL\IURLShortener;

class ClassList implements ICacheable
{
    const QUERY_MARKDOWN = 1;

    const QUERY_URL = 2;

    const QUERY_BOOL = 4;

    private $httpClient;

    private $requestFactory;

    private $listUrl;

    private $urlFactory;

    private $urlResolver;

    private $urlShortener;

    private $markdownGenerator;

    private $doc;

    private $xpath;

    public function __construct(
        Client $httpClient,
        RequestFactory $requestFactory,
        IURL $listUrl,
        IURLFactory $urlFactory,
        URLResolver $urlResolver,
        IURLShortener $urlShortener,
        MarkdownGenerator $markdownGenerator
    ) {
        $this->httpClient        = $httpClient;
        $this->requestFactory    = $requestFactory;
        $this->listUrl           = $listUrl;
        $this->urlFactory        = $urlFactory;
        $this->urlResolver       = $urlResolver;
        $this->urlShortener      = $urlShortener;
        $this->markdownGenerator = $markdownGenerator;
    }

    private function loadRemoteDocument()
    {
        $request = $this->requestFactory->create('GET', $this->listUrl);
        $response = $this->httpClient->send($request);

        $doc = new \DOMDocument;
        if (!@$doc->loadHTML($response->getBody())) {
            $err = error_get_last();
            throw new \RuntimeException('Failed to load class list from remote source: ' . $err['message']);
        }

        return new \DOMXPath($doc);
    }

    private function createLocalDocument()
    {
        $this->doc = new \DOMDocument('1.0', 'utf-8');
    }

    private function createLocalXPath()
    {
        $this->xpath = new \DOMXPath($this->doc);
    }

    private function makeMarkdownMessage(\DOMElement $element)
    {
        return '[`' . $element->getAttribute('name') . '`](' . $element->getAttribute('href') . ') - ' . $element->getAttribute('desc');
    }

    public function generate()
    {
        set_time_limit(0);

        $this->createLocalDocument();
        $root = $this->doc->appendChild($this->doc->createElement('root'));

        $xpath = $this->loadRemoteDocument();
        $classes = $xpath->query("//td[@class='jd-linkcol']/a");
        foreach ($classes as $class) {
            $el = $this->doc->createElement(strtolower($class->firstChild->data));

            $name = $class->firstChild->data;
            $url = $this->urlResolver->resolve($this->urlFactory->create($class->getAttribute('href')), $this->listUrl);
            $url = $this->urlShortener->shorten($url);
            $desc = $this->markdownGenerator->generate(
                $xpath->query("./td[@class='jd-descrcol']", $class->parentNode->parentNode)->item(0),
                $this->listUrl,
                strlen($name) + strlen($url) + 9
            );

            $el->setAttribute('name', $name);
            $el->setAttribute('href', $url);
            $el->setAttribute('desc', $desc);

            $root->appendChild($el);
        }

        $this->createLocalXPath();
    }

    public function isChanged()
    {
        return false;
    }

    public function serialize()
    {
        return $this->doc->saveXML();
    }

    public function unserialize($data)
    {
        $this->createLocalDocument();
        $this->doc->loadXML($data);
        $this->createLocalXPath();
    }

    public function query($className, $mode = self::QUERY_MARKDOWN)
    {
        if (!isset($this->xpath)) {
            throw new \LogicException('Data must be loaded before a query can be performed');
        }

        if (!$result = $this->xpath->query('/root/' . strtolower($className))->item(0)) {
            return false;
        }

        switch ($mode) {
            case self::QUERY_BOOL:
                return true;

            case self::QUERY_URL:
                return $result->getAttribute('href');
            
            case self::QUERY_MARKDOWN:
            default:
                return $this->makeMarkdownMessage($result);
        }
    }
}
