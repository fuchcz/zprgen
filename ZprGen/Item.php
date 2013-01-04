<?php

namespace ZprGen;

class Item
{

    /** general item */
    const GENERAL = 0;
    /** event item */
    const EVENT = 1;

    /** X type */
    const INTRO = 0;
    /** S type */
    const BOTH = 1;
    /** 7 type */
    const POUTNICI = 2;
    /** 8 type */
    const LESNISKRITCI = 3;

    protected $title;
    protected $unit;
    protected $priority;
    protected $string;

    private function __construct() {}

    public function getTitle()
    {
        return $this->title;
    }

    public function getUnit()
    {
        return $this->unit;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function __toString()
    {
        return $this->string;
    }

}
