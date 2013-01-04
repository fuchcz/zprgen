<?php

namespace ZprGen;

use Leenter;

class ZprGenParser extends Leenter\Parser
{

    private $data;

    public function __construct()
    {
        parent::__construct('[', ']', '/');

        /*
         * preprocessor
         */
        $this->preprocessor->registerPattern("/\\\/m", "\\textbackslash "); // remove backslashes
        // general
        $this->preprocessor->registerPattern('/(\[[XS78]\](?:\[\d{1,2}\])?)(.*?)\n/', "$1[title]$2[/title]\n"); // title
        // lists and list items
        $this->preprocessor->registerPattern('/\n\*/m', "\n[li]"); // list items opening
        $this->preprocessor->registerPattern('/"(.*?)"/', "[quote]$1[/quote]"); // quotes
        $this->preprocessor->registerPattern('/(\[li\](.*?))\n/m', "$1[/li]\n"); // list item closing
        $this->preprocessor->registerPattern('/(?<!\[\/li\])\n\[li\]/', "\n[list][li]"); // list opening
        $this->preprocessor->registerPattern('/\[\/li\]\n(?!\[li\])/', "[/li][/list]\n"); // list closing
        // tables, table rows, table cells
        $this->preprocessor->registerPattern('/\n\|(\d*[Xcrl]?[i]?)/', "\n[tr][td]$1"); // table row opening
        $this->preprocessor->registerPattern('/\|\n/', "[/td][/tr]\n"); // table row closing
        $this->preprocessor->registerPattern('/\|(\d*[Xcrl]?[i]? )/', "[/td][td]$1"); // table cell divider
        $this->preprocessor->registerPattern('/(?<!\[\/tr\])\n\[tr\]/', "\n[table][tr]"); // table opening
        $this->preprocessor->registerPattern('/\[\/tr\]\n(?!\[tr\])/', "[/tr][/table]\n"); // table closing
        // special chars
        $this->preprocessor->registerPattern('/\#/m', '\#');
        $this->preprocessor->registerPattern('/\$/m', '\$');
        $this->preprocessor->registerPattern('/\%/m', '\%');
        $this->preprocessor->registerPattern('/\_/m', '\_');
        $this->preprocessor->registerPattern('/\&/m', '\&');
        $this->preprocessor->registerPattern('/\{/m', '\{');
        $this->preprocessor->registerPattern('/\}/m', '\}');
        $this->preprocessor->registerPattern('/\^/m', '\^');
        // begin & end datetime
        $this->preprocessor->registerPattern('/((\[li\] \[b\]sraz:\[\/b\] )\[(\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}\])) (.*)(\[\/li\])/', "$2[BT $3 [bp]$4[/bp]$5");
        $this->preprocessor->registerPattern('/((\[li\] \[b\](konec|návrat):\[\/b\] )\[(\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}\])) (.*)(\[\/li\])/', "$2[ET $4 [ep]$5[/ep]$6");

        /*
         * parser
         */
        $this->parser->registerTag(new Leenter\Tag('T_UNIT_ID', Leenter\Tag::SINGLE, '[XS78]', array('T_DOCUMENT')));
        $this->parser->registerTag(new Leenter\Tag('T_ZPRAVODAJ_ID', Leenter\Tag::SINGLE, '\d{1,2}\/\d{4}', array('T_DOCUMENT')));
        $this->parser->registerTag(new Leenter\Tag('T_PRIORITY', Leenter\Tag::SINGLE, '\d{1,2}', array('T_DOCUMENT')));
        $this->parser->registerTag(new Leenter\Tag('T_TITLE', Leenter\Tag::BLOCK, 'title', array('T_DOCUMENT')));
        $this->parser->registerTag(new Leenter\Tag('T_DATETIME', Leenter\Tag::SINGLE, '\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}', array(Leenter\Tag::BLOCK, Leenter\Tag::INLINE)));
        $this->parser->registerTag(new Leenter\Tag('T_BTDATETIME', Leenter\Tag::SINGLE, 'BT \d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}', array('T_ITEM')));
        $this->parser->registerTag(new Leenter\Tag('T_ETDATETIME', Leenter\Tag::SINGLE, 'ET \d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{2}', array('T_ITEM')));
        $this->parser->registerTag(new Leenter\Tag('T_BPLACE', Leenter\Tag::INLINE, 'bp', array('T_ITEM')));
        $this->parser->registerTag(new Leenter\Tag('T_EPLACE', Leenter\Tag::INLINE, 'ep', array('T_ITEM')));
        $this->parser->registerTag(new Leenter\Tag('T_TIME', Leenter\Tag::SINGLE, '\d{1,2}:\d{2}', array(Leenter\Tag::BLOCK, Leenter\Tag::INLINE)));
        $this->parser->registerTag(new Leenter\Tag('T_LIST', Leenter\Tag::BLOCK, 'list', array(Leenter\Tag::BLOCK)));
        $this->parser->registerTag(new Leenter\Tag('T_ITEM', Leenter\Tag::BLOCK, 'li', array(Leenter\Tag::BLOCK, 'T_LIST')));
        $this->parser->registerTag(new Leenter\Tag('T_BOLD', Leenter\Tag::INLINE, 'b', array(Leenter\Tag::BLOCK, Leenter\Tag::INLINE)));
        $this->parser->registerTag(new Leenter\Tag('T_ITALIC', Leenter\Tag::INLINE, 'i', array(Leenter\Tag::BLOCK, Leenter\Tag::INLINE)));
        $this->parser->registerTag(new Leenter\Tag('T_UNDERLINE', Leenter\Tag::INLINE, 'u', array(Leenter\Tag::BLOCK, Leenter\Tag::INLINE)));
        $this->parser->registerTag(new Leenter\Tag('T_IMAGE', Leenter\Tag::BLOCK, 'img', array(Leenter\Tag::BLOCK)));
        $this->parser->registerTag(new Leenter\Tag('T_TABLE', Leenter\Tag::BLOCK, 'table', array(Leenter\Tag::BLOCK)));
        $this->parser->registerTag(new Leenter\Tag('T_TABLE_ROW', Leenter\Tag::BLOCK, 'tr', array(Leenter\Tag::BLOCK)));
        $this->parser->registerTag(new Leenter\Tag('T_TABLE_CELL', Leenter\Tag::BLOCK, 'td', array('T_TABLE_ROW')));
        $this->parser->registerTag(new Leenter\Tag('T_QUOTE', Leenter\Tag::INLINE, 'quote', array(Leenter\Tag::BLOCK, Leenter\Tag::INLINE)));
        $this->parser->registerTag(new Leenter\Tag('T_COLUMNBREAK', Leenter\Tag::SINGLE, 'cb', array('T_DOCUMENT')));

        /*
         * lexer
         */
        $this->lexer->registerPattern('\[\/[^\] ]+\]', 'T_END_TAG');
        $this->lexer->registerPattern(' ', 'T_SPACE');
        $this->lexer->registerPattern('\n', 'T_EOL');
        $this->lexer->registerPattern('[^\[\n]+','T_TEXT');

    }

