<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;

class FormatterTitler extends ConfigAware {
    public static function render(WordDok $word, Innslag $innslag)
    {
        if (!static::$config->show('titler') || !$innslag->getType()->harTitler()) {
            return;
        }

        $word->tekstMuted('TITLER');
        if ($innslag->getTitler()->getAntall() == 0) {
            $word->tekstFare('OBS: ' . $innslag->getNavn() . ' har ingen titler påmeldt dette arrangementet');
        }
        #elseif( static::$config->get('deltakere_visning') == 'tabell' ) {
        static::innslagTitlerTabell($word, $innslag);
        #} else {
        static::innslagTitlerKompakt($word, $innslag);
        #}
    }

    public static function innslagTitlerTabell(WordDok $word, Innslag $innslag)
    {
        $table = $word->tabell();

        // Beregn bredder
        $width_tittel = $word::pcToTwips(70);
        $width_detaljer = $word::pcToTwips(30);
        $width_varighet = $word::pcToTwips(12);
        if (static::$config->show('tittel_detaljer')) {
            $width_tittel -= $width_detaljer;
        }
        if (static::$config->show('tittel_varighet')) {
            $width_tittel -= $width_varighet;
        }

        // List ut titler
        foreach ($innslag->getTitler()->getAll() as $tittel) {
            $row = $table->addRow();

            $word->tekst(
                ucfirst($tittel->getTittel()),
                $row->addCell($width_tittel)
            );

            if (static::$config->show('tittel_detaljer')) {
                $word->tekst(
                    $tittel->getParentes(),
                    $row->addCell($width_detaljer)
                );
            }

            if(static::$config->show('tittel_varighet')){
                $word->tekst(
                    ( $innslag->getType()->harTitler() ? $tittel->getVarighet()->getHumanShort() : '' ),
                    $row->addCell($width_varighet)
                );
            }
        }
    }

    public static function innslagTitlerKompakt(WordDok $word, Innslag $innslag ) {
        $count = 0;
        foreach( $innslag->getTitler()->getAll() as $tittel ) {
            $count++;
            $word->tekst(
                ucfirst($tittel->getTittel()) .
                (
                    static::$config->show('tittel_varighet') && $innslag->getType()->harTid() ? 
                        $tittel->getVarighet()->getHumanShort : ''
                ).
                (
                    static::$config->show('tittel_detaljer') && !empty( $tittel->getParentes() ) ?
                        ' - ' . $tittel->getParentes() : ''
                ).
                ( 
                    $count < $innslag->getTitler()->getAll() ? ', ' : ''
                )
            );
        }
    }

}