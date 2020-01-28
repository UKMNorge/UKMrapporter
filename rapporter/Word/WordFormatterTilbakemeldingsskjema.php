<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Word\WordFormatter;


class WordFormatterTilbakemeldingsskjema extends WordFormatter {
    /**
     * Prefix innslagets navn med rekkefølgen
     *
     * @param Innslag $innslag
     * @param Int $loop_index
     * @return String concat $loop_index. ~ $innslag->getNavn()
     */
    public static function preNavn( Innslag $innslag, Int $loop_index ) {
        return $loop_index .'. '. $innslag->getNavn();
    }
}