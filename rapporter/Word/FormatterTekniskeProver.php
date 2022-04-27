<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\File\Word;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Word\Formatter;


class FormatterTekniskeProver extends Formatter {
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
        
        if( static::show('notatfelt') ) {
            static::leggTilKommentarbokser( $word );
        }
        // Oppmøtetid
        if(static::show('oppmotetid')) {
            static::leggTilOppmotetid($word, $innslag);
        }

        $word->sideskift();
    }

    public static function leggTilOppmotetid(Word $word, Innslag $innslag) {
        $width = $word::pcToTwips(100);
        if( static::getConfig()->get('notatfelt_visning') == 'liten' ) {
            $height = $word::mmToTwips(28);
        } else {
            $height = $word::mmToTwips(48);
        }
        
        $tabell = $word->tabell();

        foreach($innslag->getProgram()->getAll() as $hendelse) {
            if($hendelse->getId() == $innslag->getContext()->getForestilling()->getId()) {
                // Oppmøtetid rad
                $row = $tabell->addRow($height);
                $word->tekstMuted(
                    'Oppmøtetid: ' . $hendelse->getOppmoteTid($innslag)->format('j. M Y \k\\l. H:i'),
                    $row->addCell($width)
                );
            }
        }
    }

    public static function leggTilKommentarbokser( Word $word ) {
        
        $width = $word::pcToTwips(100);
        if( static::getConfig()->get('notatfelt_visning') == 'liten' ) {
            $height = $word::mmToTwips(28);
        } else {
            $height = $word::mmToTwips(48);
        }
        $tabell = $word->tabell();

        // RAD 1
        if( static::show('notatfelt_lydteknisk')) {
            $row = $tabell->addRow($height);
            $word->tekstMuted(
                'LYDTEKNISK',
                $row->addCell($width)
            );
        }
        
        // RAD 2
        if( static::show('notatfelt_lysteknisk') ) {
            $row = $tabell->addRow($height);
            $word->tekstMuted(
                'LYSTEKNISK',
                $row->addCell($width)
            );
        }

        // RAD 3
        if( static::show('notatfelt_generell') ) {
            $row = $tabell->addRow($height);
            $word->tekstMuted(
                'GENERELLE NOTATER',
                $row->addCell($width)
            );
        }

    }
}