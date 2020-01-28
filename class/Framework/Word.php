<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Rapporter\Framework\Word\Formatter;

class Word {
    var $grupper;

    public function __construct( String $filnavn, Gruppe $grupper, Config $config, Formatter $wordFormatter=null ) 
    {
        $this->config = $config;
        $this->grupper = $grupper;
        $this->word = new WordDok( $filnavn );
        $this->wordFormatter = $wordFormatter;
    }


    public function render() {
        $this->wordFormatter::gruppe( $this->word, $this->grupper );
    }

    public function writeToFile() {
        $this->render();
        return $this->word->writeToFile();
    }
}