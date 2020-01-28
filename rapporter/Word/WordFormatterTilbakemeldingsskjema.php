<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\File\Word;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Word\WordFormatter;


class WordFormatterTilbakemeldingsskjema extends WordFormatter
{
    /**
     * Prefix innslagets navn med rekkefølgen
     *
     * @param Innslag $innslag
     * @param Int $loop_index
     * @return String concat $loop_index. ~ $innslag->getNavn()
     */
    public static function preNavn(Innslag $innslag, Int $loop_index)
    {
        return $loop_index . '. ' . $innslag->getNavn();
    }

    /**
     * Legg til en gruppe-overskrift
     *
     * @param Word $word
     * @param String $overskrift
     * @param Int $indent
     * @return void
     */
    public function gruppeOverskrift(Word $word, String $overskrift, Int $indent)
    {
        parent::gruppeOverskrift($word,$overskrift,$indent);
        $word->addPageBreak();
    }

    /**
     * Legg til info om et innslag
     *
     * @param Word $word
     * @param Innslag $innslag
     * @param Int $loop_index
     * @return void
     */
    public static function innslag(Word $word, Innslag $innslag, Int $loop_index = null)
    {
        parent::innslag($word, $innslag, $loop_index);
        $word->addPageBreak();
    }
}
