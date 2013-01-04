<?php

namespace ZprGen;

class GeneralItem extends Item
{
    public function __construct($string, $data)
    {
        $this->string = $string;
        $this->priority = array_key_exists('priority', $data) ? $data['priority'] : NULL;
        $this->unit = $data['unit'];
        $this->title = array_key_exists('title', $data) ? $data['title'] : NULL;
    }

}
