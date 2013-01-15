<?php

namespace ZprGen;

use Leenter;

class ZprGen
{

    private $board;
    private $parser;
    private $filesPath;

    public function __construct(IBulletinBoard $board, Leenter\Parser $parser, $filesPath)
    {
        $this->board = $board;
        $this->parser = $parser;
        $this->filesPath = $filesPath;
    }

    /**
     * Convert string, save it and convert to pdf
     * @param $string string text to convert
     * @param $postId int post id
     */
    public function savePost($string, $postId)
    {
        $header = file_get_contents(__DIR__ . '/templates/header.tex');
        $footer = file_get_contents(__DIR__ . '/templates/footer.tex');
        $converted = $this->parser->parse($string);
        $this->board->saveLatexFile($header . "\\zpravodaj{XX}{TEST}{X}{X}{X}\n" . $converted . $footer, $this->filesPath . $postId . '/preview.tex');
        $this->board->generatePdf($this->filesPath . $postId . '/preview.tex');
    }

    /**
     * Finalize items of one type of one unit.
     * @param $items Item[] items
     * @param $singleUnit bool items belong to one unit
     * @return string
     */
    private function finalizePart($items, $singleUnit = false)
    {
        $return = '';
        unset($items[0]);
        ksort($items);
        foreach ($items as $item) {
            if ($singleUnit && $item->getUnit() == Item::BOTH) {
                $return .= '\akce{' . $item->getTitle() . '}\begin{odrazky}\item viz. Středisko\end{odrazky}';
            } else {
                $return .= $item;
            }
        }

        return $return;
    }

    /**
     * Generate event table from items
     * @param $items Item[] items
     * @return string
     */
    private function generateEventTable($items)
    {
        $return = '';
        unset($items[0]);
        ksort($items);
        foreach ($items as $item) {
            $return .= $item->getBeginningDate() . "&". $item->getBeginningPlace() ."& {\\bf " . $item->getTitle() . "} ". $item->getEndingPlace() ." \\\\\\hline\n";
        }

        return $return;
    }

    /**
     * Get locative month
     * @param $month int month
     * @return mixed
     */
    private function getLocativeMonth($month)
    {
        $locativeMonth = array(1 => 'LEDNU', 2 => 'ÚNORU', 3 => 'BŘEZNU', 4 => 'DUBNU', 5 => 'KVĚTNU', 6 => 'ČERVNU', 7 => 'ČERVENCI', 8 => 'SRPNU', 9 => 'ZÁŘÍ', 10 => 'ŘÍJNU', 11 => 'LISTOPADU', 12 => 'PROSINCI');

        return $locativeMonth[$month];
    }

    /**
     * Finalize section of one unit
     * @param $general string finalized general items
     * @param $events string finalized events
     * @param $title string title
     * @param $month int month
     * @return string
     */
    private function finalizeSection($general, $events, $title, $month)
    {
        $return = '';
        if (!empty($general) || !empty($events)) {
            $return .= "\n\\" . $title . "\n" . $general;
            if (!empty($events)) {
                $return .= "\n\\nadpis{CO NÁS ČEKÁ V " . $this->getLocativeMonth((int)$month) . "?}\n" . $events;
            }
        }

        return $return;
    }

