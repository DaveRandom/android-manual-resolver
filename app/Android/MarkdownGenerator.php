<?php

namespace Android;

use URL\IURL,
    URL\IURLFactory,
    URL\Resolver as URLResolver,
    ShortURL\IURLShortener;

class MarkdownGenerator
{
    private $urlResolver;

    private $urlFactory;

    private $urlShortener;

    private $lengthLimit;

    private $currentLength;

    private $currentBaseUrl;

    public function __construct(IURLFactory $urlFactory, URLResolver $urlResolver, IURLShortener $urlShortener, $lengthLimit = 485)
    {
        $this->urlResolver = $urlResolver;
        $this->urlFactory = $urlFactory;
        $this->urlShortener = $urlShortener;
        $this->lengthLimit = (int) $lengthLimit;
    }

    private function normalizeWhiteSpace($string)
    {
        return preg_replace('/(?:\s|\xC2?\xA0){2,}|[\r\n]/', ' ', $string);
    }

    private function trim($string)
    {
        return trim($this->normalizeWhiteSpace($string), " \t\x0D\0A\x00\xC2\xA0");
    }

    private function handleNodeList(\DOMNodeList $nodeList)
    {
        $result = '';

        if ($this->currentLength < $this->lengthLimit - 3) {
            foreach ($nodeList as $node) {
                $item = $this->handleNode($node);
                if (preg_match('/^[,.!?\']/i', $item)) {
                    $result = rtrim($result);
                    $this->currentLength -= 1;
                }
                $result .= $item . ' ';
                $this->currentLength += 1;

                if ($this->currentLength >= $this->lengthLimit) {
                    break;
                }
            }

            $result = substr($result, 0, -1);
            $this->currentLength -= 1;
        }

        return $this->normalizeWhiteSpace($result);
    }

    private function handleNode(\DOMNode $node)
    {
        $result = '';

        if ($node instanceof \DOMText) {
            $result = substr($this->trim($node->data), 0, $this->lengthLimit - $this->currentLength);
            $this->currentLength += strlen($result);

            if ($this->currentLength > $this->lengthLimit - 3) {
                $result = substr($result, 0, strlen($result) - (($this->currentLength - $this->lengthLimit) + 3)) . '...';
            }
        } else {
            switch (strtolower($node->tagName)) {
                case 'br':
                    $this->currentLength += 1;
                    $result = ' ';
                    break;

                case 'i':
                    $this->currentLength += 2;
                    $result = '*' . $this->handleNodeList($node->childNodes) . '*';
                    break;

                case 'b': case 'strong':
                    $this->currentLength += 4;
                    $result = '**' . $this->handleNodeList($node->childNodes) . '**';
                    break;

                case 'em': case 'emb':
                    $this->currentLength += 6;
                    $result = '***' . $this->handleNodeList($node->childNodes) . '***';
                    break;

                case 'tt': case 'blockquote':
                    $this->currentLength += 2;
                    $result = '`' . $this->handleNodeList($node->childNodes) . '`';
                    break;

                case 'a':
                    $url = $this->urlResolver->resolve($this->urlFactory->create($node->getAttribute('href')), $this->currentBaseUrl);
                    $url = $this->urlShortener->shorten($url);
                    if ($this->currentLength + strlen($url) + 4 <= $this->lengthLimit) {
                        $this->currentLength += strlen($url) + 4;
                        $result = '[' . $this->handleNodeList($node->childNodes) . '](' . $url . ')';
                    } else {
                        $result = '...';
                    }
                    break;

                case 'p': case 'div': case 'ul':
                    $result = $this->handleNodeList($node->childNodes);
                    break;

                case 'li':
                    $this->currentLength += 2;
                    $result = '# ' . $this->handleNodeList($node->childNodes);
                    break;

                case 'code':
                    if ($node->firstChild instanceof \DOMText) {
                        $this->currentLength += 2;
                        $result = '`' . $this->handleNode($node->firstChild) . '`';
                    } else if ($node->firstChild instanceof \DOMElement && strtolower($node->firstChild->tagName) === 'a') {
                        $text = $this->handleNode($node->firstChild->firstChild);
                        $url = $this->urlResolver->resolve($this->urlFactory->create($node->firstChild->getAttribute('href')), $this->currentBaseUrl);
                        $url = $this->urlShortener->shorten($url);

                        if ($this->currentLength + strlen($url) + 6 <= $this->lengthLimit) {
                            $result = '[`' . $text . '`](' . $url . ')';
                        }
                        $this->currentLength += strlen($url) + 6;
                    }
                    break;
            } 
        }

        return $result;
    }

    public function generate(\DOMNode $node, IURL $baseUrl = null, $currentLength = 0)
    {
        $this->currentLength = (int) $currentLength;
        $this->currentBaseUrl = $baseUrl;

        return $this->handleNodeList($node->childNodes);
    }
}
