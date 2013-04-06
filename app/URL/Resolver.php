<?php

namespace URL;

class Resolver
{
    private $baseURL;

    private function resolvePath($target, $base)
    {
        if ($target[0] === '/') {
            return $target;
        }

        if ($base[0] !== '/') {
            throw new \LogicException('Cannot resolve two relative paths');
        }
        if ($base[strlen($base) - 1] !== '/') {
            $base = dirname($base);
        }

        foreach (preg_split('#[/\\\\]+#', $target) as $component) {
            switch ($component) {
                case '.':
                    break;
                case '..':
                    $base = dirname($base);
                    break;
                default:
                    $base = rtrim($base, '\\/') . '/' . $component;
                    break;
            }
        }

        if ($base[0] === '\\') {
            $base[0] = '/';
        }

        return $base;
    }

    public function __construct(IURL $baseURL = null)
    {
        $this->baseURL = $baseURL;
    }

    public function resolve(IURL $targetURL, IURL $baseURL = null)
    {
        $baseURL = $baseURL ?: $this->baseURL;

        if ($targetURL->getHost() !== null) {
            $result = clone $targetURL;

            if ($targetURL->getScheme() === null) {
                $result->setScheme($baseURL->getScheme());
            }

            return $result;
        }

        $result = clone $baseURL;

        $result->setPath($this->resolvePath($targetURL->getPath(), $baseURL->getPath()));

        if ($targetURL->getQuery() !== null) {
            $result->setQuery($targetURL->getQuery());
        }
        if ($targetURL->getFragment() !== null) {
            $result->setFragment($targetURL->getFragment());
        }

        return $result;
    }
}
