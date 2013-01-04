<?php

namespace ZprGen;

interface IBulletinBoard
{
    /**
     * Called, when post is inserted to save .tex file.
     * @param $text string content of the file
     * @param $filename string filename
     * @return boolean
     */
    public function saveLatexFile($text, $filename);

    /**
     * Generate .pdf file from .tex.
     * @param $filename string filename
     * @return boolean
     */
    public function generatePdf($filename);

    /**
     * Get all items from specified forum.
     * @param $topicId int id of the forum
     * @return string[]
     */
    public function getAllItems($topicId);

}
