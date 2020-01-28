<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;

class FormatterTitler extends ConfigAware
{
    /**
     * Lag en liste med innslagets titler
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function render(WordDok $word, Innslag $innslag)
    {
        if (!static::show('titler') || !$innslag->getType()->harTitler()) {
            return;
        }

        $word->avsnittSkift();
        $word->tekstMuted('TITLER');
        if ($innslag->getTitler()->getAntall() == 0) {
            $word->tekstFare('OBS: ' . $innslag->getNavn() . ' har ingen titler påmeldt dette arrangementet');
        } elseif (static::$config->get('deltakere_visning') == 'tabell') {
            static::tabell($word, $innslag);
        } else {
            static::liste($word, $innslag);
        }
    }

    /**
     * Lag en tabell med titler
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function tabell(WordDok $word, Innslag $innslag)
    {
        $table = $word->tabell();

        // Beregn bredder
        $width_tittel = $word::pcToTwips(70);
        $width_detaljer = $word::pcToTwips(30);
        $width_varighet = $word::pcToTwips(12);
        if (static::show('tittel_detaljer')) {
            $width_tittel -= $width_detaljer;
        }
        if (static::show('tittel_varighet')) {
            $width_tittel -= $width_varighet;
        }

        // List ut titler
        foreach ($innslag->getTitler()->getAll() as $tittel) {
            $row = $table->addRow( $word::getParagraphHeight()*1.5 );

            $word->tekst(
                ucfirst($tittel->getTittel()),
                $row->addCell($width_tittel)
            );

            if (static::show('tittel_detaljer')) {
                $word->tekst(
                    $tittel->getParentes(),
                    $row->addCell($width_detaljer)
                );
            }

            if (static::show('tittel_varighet')) {
                $word->tekst(
                    ($innslag->getType()->harTitler() ? $tittel->getVarighet()->getHumanShort() : ''),
                    $row->addCell($width_varighet)
                );
            }
        }
    }

    /**
     * Lag en kompakt liste med titler
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function liste(WordDok $word, Innslag $innslag)
    {
        $count = 0;
        foreach ($innslag->getTitler()->getAll() as $tittel) {
            $count++;
            $word->tekst(
                ucfirst($tittel->getTittel()) . (static::show('tittel_varighet') && $innslag->getType()->harTid() ?
                        $tittel->getVarighet()->getHumanShort : '') . (static::show('tittel_detaljer') && !empty($tittel->getParentes()) ?
                        ' - ' . $tittel->getParentes() : '') . ($count < $innslag->getTitler()->getAll() ? ', ' : '')
            );
        }
    }
}