    /**
     * Prepare all items
     * @param $posts
     * @return array
     */
    private function prepareItems($posts)
    {
        $items = array();

        $items[Item::INTRO][Item::GENERAL][0] = 30;
        $items[Item::BOTH][Item::GENERAL][0] = 30;
        $items[Item::BOTH][Item::EVENT][0] = 30;
        $items[Item::POUTNICI][Item::GENERAL][0] = 30;
        $items[Item::POUTNICI][Item::EVENT][0] = 30;
        $items[Item::LESNISKRITCI][Item::GENERAL][0] = 30;
        $items[Item::LESNISKRITCI][Item::EVENT][0] = 30;

        foreach ($posts as $post) {
            $post = deCodeBB(str_replace(array('&quot;', '&#92;'), array('"', '\\'), $post));
            $iId = substr($post, 0, 3);
            if ($iId != '[X]' && $iId != '[S]' && $iId != '[7]' && $iId != '[8]') continue;

            if (preg_match("/\[X\]\[(\d{2})\/(\d{4})\](\*)?/", $post, $matches) == 1) {
                $month = $matches[1];
                $year = $matches[2];
                if (isset($matches[3]) && $matches[3] == '*') {
                    $multicol = true;
                } else {
                    $multicol = false;
                }
                continue;
            }

            $item = $this->parser->parse($post);

            $itemUnit = $item->getUnit();
            $itemType = get_class($item) == 'ZprGen\GeneralItem' ? Item::GENERAL : Item::EVENT;

            $possiblePriority = $item->getPriority() == 0 ? $items[$itemUnit][$itemType][0] : $possiblePriority = $item->getPriority();
            while(isset($items[$itemUnit][$itemType][$possiblePriority])) $possiblePriority++;

            $items[$itemUnit][$itemType][0] = $itemPriority = $items[$itemUnit][$itemType][0] + 5 - ($possiblePriority % 5);
            $itemPriority = $possiblePriority;
            $items[$itemUnit][$itemType][$itemPriority] = $item;
        }

        return array($items, $multicol, $month, $year);
    }

    /**
     * Generate whole zpravodaj and save it
     * @param $topicId int topic ic
     * @param $postId int post id
     */
    public function saveAll($topicId, $postId)
    {
        // get items
        $posts = $this->board->getAllItems($topicId);

        list($items, $multicol, $month, $year) = $this->prepareItems($posts);

        $finalCode = '';
        $finalCode .= $this->finalizePart($items[Item::INTRO][Item::GENERAL]);

        $finalCode .= $this->finalizeSection($this->finalizePart($items[Item::BOTH][Item::GENERAL]), $this->finalizePart($items[Item::BOTH][Item::EVENT]), 'stredisko', $month);

        $poutniciEventsTable = $this->generateEventTable($items[Item::BOTH][Item::EVENT] + $items[Item::POUTNICI][Item::EVENT]);
        $finalCode .= $this->finalizeSection($this->finalizePart($items[Item::POUTNICI][Item::GENERAL]), $this->finalizePart($items[Item::BOTH][Item::EVENT] + $items[Item::POUTNICI][Item::EVENT], true), 'poutnici', $month);

        $skritciEventsTable = $this->generateEventTable($items[Item::BOTH][Item::EVENT] + $items[Item::LESNISKRITCI][Item::EVENT]);
        $finalCode .= $this->finalizeSection($this->finalizePart($items[Item::LESNISKRITCI][Item::GENERAL]), $this->finalizePart($items[Item::BOTH][Item::EVENT] + $items[Item::LESNISKRITCI][Item::EVENT], true), 'lesniskritci', $month);

        $header = file_get_contents(__DIR__ . '/templates/header.tex');
        $footer = file_get_contents(__DIR__ . '/templates/footer.tex');

        if ($month != 12) {
            $deadline = strtotime($year . '-' . ($month + 1) . '-01 last friday');
        } else {
            $deadline = strtotime(($year + 1) . '-01-01 last friday');
        }

        $header = str_replace('\\begin{multicols*}', '\\begin{multicols}', $header);
        if ($multicol) {
            $footer = str_replace('\\end{multicols*}', '\\end{multicols}\\newpage', $footer);
        } else {
            $footer = str_replace('\\end{multicols*}', '\\end{multicols}', $footer);
        }

        $footer = sprintf($footer, date('j. n. Y', $deadline), date('j. n. Y'), $poutniciEventsTable, $skritciEventsTable);

        if ($month >= 9) {
            $issue = $month -8;
            $volume = $year - 1993;
        } else {
            $issue = $month +4;
            $volume = $year - 1994;
        }

        $this->board->saveLatexFile($header . '\zpravodaj{' . date('j. n. Y') . '}{' . $month . '}{' . $year . '}{' . $volume . '}{' . $issue . '}' . "\n" . $finalCode . $footer, $this->filesPath . $postId . '/final.tex');
        $this->board->generatePdf($this->filesPath . $postId . '/final.tex');
    }

}
