<?php

namespace UKMNorge\Rapporter\Framework\Word;

use \PhpOffice\PhpWord\Element\Table;
use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Gruppe;

class Formatter extends ConfigAware implements FormatterInterface
{

    static $wordFormatterTitler;
    static $wordFormatterPersoner;

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
            static::gruppeOverskrift($word, $gruppe->getOverskrift(), $indent);
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
     * Legg til en gruppe-overskrift
     *
     * @param WordDok $word
     * @param String $overskrift
     * @param Int $indent
     * @return void
     */
    public function gruppeOverskrift(WordDok $word, String $overskrift, Int $indent)
    {
        $word->overskrift($overskrift, $indent);
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
        if (method_exists(static::class, 'preNavn')) {
            $navn = static::preNavn($innslag, $loop_index);
        } else {
            $navn = $innslag->getNavn();
        }
        // Opprett tabell
        $table = $word->tabell();
        static::innslagBasisInfo($word, $table, $innslag, $navn);
        static::innslagBeskrivelse($word, $table, $innslag);
        static::getWordFormatterTitler()::render($word, $innslag);
    }

    /**
     * Hent formateringsklasse for titler
     *
     * @return FormatterTitler
     */
    public static function getWordFormatterTitler() {
        if( is_null( static::$wordFormatterTitler ) ) {
            static::$wordFormatterTitler = new FormatterTitler( static::getConfig() );
        }
        return static::$wordFormatterTitler;
    }

    /**
     * Hent formateringsklasse for personer
     *
     * @return FormatterPersoner
     */
    public static function getWordFormatterPersoner() {
        if( is_null(static::$wordFormatterPersoner)){
            static::$wordFormatterPersoner = new FormatterPersoner( static::getConfig());
        }
        return static::$wordFormatterPersoner;
    }

    /**
     * Legg til innslagets beskrivelse
     *
     * @param WordDok $word
     * @param Table $table
     * @param Innslag $innslag
     * @return void
     */
    public static function innslagBeskrivelse(WordDok $word, Table $table, Innslag $innslag)
    {
        if (!static::getConfig()->show('beskrivelse')) {
            return;
        }

        if ($innslag->getType()->harBeskrivelse()) {
            $row = $table->addRow();

            if ($innslag->getType()->erEnkeltPerson() && $innslag->getType()->harFunksjoner()) {
                $word->tekst(
                    $innslag->getPersoner()->getSingle()->getRolle(),
                    $row->addCell(
                        $table->findFirstDefinedCellWidths()[0]
                    )
                );
                $word->tekst(
                    $innslag->getBeskrivelse(),
                    $row->addCell(
                        ($word::pcToTwips(100) - $table->findFirstDefinedCellWidths()[0]),
                        [
                            'gridSpan' => $table->countColumns() - 1
                        ]
                    )
                );
            } else {
                $word->tekst(
                    $innslag->getBeskrivelse(),
                    $row->addCell(
                        $word::pcToTwips(100),
                        [
                            'gridSpan' => $table->countColumns()
                        ]
                    )
                );
            }
        } elseif ($innslag->getType()->erEnkeltPerson() && $innslag->getType()->harFunksjoner()) {
            $word->tekst(
                $innslag->getPersoner()->getSingle()->getRolle(),
                $row->addCell(
                    $word::pcToTwips(100),
                    [
                        'gridSpan' => $table->countColumns()
                    ]
                )
            );
        } else {
            $word->tekst(
                'TEST',
                $row->addCell(
                    $word::pcToTwips(100),
                    [
                        'gridSpan' => $table->countColumns()
                    ]
                )
            );
        }
    }

    /**
     * Basisinfo om innslaget (rad 1)
     *
     * @param WordDok $word
     * @param [type] $table
     * @param Innslag $innslag
     * @param String $innslag_navn
     * @return void
     */
    public static function innslagBasisInfo(WordDok $word, Table $table, Innslag $innslag, String $innslag_navn)
    {
        // Beregn bredder
        $width_navn = 100;
        $width_kategori_og_sjanger = 14;
        $width_fylke_or_kommune = 20;
        $width_varighet = 12;
        if (static::getConfig()->show('kategori_og_sjanger')) {
            $width_navn -= $width_kategori_og_sjanger;
        }
        if (static::getConfig()->show('fylke') || static::getConfig()->show('kommune')) {
            $width_navn -= $width_fylke_or_kommune;
        }
        if (static::getConfig()->show('varighet')) {
            $width_navn -= $width_varighet;
        }


        // Rad 1: basic infos
        $row = $table->addRow(WordDok::ptToTwips(0));

        $word->h3(
            $innslag_navn,
            $row->addCell(WordDok::pcToTwips($width_navn))
        );

        if (static::getConfig()->show('kategori_og_sjanger')) {
            $word->tekst(
                $innslag->getType()->getNavn() . (!empty($innslag->getSjanger()) ? ' - ' . $innslag->getSjanger() : ''),
                $row->addCell(
                    WordDok::pcToTwips($width_kategori_og_sjanger)
                )
            );
        }

        if (static::getConfig()->show('fylke') || static::getConfig()->show('kommune')) {
            $word->tekst(
                (static::getConfig()->show('fylke') ? $innslag->getFylke()->getNavn() : '') . (static::getConfig()->show('fylke') && static::getConfig()->show('kommune') ? ' - ' : '') . (static::getConfig()->show('kommune') ?  $innslag->getKommune()->getNavn() : ''),
                $row->addCell(
                    WordDok::pcToTwips($width_fylke_or_kommune)
                )
            );
        }

        if (static::getConfig()->show('varighet')) {
            if ($innslag->getType()->harTid()) {
                $varighet = $innslag->getVarighet()->getHumanShort();
            } elseif (
                $innslag->getType()->erEnkeltPerson() &&
                static::getConfig()->show('kontaktperson') &&
                static::getConfig()->show('kontakt_alder')
            ) {
                $varighet = $innslag->getPersoner()->getSingle()->getAlder();
            } else {
                $varighet = '';
            }
            $word->tekst(
                $varighet,
                $row->addCell(
                    WordDok::pcToTwips($width_varighet)
                )
            );
        }
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
