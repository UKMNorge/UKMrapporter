<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Rapporter\Framework\Gruppe;

interface WordFormatterInterface {
    public function gruppe( WordDok $word, Gruppe $gruppe );
}