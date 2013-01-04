<?php

class ZprGenParserTest extends PHPUnit_Framework_TestCase {

    private $parser;

    public function __construct() {
        require_once(__DIR__ . '/../tools/autoload.php');
        spl_autoload_register(function ($class) {
            $parts = explode('\\', $class);
            if (file_exists(__DIR__ . '/../ZprGen/' . end($parts) . '.php')) require_once __DIR__ . '/../ZprGen/' . end($parts) . '.php';
        });
        $this->parser = new ZprGen\ZprGenParser();
    }

    private function runTests($assertions) {
        foreach($assertions as $assertion) {
            $parsed = $this->parser->parse($assertion['source']);
            $this->assertEquals($assertion['final'], (string)$parsed);
        }
    }

    public function testParagraphs() {
        $tests[] = array(
            'source'    =>  "[X]\nThis is paragraph.",
            'final'     =>  '\par{This is paragraph.}');
        $tests[] = array(
            'source'    =>  "[X]\nThis is first line.\nThis is second.",
            'final'     =>  "\\par{This is first line.}\n\\par{This is second.}");
        $tests[] = array(
            'source'    =>  "[X]\nPlain text gets passed through unchanged.  b is not a tag and i is not a tag and neither is /i and neither is (b).",
            'final'     =>  '\par{Plain text gets passed through unchanged.  b is not a tag and i is not a tag and neither is /i and neither is (b).}');
        $this->runTests($tests);
    }
    public function testUnknownAndBrokenTags() {
        $tests[] = array(
            'source' => "[X]\nThis is [broken a tag.",
            'final' => '\par{This is [broken a tag.}');
        $tests[] = array(
            'source' => "[X]\nThis is [/broken a tag.",
            'final' => '\par{This is [/broken a tag.}');
        $tests[] = array(
            'source' => "[X]\nThis is [] a tag.",
            'final' => "\\par{This is [] a tag.}");
        $tests[] = array(
            'source' => "[X]\nThis is [/  ] a tag.",
            'final' => "\\par{This is [/  ] a tag.}");
        $tests[] = array(
            'source' => "[X]\nThis is [/ a tag.",
            'final' => "\\par{This is [/ a tag.}");
        $tests[] = array(
            'source' => "[X]\nBroken [ tags before [b]real tags[/b] don't break the real tags.",
            'final' => "\\par{Broken [ tags before \\textbf{real tags} don't break the real tags.}");
        $tests[] = array(
            'source' => "[X]\nBroken [tags before [b]real tags[/b] don't break the real tags.",
            'final' => "\\par{Broken [tags before \\textbf{real tags} don't break the real tags.}");
        $tests[] = array(
            'source' => "[X]\nBroken [/tags before [b]real tags[/b] don't break the real tags.",
            'final' => "\\par{Broken [/tags before \\textbf{real tags} don't break the real tags.}");
        $this->runTests($tests);
    }
    public function testMisorderedTags() {
        $tests[] = array(
            'source' => "[X]\n[i][b]Mis-ordered nesting[/i][/b] gets fixed.",
            'final' => "\\par{\\textit{\\textbf{Mis-ordered nesting}} gets fixed.}");
        $tests[] = array(
            'source' => "[X]\n[b]Mismatched tags[/i] are not matched to each other.",
            'final' => "\\par{\\textbf{Mismatched tags[/i] are not matched to each other.}}");
        $this->runTests($tests);
    }
    public function testPartialTags() {
        $tests[] = array(
            'source' => "[X]\n[i]Unended blocks are automatically ended.",
            'final' => "\\par{\\textit{Unended blocks are automatically ended.}}");
        $tests[] = array(
            'source' => "[X]\nUnstarted blocks[/i] have their end tags ignored.",
            'final' => "\\par{Unstarted blocks[/i] have their end tags ignored.}");
        $this->runTests($tests);
    }
    public function testInlineAndBlockNesting() {
        $tests[] = array(
            'source' => "[X]\n* Inlines and [b]blocks get[/b] nested correctly.",
            'final' => "\\begin{odrazky}\n\\item Inlines and \\textbf{blocks get} nested correctly.\n\\end{odrazky}");
        $tests[] = array(
            'source' => "[X]\n[b]Inlines and\n* blocks get\nnested correctly[/b].",
//            'final' => "\\par{\\textbf{Inlines and}}\n\\begin{odrazky}\n\\item blocks get\n\\end{odrazky}\n\\par{nested correctly.}");
            'final' => "\\par{\\textbf{Inlines and\n[list][li] blocks get[/li][/list]\nnested correctly}.}");
        $this->runTests($tests);
    }
    public function testCaseSensitivity() {
        $tests[] = array(
            'source' => "[X]\n[b]This[/B] is a [I]test[/i].",
            'final' => "\\par{\\textbf{This} is a \\textit{test}.}");
        $this->runTests($tests);
    }
    public function testSpecialCharacters() {
        $tests[] = array(
            'source' => "[X]\n#, $, %, _, &, {, } and ^ are replaced with equivalents.",
            'final' => "\\par{\#, \$, \%, \_, \&, \{, \} and \^ are replaced with equivalents.}");
        $this->runTests($tests);
    }
    public function testWhitespace() {
        $tests[] = array(
            'source' => "[X]\nThis [b ]is a test[/b ].",
            'final' => "\\par{This [b ]is a test[/b ].}");
        $tests[] = array(
            'source' => "[X]\nThis [b]is a test[/b ].",
            'final' => "\\par{This \\textbf{is a test[/b ].}}");
        $this->runTests($tests);
    }
    public function testInlineTagConversion() {
        $tests[] = array(
            'source' => "[X]\nThis is a test of the [i]emergency broadcasting system[/i].",
            'final' => "\\par{This is a test of the \\textit{emergency broadcasting system}.}");
        $tests[] = array(
            'source' => "[X]\nThis is a test of the [b]emergency broadcasting system[/b].",
            'final' => "\\par{This is a test of the \\textbf{emergency broadcasting system}.}");
        $tests[] = array(
            'source' => "[X]\nThis is a test of the [u]emergency broadcasting system[/u].",
            'final' => "\\par{This is a test of the \\ulem{emergency broadcasting system}.}");
        $this->runTests($tests);
    }
    public function testImagesAndReplacedTagConversion() {
        $tests[] = array(
            'source' => "[X]\n[img]pokus.png[/img]",
            'final' => "\\image{pokus.png}{1}");
        $tests[] = array(
            'source' => "[X]\n[img]pokus.png|68[/img]",
            'final' => "\\image{pokus.png}{0.68}");
        $tests[] = array(
            'source' => "[X]\n[img]pokus.png|168[/img]",
            'final' => "\\image{pokus.png}{1}");
        $this->runTests($tests);
    }
    public function testListsAndListItems() {
        $tests[] = array(
            'source'    =>  "[X]\n* This is item.",
            'final'     =>  "\\begin{odrazky}\n\\item This is item.\n\\end{odrazky}");
        $tests[] = array(
            'source'    =>  "[X]\n* This is first item.\n* This is second item.",
            'final'     =>  "\\begin{odrazky}\n\\item This is first item.\n\\item This is second item.\n\\end{odrazky}");
        $this->runTests($tests);
    }
    public function testTables() {
        $tests[] = array(
            'source'    =>  "[X]\n| a | b | c |",
            'final'     =>  '\table{|X|X|X|}{a & b & c\\\\\\hline}');
        $tests[] = array(
            'source'    =>  "[X]\n| a | b | c |\n|3 s |\n| v | x | z |",
            'final'     =>  "\\table{|X|X|X|}{a & b & c\\\\\\hline\n\\multicolumn{3}{|c|}{s}\\\\\\hline\nv & x & z\\\\\\hline}");
        $tests[] = array(
            'source'    =>  "[X]\n| a | b | c |\n|2 s | u |\n| v | x | z |",
            'final'     =>  "\\table{|X|X|X|}{a & b & c\\\\\\hline\n\\multicolumn{2}{|c|}{s} & u\\\\\\hline\nv & x & z\\\\\\hline}");
        $tests[] = array(
            'source'    =>  "[X]\n|2 a b | c |",
            'final'     =>  '\table{|X|X|X|}{\\multicolumn{2}{|c|}{a b} & c\\\\\\hline}');
        $this->runTests($tests);
    }
    public function testUnallowedTags() {
        $tests[] = array(
            'source'    =>  "[X]\n* [BT 12/12/2012 09:00]",
            'final'     =>  "\\begin{odrazky}\n\\item ve středu 12. 12. v 9:00\n\\end{odrazky}");
        $tests[] = array(
            'source'    =>  "[X]\n[BT 12/12/2012 09:00]",
            'final'     =>  "\\par{[BT 12/12/2012 09:00]}");
        $this->runTests($tests);
    }

}
