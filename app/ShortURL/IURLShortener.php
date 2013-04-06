<?php

namespace ShortURL;

use URL\IURL;

interface IURLShortener
{
    public function /* string*/ shorten(IURL $url);
}
