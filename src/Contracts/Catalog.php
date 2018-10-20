<?php

namespace Viviniko\Catalog\Contracts;

interface Catalog
{
    public function getCategory($id);

    public function getProduct($id);
}