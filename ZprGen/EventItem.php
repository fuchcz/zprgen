<?php

namespace ZprGen;

class EventItem extends Item
{
    private $beginningTime;
    private $beginningPlace;
    private $endingTime;
    private $endingPlace;
    private $endingType;

    public function __construct($string, $data)
    {
        $this->string = $string;
        $this->priority = $data['beginningTime']['tm_year'] * 10000 + $data['beginningTime']['tm_mon'] * 1000 + $data['beginningTime']['tm_mday'] * 100 + $data['beginningTime']['tm_hour'] * 10 + $data['beginningTime']['tm_min'];
        $this->unit = $data['unit'];
        $this->title = $data['title'];
        $this->beginningTime = $data['beginningTime'];
        $this->beginningPlace = $data['beginningPlace'];
        $this->endingTime = key_exists('endingTime', $data) ? $data['endingTime'] : NULL;
        $this->endingPlace = key_exists('endingPlace', $data) ? $data['endingPlace'] : NULL;
        $this->endingType = key_exists('endingType', $data) ? $data['endingType'] : NULL;

    }

    public function getbeginningTime()
    {
        return $this->beginningTime;
    }

    public function getEndingTime()
    {
        return $this->endingTime;
    }

    /**
     * Returns abbreviation of a day in a week
     * @param $day int tm_wday
     * @return string
     */
    private function getDayAbbr($day)
    {
        switch ($day) {
            case 1: return 'po'; break;
            case 2: return 'út'; break;
            case 3: return 'st'; break;
            case 4: return 'čt'; break;
            case 5: return 'pá'; break;
            case 6: return 'so'; break;
            case 0: return 'ne'; break;
        }
    }

    /**
     * Returns beginning date of event item to be used in events table
     * @return string
     */
    public function getBeginningDate()
    {
        return $this->getDayAbbr($this->beginningTime['tm_wday']) . ' ' . $this->beginningTime['tm_mday'] . '. ' . ($this->beginningTime['tm_mon'] + 1) . '.';
    }

    /**
     * Returns ending date of event item to be used in events table
     * @return string
     */
    public function getEndingDate()
    {
        if (isset($this->endingTime)) {
            return $this->getDayAbbr($this->endingTime['tm_wday']) . ' ' . $this->endingTime['tm_mday'] . '. ' . ($this->endingTime['tm_mon'] + 1) . '.';
        } else {
            return '';
        }
    }

    /**
     * Returns beginning place of event item to be used in events table
     * @return string
     */
    public function getBeginningPlace()
    {
        return $this->beginningTime['tm_hour'] . ':' . sprintf("%02d", $this->beginningTime['tm_min']) . ' ' . $this->beginningPlace;
    }

    /**
     * Returns ending place of event item to be used in events table
     * @return string
     */
    public function getEndingPlace()
    {
        if (isset($this->endingTime)) {
            return $this->endingType . ' v ' . $this->endingTime['tm_hour'] . ':' . sprintf("%02d", $this->endingTime['tm_min']) . ' ' . $this->endingPlace;
        } else {
            return '';
        }
    }

}
