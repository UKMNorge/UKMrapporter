<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\File\Word;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Word\Formatter;


class FormatterTilbakemeldingsskjema extends Formatter
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
        parent::gruppeOverskrift($word, $overskrift, $indent);
        $word->sideskift();
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
        static::visVideresending($word, $innslag);
        parent::innslag($word, $innslag, $loop_index);
        static::leggTilVurderingsskjema($word);
        $word->sideskift();
    }

    /**
     * Ikke sett inn linjeskift etter innslag
     *
     * @param Word $word
     * @return void
     */
    public static function innslagLinjeskiftEtter( Word $word ) {
    }

    public static function visVideresending(Word $word, Innslag $innslag)
    {
        if (!static::getConfig()->show('videresendes')) {
            return;
        }

        $tabell = $word->tabell();
        $row = $tabell->addRow();

        $span = explode('-', static::getConfig()->get('vis_deltakere_innenfor'));

        $start = $span[0];
        $stop = $span[1];

        $innenfor = $innslag->getPersoner()->getProsentInnenforAlder($start, $stop);
        $word->tekstLiten(
            $innenfor . '% av deltakerne er mellom ' . $start . ' og ' . $stop . ' år',
            $word->celle(
                $word::pcToTwips(50),
                $row,
                [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT
                ]
            )
        );

        if ($innenfor < 50) {
            $word->tekstFare(
                'Innslaget kan ikke videresendes',
                $word->celle(
                    $word::pcToTwips(50),
                    $row,
                    [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT
                    ]
                )
            );
        }
    }

    public static function leggTilVurderingsskjema(Word $word)
    {

        $half = $word::pcToTwips(50);
        $height = $word::mmToTwips(28);
        $word->h2('Tilbakemeldinger');
        $tabell = $word->tabell();

        // RAD 1
        $row = $tabell->addRow($height);
        $word->tekstFet(
            'Originalitet',
            $row->addCell($half)
        );
        $word->tekstFet(
            'Kreativitet',
            $row->addCell($half)
        );

        // RAD 2
        $row = $tabell->addRow($height);
        $word->tekstFet(
            'Publikumskontakt / formidling',
            $row->addCell($half)
        );
        $word->tekstFet(
            'Tekniske ferdigheter',
            $row->addCell($half)
        );

        // RAD 3
        $row = $tabell->addRow($height);
        $word->tekstFet(
            'Originalitet',
            $row->addCell($half)
        );
        $word->tekstFet(
            'Kreativitet',
            $row->addCell($half)
        );

        // RAD 4
        $row = $tabell->addRow($height);
        $word->tekstFet(
            'Scenefremtreden / fremføring',
            $row->addCell($half)
        );
        $word->tekstFet(
            'Kvalitet i forhold til forutsetninger',
            $row->addCell($half)
        );

        // RAD 5
        $row = $tabell->addRow();
        $word->tekstFet(
            'Basert på ovenstående vurderinger gjør jeg følgende midlertidige vurdering',
            $row->addCell(
                $half,
                [
                    'vGrid' => 2
                ]
            )
        );
    }
}
