<?php

    use HTTP\RequestFactory,
        HTTP\ResponseFactory,
        HTTP\cURLClient,
        URL\URLFactory,
        URL\Resolver as URLResolver,
        ShortURL\Map as ShortURLMap,
        ShortURL\TinyURL,
        Android\MarkdownGenerator,
        Android\ClassList,
        Cache\Cache;

    // error_reporting(0);

    require __DIR__ . '/config.php';
    require __DIR__ . '/autoload.php';

    $requestFactory = new RequestFactory;
    $httpClient = new cURLClient(new ResponseFactory);

    $urlFactory = new URLFactory;
    $listUrl = $urlFactory->create($baseUrl);
    $urlResolver = new URLResolver($listUrl);
    $urlMap = new ShortURLMap($urlFactory);
    $urlShortener = new TinyURL($urlFactory, $requestFactory, $httpClient, $urlMap);
    $markdownGenerator = new MarkdownGenerator($urlFactory, $urlResolver, $urlShortener);
    $classList = new ClassList($httpClient, $requestFactory, $listUrl, $urlFactory, $urlResolver, $urlShortener, $markdownGenerator);

    $urlCache   = new Cache($urlMap, $urlCacheFile, -1);
    $classCache = new Cache($classList, $classCacheFile, $classCacheTTL);

    if (empty($_GET['name']) && !isset($argv[1])) {
        @header('HTTP/1.1 400 Bad Request');
        exit('No class specified');
    } else if (isset($_GET['name'])) {
        $className = basename($_GET['name']);
    } else {
        $className = basename($argv[1]);
    }

    switch (isset($_GET['mode']) ? $_GET['mode'] : (isset($argv[2]) ? $argv[2] : null)) {
        case 'url':
            $mode = ClassList::QUERY_URL;
            break;
        case 'markdown':
        default:
            $mode = ClassList::QUERY_MARKDOWN;
            break;
    }

    $urlCache->retrieve();
    $classCache->retrieve();

    $result = $classList->query($className, $mode);

    if (!$result) {
        @header('HTTP/1.1 404 Not Found');
        exit("Unable to locate class {$className}");
    }

    if (!empty($_GET['callback'])) {
        $result =  $_GET['callback'] . '(' . json_encode(array('src' => $result)) . ')';
    }

    echo $result;