    public function parse($string)
    {
        $iId = substr($string, 0, 3);
        if ($iId != '[X]' && $iId != '[S]' && $iId != '[7]' && $iId != '[8]') return false;
        $parsedString = $this->parser->parse($string);
        if ($this->data['type'] != Item::GENERAL) {
            if (strpos('[b]konec:[/b]', $string) === FALSE) { $this->data['finishType'] = 'konec'; } else { $this->data['finishType'] = 'návrat'; }
            return new EventItem($parsedString, $this->data);
        } else {
            return new GeneralItem($parsedString, $this->data);
        }
    }

    /*** STARTUP METHODS ***/

    /**
     * Determine item type.
     * @param $string string Item string
     */
    public function startupDetermineType($string)
    {
        if (strpos($string, '[b]sraz:[/b]') === FALSE) {
            $this->data['type'] = Item::GENERAL;
        } else {
            $this->data['type'] = Item::EVENT;
        }
    }

    /*** IMPLICIT PROCESSING METHODS ***/

    public function processParagraph($content)
    {
        if (!empty($content)) {
            return '\par{' . trim($content) . '}';
        } else {
            return '';
        }
    }

    /*** CUSTOM PROCESSING METHODS ***/

    public function processT_TITLE($string)
    {
        $string = trim($string);
        if (!empty($string)) {
        $this->data['title'] = $string;
        if ($this->data['type'] == Item::GENERAL) {
            return '\nadpis{' . $string . '}';
        } else {
            return '\akce{' . $string . '}';
        }
        }
    }

    public function processT_UNIT_ID($content)
    {
        switch ($content) {
            case 'X':
                $this->data['unit'] = Item::INTRO;
                break;
            case 'S':
                $this->data['unit'] = Item::BOTH;
                break;
            case '7':
                $this->data['unit'] = Item::POUTNICI;
                break;
            case '8':
                $this->data['unit'] = Item::LESNISKRITCI;
                break;
        }

        return '';
    }

    public function processT_BOLD($string)
    {
        return '\textbf{' . trim($string) . '}';
    }

    public function processT_ITALIC($string)
    {
        return '\textit{' . trim($string) . '}';
    }

    public function processT_UNDERLINE($string)
    {
        return '\ulem{' . trim($string) . '}';
    }

    public function processT_QUOTE($string)
    {
        return '\czuv{' . trim($string) . '}';
    }

    public function processT_LIST($string)
    {
        return "\n" . '\begin{odrazky}' . "\n" . $string . "\n" . '\end{odrazky}';
    }

    public function processT_BPLACE($string)
    {
        $this->data['beginningPlace'] = $string;

        return $string;
    }

    public function processT_EPLACE($string)
    {
        $this->data['endPlace'] = $string;

        return $string;
    }

    public function processT_ITEM($string)
    {
        return '\item ' . trim($string);
    }

