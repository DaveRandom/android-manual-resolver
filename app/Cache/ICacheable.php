<?php

namespace Cache;

interface ICacheable
{
    public function /* void */ generate(/* void */);

    public function /* bool */ isChanged(/* void */);

    public function /* string */ serialize(/* void */);

    public function /* void */ unserialize(/* string */ $data);
}
