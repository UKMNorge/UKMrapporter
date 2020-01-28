<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\Gruppe;

interface WordFormatterInterface {

    public function __construct( Config $config );
    public function gruppe( WordDok $word, Gruppe $gruppe );
    #public function innslag()
}