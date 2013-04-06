<?php

spl_autoload_register(function($className) {
    static $classMap = array(
        'android\classlist'         => '/app/Android/ClassList.php',
        'android\markdowngenerator' => '/app/Android/MarkdownGenerator.php',
        'cache\cache'               => '/app/Cache/Cache.php',
        'cache\icacheable'          => '/app/Cache/ICacheable.php',
        'http\client'               => '/app/HTTP/Client.php',
        'http\curlclient'           => '/app/HTTP/cURLClient.php',
        'http\header'               => '/app/HTTP/Header.php',
        'http\headercollection'     => '/app/HTTP/HeaderCollection.php',
        'http\message'              => '/app/HTTP/Message.php',
        'http\request'              => '/app/HTTP/Request.php',
        'http\requestfactory'       => '/app/HTTP/RequestFactory.php',
        'http\response'             => '/app/HTTP/Response.php',
        'http\responsefactory'      => '/app/HTTP/ResponseFactory.php',
        'shorturl\iurlshortener'    => '/app/ShortURL/IURLShortener.php',
        'shorturl\map'              => '/app/ShortURL/Map.php',
        'shorturl\tinyurl'          => '/app/ShortURL/TinyURL.php',
        'url\iurl'                  => '/app/URL/IURL.php',
        'url\iurlfactory'           => '/app/URL/IURLFactory.php',
        'url\resolver'              => '/app/URL/Resolver.php',
        'url\url'                   => '/app/URL/URL.php',
        'url\urlfactory'            => '/app/URL/URLFactory.php',
    );

    $className = strtolower($className);
    if (isset($classMap[$className])) {
        require __DIR__ . $classMap[$className];
    }
});