    public function processT_TABLE_CELL($string)
    {
        if (preg_match('/^((\d*)([Xcrl]?)([i]?)) (.*)$/', $string, $matches) === 1) {
            $matches[5] = trim($matches[5]);
            if (!empty($matches[4])) {
                $matches[5] = '\cellcolor{black}\color{white} ' . trim($matches[5]);
            }
            if (!empty($matches[2])) {
                return '\multicolumn{' . $matches[2] . '}{|' . (!empty($matches[3]) ? $matches[3] : 'c' ) . '|}{' . $matches[5] . '}' . ' & ';
            } elseif (!empty($matches[3])) {
                return '\multicolumn{1}{|' . $matches[3] . '|}{' . $matches[5] . '}' . ' & ';
            } else {
                return $matches[5] . ' & ';
            }
        } else {
            return $string . ' & ';
        }
    }

    public function processT_TABLE_ROW($string)
    {
        $string = preg_replace("/(& \\\\multicolumn\{\d+\}\{)\|/i", '$1', $string);

        return substr($string,0,-3) . "\\\\\\hline";
    }

    private function getAlignmentPreamble(&$string)
    {
        preg_match('/^(.*?)$/m', $string, $matches); // get first line
        $firstRow = '|' . str_replace(array(' ','&'), array('', '|'), substr($matches[0], 0, -8)) . '|';
        if (preg_match('/^(\|[Xclr])+\|$/', $firstRow) === 1) {
            $string = str_replace($matches[0], '', $string);

            return $firstRow;
        } else {
            return $this->determineAlignmentPreamble($firstRow);
        }
    }

    private function determineAlignmentPreamble($firstRow)
    {
        $columnCount = preg_match_all('/(?<!\{)\|(?!\})/', $firstRow, $matches) - 1;
        preg_match_all('/multicolumn\{(\d*)\}/', $firstRow, $matchesMultiColumn);
        foreach ($matchesMultiColumn[1] as $multiColumnCount) {
            $columnCount += $multiColumnCount-1;
        }
        $columnsParam = '|';
        for($i = 0; $i<$columnCount; $i++) $columnsParam .= "X|";

        return $columnsParam;
    }

    public function processT_TABLE($string)
    {
        return "\n".'\table{' . $this->getAlignmentPreamble($string) . '}{' . trim($string) . '}' . "\n";
    }

    public function processT_IMAGE($string)
    {
        $image = explode("|", $string);
        $scale = 1;
        if (count($image) == 2) {
            $scale = $image[1] / 100;
            if ($scale > 1) $scale = 1;
        }

        return "\\image{{$image[0]}}{{$scale}}";
    }

    public function processT_DATETIME($string)
    {
        preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{1,2}):(\d{2})/', $string, $matches);
        $time = strptime(sprintf('%02d', $matches[1]) . '/' . sprintf('%02d', $matches[2]) . '/' . $matches[3] . ' ' . sprintf('%02d', $matches[4]) . ':' . $matches[5], '%d/%m/%Y %H:%M');
        switch ($time['tm_wday']) {
            case '1': $converted = 'v pondělí'; break;
            case '2': $converted = 'v úterý'; break;
            case '3': $converted = 've středu'; break;
            case '4': $converted = 've čtvrtek'; break;
            case '5': $converted = 'v pátek'; break;
            case '6': $converted = 'v sobotu'; break;
            case '0': $converted = 'v neděli'; break;
        }
        $converted .= ' ' . $time['tm_mday'] . '. ' . ($time['tm_mon'] + 1) . '. v ' . $time['tm_hour'] . ':' . sprintf('%02d', $time['tm_min']);

        return $converted;
    }

    public function processT_PRIORITY($string)
    {
        $this->data['priority'] = $string;

        return '';
    }

    public function processT_BTDATETIME($string)
    {
        preg_match('/BT (\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{1,2}):(\d{2})/', $string, $matches);
        $time = strptime(sprintf('%02d', $matches[1]) . '/' . sprintf('%02d', $matches[2]) . '/' . $matches[3] . ' ' . sprintf('%02d', $matches[4]) . ':' . $matches[5], '%d/%m/%Y %H:%M');
        $this->data['beginningTime'] = $time;

        return $this->processT_DATETIME(substr($string,2));
    }

    public function processT_ETDATETIME($string)
    {
        preg_match('/ET (\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{1,2}):(\d{2})/', $string, $matches);
        $time = strptime(sprintf('%02d', $matches[1]) . '/' . sprintf('%02d', $matches[2]) . '/' . $matches[3] . ' ' . sprintf('%02d', $matches[4]) . ':' . $matches[5], '%d/%m/%Y %H:%M');
        $this->data['endingTime'] = $time;
        if ($this->data['beginningTime']['tm_mday'] == $this->data['endingTime']['tm_mday'] && $this->data['beginningTime']['tm_mon'] == $this->data['endingTime']['tm_mon'] && $this->data['beginningTime']['tm_year'] == $this->data['endingTime']['tm_year']) {
            return 'v ' . $time['tm_hour'] . ':' . sprintf('%02d', $time['tm_min']);
        } else {
            return $this->processT_DATETIME(substr($string,2));
        }
    }

}
