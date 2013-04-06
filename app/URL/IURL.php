<?php

namespace URL;

interface IURL
{
    const COMP_SCHEME   = 1;
    const COMP_AUTH     = 2;
    const COMP_HOST     = 4;
    const COMP_PATH     = 8;
    const COMP_QUERY    = 16;
    const COMP_FRAGMENT = 32;
    const COMP_ALL      = -1;

    public function /* string */ __toString(/* void */);

    public function /* string */ toString(/* int */ $components = IURL::COMP_ALL);

    public function /* string */ getScheme(/* void */);

    public function /*   void */ setScheme(/* string */ $value);

    public function /* string */ getUser(/* void */);

    public function /*   void */ setUser(/* string */ $value);

    public function /* string */ getPass(/* void */);

    public function /*   void */ setPass(/* string */ $value);

    public function /* string */ getHost(/* void */);

    public function /*   void */ setHost(/* string */ $value);

    public function /*    int */ getPort(/* void */);

    public function /*   void */ setPort(/* int */ $value);

    public function /* string */ getPath(/* void */);

    public function /*   void */ setPath(/* string */ $value);

    public function /* vector */ getQuery(/* void */);

    public function /*   void */ setQuery(/* mixed */ $value);

    public function /* string */ getFragment(/* void */);

    public function /*   void */ setFragment(/* string */ $value);
}
