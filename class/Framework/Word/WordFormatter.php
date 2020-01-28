<?php

namespace UKMNorge\Rapporter\Framework\Word;

use PhpOffice\PhpWord\Element\Table;
use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Config;

class WordFormatter implements WordFormatterInterface
{

    static $config;

    /**
     * Opprett en wordFormatter
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        static::$config = $config;
    }

    /**
     * Hent config for rapport-rendring
     *
     * @return Config
     */
    public static function getConfig()
    {
        return static::$config;
    }

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
        static::innslagTitler($word, $innslag);
    }

    public static function innslagTitler(WordDok $word, Innslag $innslag)
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
