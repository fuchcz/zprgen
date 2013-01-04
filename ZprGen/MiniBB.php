<?php

namespace ZprGen;

class MiniBB implements IBulletinBoard
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Called, when post is inserted to save .tex file.
     * @param $text string content of the file
     * @param $filename string filename
     * @return boolean
     */
    public function saveLatexFile($text, $filename)
    {
        $file = pathinfo($filename);
        if (!is_dir($file['dirname'])) {
            if (!mkdir($file['dirname'])) {
                return false;
            }
        }
        if (!file_put_contents($filename, $text)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Generate .pdf file from .tex.
     * @param $filename string filename
     * @return boolean
     */
    public function generatePdf($filename)
    {
        $file = pathinfo($filename);
        chdir($file['dirname']);
        exec($this->options['exec_vlna'] . ' ' . $file['basename']);
        exec($this->options['exec_pdflatex'] . ' ' . $file['basename']);
        if (file_exists($file['filename'] . '.pdf')) {
            if (!is_dir($file['dirname'] . "/old")) mkdir($file['dirname'] . "/old");
            rename($file['dirname'] . "/" . $file['filename'] . '.tex', $file['dirname'] . "/old/" . $file['filename'] . '.tex');
            unlink($file['filename'] . '.log');
            unlink($file['filename'] . '.aux');
            unlink($file['filename'] . '.te~');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all items from specified forum.
     * @param $topicId int id of the forum
     * @return string[]
     */
    public function getAllItems($topicId)
    {
        $sql = sprintf($this->options['sql_getAllItems'], $topicId);
        $result = mysql_query($sql);
        $items = array();
        while ($row = mysql_fetch_assoc($result)) {
            $items[] = $row['post_text'];
        }

        return $items;
    }

}
