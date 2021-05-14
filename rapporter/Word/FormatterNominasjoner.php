<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\File\Word;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Personer\Person;
use UKMNorge\Rapporter\Framework\Word\Formatter;
use UKMNorge\Innslag\Nominasjon\Nominasjon;
use Exception;


class FormatterNominasjoner extends Formatter
{
    public static function nominasjon(Word $word, Nominasjon $nominasjon, string $type, Int $loop_index = null)
    {

        switch( $type ) {
            case 'Media': 
                $valgt_type = 5;
                $valgt_type_navn = 'UKM Media';
                break;
            case 'Arrangør':
                $valgt_type = 8;
                $valgt_type_navn = 'Arrangører';
                break;
            case 'Konferansier':
                $valgt_type = 4;
                $valgt_type_navn = 'Konferansierer';
                break;
            default:
                throw new Exception('Beklager, vi støtter ikke denne typen innslag ' . $type);
        }


        $innslag = Innslag::getById($nominasjon->getInnslagId());
        $person = $innslag->getPersoner()->getSingle();
        

        $tab = $word->getSection()->addTable();
        $section = $word->getSection();        
        
        // NAVN OG STED
        $tab->addRow();
        $c = $tab->addCell(8000);
        
        if( !$nominasjon->erNominert() ) {
            $word->h1('IKKE NOMINERT: '. $innslag->getNavn(), $c);
        } else {
            $word->h1($innslag->getNavn(), $c);
        }
        if( $valgt_type == 8 ) {
            $word->tekst($innslag->getFylke()->getNavn(), $c);
        }
        
        $c = $tab->addCell(3500);
        if( $valgt_type == 8 ) {
            if( $nominasjon->harDeltakerskjema() ) {
                if( $nominasjon->getLydtekniker() ) {
                    $word->h2('LYDTEKNIKER', $c);
                }
                if( $nominasjon->getLystekniker() ) {
                    $word->h2('LYSTEKNIKER', $c);
                }
                if( $nominasjon->getVertskap() ) {
                    $word->h2('VERTSKAP', $c);
                }
                if( $nominasjon->getProdusent() ) {
                    $word->h2('PRODUSENT', $c);
                }
            } else {
                $word->h2('IKKE VALGT', $c);
            }
        } else {
            $word->h1($innslag->getFylke()->getNavn(), $c);
        }

        // PERSONDATA
        $tab->addRow();
        $c = $tab->addCell(8000);
        $word->tekst($person->getAlder(), $c);
        $c = $tab->addCell(3500);
        $word->tekst($person->getMobil(), $c);
        
        
        // ARRANGØRER
        if( $valgt_type == 8 && !empty( $nominasjon->getSorry() )) {
            $message = 'OBS: Deltakeren har på et tidspunkt sagt at '.
                $person->getKjonnspronomen() .' ikke kan være med på ';
            switch( $nominasjon->getSorry() ) {
                case 'begge':
                    $message .= 'planleggingshelg og UKM-festivalen.';
                    break;
                case 'planleggingshelg':
                    $message .= 'planleggingshelgen';
                    break;
                case 'festivalen':
                    $message .= 'UKM-festivalen';
                    break;
            }
            $word->tekst($message, $section, 'h3');
            $word->tekst('Deltakeren har gått tilbake i skjemaet og sagt at '. $person->getKjonnspronomen().
                ' kan være med likevel, så alt er teoretisk sett i orden, men det er verdt å dobbeltsjekke.', $section );
        }

        
        /*******************************************************/
        /* MEDIA DELTAKERSKJEMA
        /*******************************************************/
        if( $valgt_type == 5 && $nominasjon->harDeltakerskjema() ) {
            $word->h2('', $section);
            $word->h2('Deltakerskjema', $section);

            $word->tekst('', $section);
            $word->tekst('Ønsker å jobbe med', $section);
            
            $word->tekst('1: '. $nominasjon->getPri1(), $section);
            $word->tekst('2: '. $nominasjon->getPri2(), $section);
            $word->tekst('3: '. $nominasjon->getPri3(), $section);
            $word->tekst('Annet: '. $nominasjon->getAnnet(), $section);
            
            $word->tekst('', $section);
            $word->tekst('Beskrivelse', $section);
            $word->tekst($nominasjon->getBeskrivelse(), $section);
        }
        /*******************************************************/
        /* MEDIA UTEN DELTAKERSKJEMA
        /*******************************************************/
        elseif( $valgt_type == 5 ) {
            $word->tekst('Har ikke levert deltakerskjema', $section);
        }


        /**
         *
         * VOKSENSKJEMA
         *
         *
        **/
        $word->h2('', $section);
        $word->h2('Voksenskjema', $section);
        if( $nominasjon->harVoksenskjema() ) {
            $tab = $section->addTable();
            $tab->addRow();
            $c = $tab->addCell(8000);
            $word->tekst('Fylt ut av: '. $nominasjon->getVoksen()->getNavn(), $c );
            $c = $tab->addCell(3500);
            $word->tekst($nominasjon->getVoksen()->getMobil(), $c );
            
            /*******************************************************/
            /* MEDIA VOKSENSKJEMA
            /*******************************************************/
            if( $valgt_type == 5 ) {
                $word->tekst('', $section);
                $word->tekst('Slik samarbeider '. $person->getFornavn() .' med andre:', $section);
                $word->tekst($nominasjon->getSamarbeid(), $section);
            
                $word->tekst('', $section);
                $word->tekst(''. $person->getFornavn() .' sin erfaring og kunnskap:', $section);
                $word->tekst($nominasjon->getErfaring(), $section);
            }
            /*******************************************************/
            /* KONFERANSIER VOKSENSKJEMA
            /*******************************************************/
            elseif( $valgt_type == 4 ) {
                $word->tekst('', $section);
                $word->tekst(''. $person->getFornavn() .' nomineres fordi:', $section);
                $word->tekst($nominasjon->getHvorfor(), $section);
            
                $word->tekst('', $section);
                $word->tekst(''. $person->getFornavn() .' sin erfaring og spesielle egenskaper som konferansier:', $section);
                $word->tekst($nominasjon->getBeskrivelse(), $section);
                
                
                $word->tekst('', $section);
                $word->tekst('I playbackmodulen er følgende filer tilknyttet '. $person->getFornavn(), $section);
                
                if( $innslag->getPlayback()->getAntall() > 0 ) {
                    $tab = $section->addTable();
                    foreach( $innslag->getPlayback()->getAll() as $playback ) {
                        $tab->addRow();
                        $c = $tab->addCell(3000);
                        $word->tekst($playback->name, $c);	
                        $c = $tab->addCell(8500);
                        $word->tekst($playback->download(), $c);
                    }
                } else {
                    $word->tekst($person->getFornavn() .' har ingen playbackfiler', $section);
                }

                if( !empty( $nominasjon->getFilUrl() ) ) {
                    $word->tekst('', $section);
                    $word->tekst($nominasjon->getVoksen()->getNavn() .' har lastet opp denne referanse-filen', $section);
                    $word->tekst($nominasjon->getFilUrl(), $section);
                } else {
                    $word->tekst('', $section);
                    $word->tekst($nominasjon->getVoksen()->getNavn() .' har ikke lastet opp ytterligere referanse-filer', $section);
                }
            }
            /*******************************************************/
            /* MEDIA VOKSENSKJEMA
            /*******************************************************/
            elseif( $valgt_type == 8 ) {
                $word->tekst('', $section);
                $word->tekst(''. $person->getFornavn() .' sine positive egenskaper i samarbeid med andre:', $section);
                $word->tekst($nominasjon->getVoksenSamarbeid(), $section);
            
                $word->tekst('', $section);
                $word->tekst(''. $person->getFornavn() .' sin erfaring og kunnskap:', $section);
                $word->tekst($nominasjon->getVoksenErfaring(), $section);

                $word->tekst('', $section);
                $word->tekst(
                    'Andre kommentarer eller annet '. 
                        $nominasjon->getVoksen()->getNavn() .
                        ' tror er viktig å få frem om '. 
                        $person->getNavn() .
                        ' sin erfaring og kunnskap:',
                    $section
                );
                $word->tekst($nominasjon->getVoksenAnnet(), $section);

            }
        } else {
            $word->tekst('Har ikke levert voksenskjema', $section);
        }
        
        
        /**
         *
         * DELTAKERSKJEMA ARRANGØRER
         *
        **/
        /*******************************************************/
        /* ARRANGØRER DELTAKERSKJEMA
        /*******************************************************/
        if( $valgt_type == 8 && $nominasjon->harDeltakerskjema() ) {
            $word->h2('', $section);
            $word->h2('Deltakerskjema', $section);
            $tab = $section->addTable();
            data( $tab, 'Samarbeid med andre', $nominasjon->getSamarbeid());
            data( $tab, 'Om egen erfaring', $nominasjon->getErfaring());
            data( $tab, 'Suksesskriterie for UA', $nominasjon->getSuksesskriterie());
            data( $tab, 'Eventuelle kommentarer	', $nominasjon->getAnnet());
            
            /**
             * LYDTEKNIKK
            **/
            if( $nominasjon->getLydtekniker() ) {
                $word->tekst('', $section);
                $word->tekst('LYDTEKNIKER', $section);
                $tab = $section->addTable('bordered');
                
                data( $tab,
                    'Tidligere erfaring? Brukt hvilke mikser(e)? (kjenner du den godt, eller mindre godt?)',
                    $nominasjon->getLydErfaring1()
                );
                data( $tab,
                    'Jobbet med lydteknikk utenom UKM? Hvilke? Rolle?',
                    $nominasjon->getLydErfaring2()
                );
                data( $tab,
                    'Hvordan fungerer stagerack? Hvordan koble det sammen med digitalmikser?',
                    $nominasjon->getLydErfaring3()
                );
                data( $tab,
                    'Forskjell på dynamisk og kondesator-mikrofon? Når bruke hvilken?',
                    $nominasjon->getLydErfaring4()
                );
                data( $tab,
                    'Ville du, på kort tid, full lydsjekk band. Monitor + PA + konsert for mange?',
                    $nominasjon->getLydErfaring5()
                );
                data( $tab,
                    'Eventuelle referanser',
                    $nominasjon->getLydErfaring6()
                );
            }
            
            /**
             * LYSTEKNIKK
            **/
            if( $nominasjon->getLystekniker() ) {
                $word->tekst('', $section);
                $word->tekst('LYSTEKNIKER', $section);
                $tab = $section->addTable('bordered');
                
                data( $tab,
                    'Tidligere erfaring? Brukt hvilke mikser(e)? (kjenner du den godt, eller mindre godt?)',
                    $nominasjon->getLysErfaring1()
                );
                data( $tab,
                    'Jobbet med lysteknikk utenom UKM? Hvilke? Rolle?',
                    $nominasjon->getLysErfaring2()
                );
                data( $tab,
                    'Kan du patche opp til to universer med dmx-adresser på en mikser, på lampene og om nødvendig feilsøke lampe/fixture?',
                    $nominasjon->getLysErfaring3()
                );
                data( $tab,
                    'Forskjell på beam, wash og profil? Når bruke hvilken?',
                    $nominasjon->getLysErfaring4()
                );
                data( $tab,
                    'Takler du stress? Klarer du programmere lysshow + gjennomføre live for mange?',
                    $nominasjon->getLysErfaring5()
                );
                data( $tab,
                    'Eventuelle referanser',
                    $nominasjon->getLysErfaring6()
                );
            }

        }
        /*******************************************************/
        /* ARRANGØRER UTEN DELTAKERSKJEMA
        /*******************************************************/
        elseif( $valgt_type == 8 ) {
            $word->tekst('', $section);
            $word->h2('Har ikke levert deltakerskjema', $section);
        }				
        $section->addPageBreak();
    }
}