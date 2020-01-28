<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\File\Word as WordDok;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Personer\Person;
use UKMNorge\Rapporter\Framework\Word\ConfigAware;
use \PhpOffice\PhpWord\Element\Table;


class FormatterPersoner extends ConfigAware
{

    public static function render(WordDok $word, Innslag $innslag)
    {
        if (!static::show('deltakere') || !$innslag->getType()->erGruppe()) {
            return;
        }

        $word->avsnittSkift();

        // Personer (og kanskje kontaktperson skal vises)
        if (static::show('deltakere')) {
            // Ingen personer påmeldt
            if ($innslag->getPersoner()->getAntall() == 0) {
                $word->tekstFare('OBS: Ingen personer fra "' . $innslag->getNavn() . '" er påmeldt dette arrangementet');
            }
            // Gruppe-innslag
            elseif ($innslag->getType()->erGruppe()) {
                $word->tekstMuted('PERSONER');
                #if (static::getConfig()->get('deltakere_visning') == 'tabell') {
                static::tabell($word, $innslag);
                #} else {
                static::liste($word, $innslag);
                #}
            }
            // Enkeltperson-innslag
            else {
                static::listePerson($word, $innslag, $innslag->getPersoner()->getSingle());
            }
        }
        // Personer skal ikke vises, men kontaktpersonen skal
        elseif (static::show('kontaktperson')) {
            $word->tekstMuted('KONTAKTPERSON');
            static::listePerson($word, $innslag, $innslag->getKontaktperson());
        }
    }

    public static function tabell(WordDok $word, Innslag $innslag)
    {
        $table = $word->tabell();
        if (static::show('kontaktperson')) {
            static::rad($word, $table, $innslag, $innslag->getKontaktperson(), true);
        }
        foreach ($innslag->getPersoner()->getAll() as $person) {
            static::rad($word, $table, $innslag, $person);
        }
    }

    public static function rad(WordDok $word, Table $table, Innslag $innslag, Person $person, Bool $erKontakt = false)
    {
        $tekst = $erKontakt ? 'tekstFet' : 'tekst';
        $selector = $erKontakt ? 'kontakt' : 'deltakere';

        // Beregner bredder
        $width_navn = $word::pcToTwips(70);
        $width_mobil = $word::mmToTwips(25);
        $width_alder = $word::mmToTwips(18);
        $width_rolle = $word::pcToTwips(20);

        if (static::show('deltakere_mobil') || (static::show('kontaktperson') && static::show('kontakt_mobil'))) {
            $width_navn -= $width_mobil;
        }
        if (static::show('deltakere_alder') || (static::show('kontaktperson') && static::show('kontakt_alder'))) {
            $width_navn -= $width_alder;
        }
        if (static::show('deltakere_rolle') || static::show('kontaktperson')) {
            $width_navn -= $width_rolle;
        }

        // Legger til informasjon
        $row = $table->addRow( $word::getParagraphHeight()*1.5 );

        $navnCelle = $row->addCell($width_navn);
        $word->$tekst(
            $person->getNavn(),
            $navnCelle
        );

        if (static::show($selector . '_epost')) {
            $word->tekstLiten(
                $person->getEpost(),
                $navnCelle
            );
        }

        if (static::show($selector . '_mobil')) {
            $word->tekst(
                strval($person->getMobil()),
                $row->addCell(
                    $width_mobil
                )
            );
        }

        if (static::show('deltakere_alder') || (static::show('kontaktperson') && static::show('kontakt_alder'))) {
            $alderCelle = $row->addCell($width_alder);
            if (static::show($selector . '_alder')) {
                $word->tekst(
                    $person->getAlder(),
                    $alderCelle
                );
            }
        }

        if (static::show('deltakere_rolle') || static::show('kontaktperson')) {
            $word->tekst(
                $erKontakt ? 'Kontaktperson' : $person->getRolle(),
                $row->addCell($width_rolle)
            );
        }
    }

    public static function liste(WordDok $word, Innslag $innslag)
    { 
        $tekst = '';
        foreach( $innslag->getPersoner()->getAll() as $person ) {
            $tekst .= static::listePerson($word, $innslag, $person) .', ';
        }
        if( static::show('kontaktperson') ) {
            $tekst .= static::listePerson($word, $innslag, $innslag->getKontaktperson(), true);
        }

        $tekst = rtrim( $tekst, ', ');

        $word->tekst(
            $tekst
        );
    }

    public static function listePerson(WordDok $word, Innslag $innslag, Person $person, Bool $erKontakt = false)
    { 
        $selector = $erKontakt ? 'kontakt' : 'deltakere';

        $tekst = $person->getNavn() .
            (
                static::show($selector.'_mobil') ? 
                    ' '. $person->getMobil() : ''
            ).
            (
                static::show($selector.'_epost') && !empty($person->getEpost()) ?
                    ' - ' . $person->getEpost() : ''
            ).
            (
                static::show($selector.'_alder') && !$erKontakt ?
                    ' '. $person->getAlder() : ''
            ).
            (
                $erKontakt ?
                    ' (kontaktperson)' : ''
            ).
            (
                static::show('deltakere_rolle') && !$erKontakt ?
                    ' - '.  $person->getRolle() : ''
            );
        return $tekst;
    }
}
