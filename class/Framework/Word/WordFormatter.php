<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Gruppe;

class WordFormatter implements WordFormatterInterface
{

    /**
     * Rendre innholdet i en gruppe (rekursiv funksjon)
     *
     * @param WordDok $word
     * @param Gruppe $gruppe
     * @param Int $indent
     * @return void
     */
    public function gruppe(WordDok $word, Gruppe $gruppe, Int $indent = 0)
    {
        if ($gruppe->visOverskrift()) {
            $word->overskrift(
                $gruppe->getOverskrift(),
                $indent
            );
        }

        if ($gruppe->harGrupper()) {
            foreach ($gruppe->getGrupper() as $undergruppe) {
                static::gruppe($word, $undergruppe, $indent + 1);
            }
        } elseif ($gruppe->harInnslag()) {
            $loop_index = 0;
            foreach ($gruppe->getInnslag() as $innslag) {
                $loop_index++;
                static::innslag($word, $innslag, $loop_index);
            }
        } else {
            static::ingen($word, $gruppe->getNavn());
        }
    }

    /**
     * Rendre informasjonen om et innslag
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function innslag(WordDok $word, Innslag $innslag, Int $loop_index = null)
    {
        if( method_exists(static::class, 'preNavn' ) ) {
            $navn = static::preNavn( $innslag, $loop_index );
        } else {
            $navn = $innslag->getNavn();
        }
        $word->h3($navn);
    }

    /**
     * Rendre informasjon om at det ikke er innslag i en gruppe
     *
     * @param WordDok $word
     * @param String $gruppe_navn
     * @return void
     */
    public static function ingen(WordDok $word, String $gruppe_navn)
    {
        $word->tekst('Ingen innslag i ' . $gruppe_navn);
    }
}
