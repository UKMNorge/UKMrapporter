<?php

namespace UKMNorge\Rapporter\Framework\Word;

use \PhpOffice\PhpWord\Element\Table;
use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Media\Bilder\Bilde;
use UKMNorge\Innslag\Personer\Person;
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
        } elseif ($gruppe->harPersoner()) {
            $loop_index = 0;
            foreach ($gruppe->getPersoner() as $person) {
                $loop_index++;
                static::person($word, $person, $loop_index);
            }
        } else {
            static::ingen($word, $gruppe->getOverskrift());
        }

        if ($indent < 2) {
            $word->sideskift();
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
        static::innslagTekniskeBehov($word, $innslag);
        static::innslagMediefiler($word, $innslag);
        static::innslagBilder($word, $innslag);
        static::innslagFilmer($word, $innslag);
        static::getWordFormatterTitler()::render($word, $innslag);
        static::getWordFormatterPersoner()::render($word, $innslag);
        static::innslagLinjeskiftEtter($word);
    }

    /**
     * Rendre informasjonen om et innslag
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function person(WordDok $word, Person $person, Int $loop_index = null)
    {
        // Opprett tabell
        $table = $word->tabell();
        static::personBasisInfo($word, $table, $person);
        $word->linjeSkift();
    }

    /**
     * Sett inn linjeskift etter innslag
     *
     * @param WordDok $word
     * @return void
     */
    public static function innslagLinjeskiftEtter(WordDok $word)
    {
        $word->linjeSkift();
        $word->linjeSkift();
        $word->linjeSkift();
    }

    /**
     * List ut tekniske behov
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function innslagTekniskeBehov(WordDok $word, Innslag $innslag)
    {
        if (!static::show('tekniske_behov') || !$innslag->getType()->harTekniskeBehov()) {
            return;
        }

        if (!empty($innslag->getTekniskeBehov())) {
            $word->linjeSkift();
            $word->tekstMuted('TEKNISKE BEHOV:');
            $word->tekst($innslag->getTekniskeBehov());
        }
    }

    /**
     * List ut mediefiler for innslaget
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function innslagMediefiler(WordDok $word, Innslag $innslag)
    {
        if (!static::show('mediefiler')) {
            return;
        }

        $word->linjeSkift();
        $word->tekstMuted('MEDIEFILER:');
        if ($innslag->getPlayback()->getAntall() > 0) {
            foreach ($innslag->getPlayback()->getAll() as $fil) {
                $tekstRun = $word->tekstRun();
                $word->tekstFet($fil->getNavn(), $tekstRun);
                if (!empty($fil->getBeskrivelse())) {
                    $word->tekst(' - ' . $fil->getBeskrivelse(), $tekstRun);
                }
                $word->link($fil->getUrl(), ' Last ned: ' . $fil->getFil());
            }
        } else {
            $word->tekst(
                $innslag->getNavn() .
                    ' har ikke opplastede mediefiler for bruk i gjennomføringen'
            );
        }
    }

    /**
     * List ut alle bilder for innslaget
     *
     * @param WordDok $word
     * @param Innslag $innslag
     * @return void
     */
    public static function innslagBilder(WordDok $word, Innslag $innslag)
    {
        if (!static::show('bilder')) {
            return;
        }
        $word->tekstMuted('BILDER:');
        $tabell = $word->tabell();
        $printed = [];
        # UTSTILLING-INNSLAG
        if ($innslag->getType()->getKey() == 'utstilling') {
            foreach ($innslag->getTitler()->getAll() as $tittel) {
                ## BILDE(R) AV KUNSTVERK
                if ($innslag->getBilder()->harValgt($tittel->getId())) {
                    $bilde = $innslag->getBilder()->getValgt($tittel->getId());
                    $printed[] = $bilde->getId();
                    static::innslagBilde($word, $tabell, $bilde, 'Valgt bilde for ' . $tittel->getId());
                }

                # BILDE AV KUNSTNER
                if ($innslag->getBilder()->harValgt(0)) {
                    $bilde = $innslag->getBilder()->getValgt(0);
                    $printed[] = $bilde->getId();
                    static::innslagBilde($word, $tabell, $bilde, 'Bilde av kunstneren');
                }
            }
        }
        # ANDRE INNSLAG
        elseif ($innslag->getBilder()->harValgt(0)) {
            $bilde = $innslag->getBilder()->getValgt(0);
            $printed[] = $bilde->getId();
            static::innslagBilde($word, $tabell, $bilde, 'Valgt bilde for innslaget');
        }


        # ANDRE BILDER AV INNSLAGET
        foreach ($innslag->getBilder()->getAll() as $bilde) {
            if (in_array($bilde->getId(), $printed)) {
                continue;
            }
            static::innslagBilde($word, $tabell, $bilde);
        }
    }

    public static function innslagFilmer(WordDok $word, Innslag $innslag ) {
        if (!static::show('ukmtv') || $innslag->getFilmer()->getAntall() == 0) {
            return;
        }
        $word->tekstMuted('FILMER I UKM-TV:');
        $tabell = $word->tabell();
        foreach ($innslag->getFilmer()->getAll() as $film) {
            $tabell->addRow();
            $celle = $tabell->addCell(WordDok::mmToTwips(80));
            $word->tekst($film->getNavn(), $celle);
            $celle = $tabell->addCell(WordDok::mmToTwips(210 - 80));
            $word->link(
                $film->getTvUrl(),
                $film->getTvUrl(),
                $celle
            );
        }
    }

    public static function innslagBilde(WordDok $word, Table $table, Bilde $bilde, String $tekst = null)
    {
        $table->addRow();
        $celle = $table->addCell(WordDok::mmToTwips(80));
        $word->bilde(
            $bilde->getSize('medium')->getPath(),
            ['height' => 80], // points
            $celle
        );
        if (!is_null($tekst)) {
            $word->tekst($tekst, $celle);
        }

        $celle = $table->addCell(WordDok::mmToTwips(210 - 80));
        $word->link(
            $bilde->getSize('original')->getUrl(),
            $bilde->getSize('original')->getUrl(),
            $celle
        );
    }

    /**
     * Hent formateringsklasse for titler
     *
     * @return FormatterTitler
     */
    public static function getWordFormatterTitler()
    {
        if (is_null(static::$wordFormatterTitler)) {
            static::$wordFormatterTitler = new FormatterTitler(static::getConfig());
        }
        return static::$wordFormatterTitler;
    }

    /**
     * Hent formateringsklasse for personer
     *
     * @return FormatterPersoner
     */
    public static function getWordFormatterPersoner()
    {
        if (is_null(static::$wordFormatterPersoner)) {
            static::$wordFormatterPersoner = new FormatterPersoner(static::getConfig());
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
        if (!static::show('beskrivelse')) {
            return;
        }

        if ($innslag->getType()->harBeskrivelse()) {
            if ($innslag->getType()->erEnkeltPerson() && $innslag->getType()->harFunksjoner()) {
                $row = $table->addRow();

                $word->tekst(
                    $innslag->getPersoner()->getSingle()->getRolle(),
                    $word->celle(
                        $table->findFirstDefinedCellWidths()[0],
                        $row
                    )
                );
                $word->tekst(
                    $innslag->getBeskrivelse(),
                    $word->celle(
                        ($word::pcToTwips(100) - $table->findFirstDefinedCellWidths()[0]),
                        $row,
                        [
                            'gridSpan' => $table->countColumns() - 1
                        ]
                    )
                );
            } else {
                if (empty($innslag->getBeskrivelse())) {
                    return;
                }
                $row = $table->addRow();
                $word->tekst(
                    $innslag->getBeskrivelse(),
                    $word->celle(
                        $word::pcToTwips(100),
                        $row,
                        [
                            'gridSpan' => $table->countColumns()
                        ]
                    )
                );
            }
        } elseif ($innslag->getType()->erEnkeltPerson() && $innslag->getType()->harFunksjoner()) {
            $row = $table->addRow();

            $word->tekst(
                $innslag->getPersoner()->getSingle()->getRolle(),
                $word->celle(
                    $word::pcToTwips(100),
                    $row,
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
        if (static::show('kategori_og_sjanger')) {
            $width_navn -= $width_kategori_og_sjanger;
        }
        if (static::show('fylke') || static::show('kommune')) {
            $width_navn -= $width_fylke_or_kommune;
        }
        if (static::show('varighet')) {
            $width_navn -= $width_varighet;
        }


        // Rad 1: basic infos
        $row = $table->addRow(WordDok::ptToTwips(0));

        $word->h3(
            $innslag_navn,
            $word->celle(
                WordDok::pcToTwips($width_navn),
                $row
            )
            /*
             * En vakker dag kommer denne også kanskje ☀️
            ,
            [
                'spaceBefore' => $word::getH3Height() * 0.5
            ]
            **/
        );

        if (static::show('kategori_og_sjanger')) {
            $word->tekst(
                $innslag->getType()->getNavn() . (!empty($innslag->getSjanger()) ? ' - ' . $innslag->getSjanger() : ''),
                $word->celle(
                    WordDok::pcToTwips($width_kategori_og_sjanger),
                    $row
                )
            );
        }

        if (static::show('fylke') || static::show('kommune')) {
            $word->tekst(
                (static::show('fylke') ? $innslag->getFylke()->getNavn() : '') . (static::show('fylke') && static::show('kommune') ? ' - ' : '') . (static::show('kommune') ?  $innslag->getKommune()->getNavn() : ''),
                $word->celle(
                    WordDok::pcToTwips($width_fylke_or_kommune),
                    $row
                )
            );
        }

        if (static::show('varighet')) {
            if ($innslag->getType()->harTid()) {
                $varighet = $innslag->getVarighet()->getHumanShort();
            } elseif (
                $innslag->getType()->erEnkeltPerson() &&
                static::show('kontaktperson') &&
                static::show('kontakt_alder')
            ) {
                $varighet = $innslag->getPersoner()->getSingle()->getAlder();
            } else {
                $varighet = '';
            }
            $word->tekst(
                $varighet,
                $word->celle(
                    WordDok::pcToTwips($width_varighet),
                    $row
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



    /**
     * Basisinfo om en gitt person (hvis vi bruker personer, ikke innslag) (rad 1)
     *
     * @param WordDok $word
     * @param Table $table
     * @param Person $person
     * @return void
     */
    public static function personBasisInfo(WordDok $word, Table $table, Person $person)
    {
        // Beregn bredder
        $width_navn = 100;

        // Rad 1: basic infos
        $row = $table->addRow(WordDok::ptToTwips(0));

        $word->h3(
            $person->getNavn(),
            $word->celle(
                WordDok::pcToTwips($width_navn),
                $row
            )
        );
    }
}
