<?php

namespace UKMNorge\Rapporter\Word;

use UKMNorge\File\Word;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Personer\Person;
use UKMNorge\Rapporter\Framework\Word\Formatter;
use UKMNorge\Innslag\Nominasjon\Nominasjon;
use UKMNorge\Innslag\Nominasjon\Arrangor;
use UKMNorge\Innslag\Nominasjon\Konferansier;
use UKMNorge\Innslag\Nominasjon\Media;
use Exception;


class FormatterNominasjoner extends Formatter
{
    /**
     * Legg til en nominasjon
     * 
     * @param Word $word
     * @param Nominasjon|Arrangor|Konferansier|Media $nominasjon
     * @param string $type
     * @param Int|null $loop_index 
     */
    public static function nominasjon(Word $word, Nominasjon $nominasjon, string $type, Int $loop_index = null)
    {
        if( !in_array($type, ['Media','Arrangør','Konferansier', 'Datakultur'])) {
            throw new Exception('Beklager, vi støtter ikke denne typen innslag ' . $type);
        }

        $innslag = Innslag::getById($nominasjon->getInnslagId());
        $person = $innslag->getPersoner()->getSingle();

        $table = $word->tabell();
        
        // NAVN OG STED
        $table->addRow();
        $celle = $table->addCell(8000, ['valign'=>'bottom']);
        
        if( !$nominasjon->erNominert() ) {
            $word->h3(
                'IKKE NOMINERT: '. $innslag->getNavn(),
                $celle
            );
        } else {
            $word->h3(
                $innslag->getNavn(),
                $celle
            );
        }
        
        $celle = $table->addCell(3500, ['valign'=>'bottom', 'gridSpan' => 2]);
        if( $type == 'Arrangør' ) {
            if( $nominasjon->harDeltakerskjema() ) {
                if( $nominasjon->getLydtekniker() ) {
                    $word->h3('LYDTEKNIKER', $celle);
                }
                if( $nominasjon->getLystekniker() ) {
                    $word->h3('LYSTEKNIKER', $celle);
                }
                if( $nominasjon->getVertskap() ) {
                    $word->h3('VERTSKAP', $celle);
                }
                if( $nominasjon->getProdusent() ) {
                    $word->h3('PRODUSENT', $celle);
                }
            } else {
                $word->tekstFare('IKKE VALGT KATEGORI', $celle);
            }
        } else {
            $word->h2($innslag->getFylke()->getNavn(), $celle);
        }
        
        // PERSONDATA
        $table->addRow();
        $word->tekst(
            $person->getMobil(),
            $table->addCell( Word::pcToTwips(30))
        );
        $word->tekst(
            $person->getAlder(),
            $table->addCell( Word::pcToTwips(20))
        );
        $word->tekst(
            $innslag->getFylke()->getNavn(),
            $table->addCell( Word::pcToTwips(60))
        );
        
        
        // ARRANGØRER
        if( $type == 'Arrangør' && !empty( $nominasjon->getSorry() )) {
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
            $word->tekstFare($message);
            $word->tekst('Deltakeren har gått tilbake i skjemaet og sagt at '. $person->getKjonnspronomen().
                ' kan være med likevel, så alt er teoretisk sett i orden, men det er verdt å dobbeltsjekke.' );
        }

        
        /*******************************************************/
        /* MEDIA DELTAKERSKJEMA
        /*******************************************************/
        if( $type == 'Media' && $nominasjon->harDeltakerskjema() ) {
            $word->h2('');
            $word->h2('Deltakerskjema');

            $word->tekst('');
            $word->tekstFet('Ønsker å jobbe med');
            
            $word->tekst('1: '. $nominasjon->getPri1());
            $word->tekst('2: '. $nominasjon->getPri2());
            $word->tekst('3: '. $nominasjon->getPri3());
            $word->tekst('Annet: '. $nominasjon->getAnnet());
            
            $word->tekst('');
            $word->tekstFet('Beskrivelse');
            $word->tekst($nominasjon->getBeskrivelse());
        }
        /*******************************************************/
        /* MEDIA UTEN DELTAKERSKJEMA
        /*******************************************************/
        elseif( $type == 'Media' ) {
            $word->h3('Har ikke levert deltakerskjema');
        }


        /**
         *
         * VOKSENSKJEMA
         *
         *
        **/
        $word->h2('Voksenskjema');
        if( $nominasjon->harVoksenskjema() ) {
            $table = $word->tabell();
            $table->addRow();
            $celle = $table->addCell(8000);
            $word->tekst('Fylt ut av: '. $nominasjon->getVoksen()->getNavn(), $celle );
            $celle = $table->addCell(3500);
            $word->tekst($nominasjon->getVoksen()->getMobil(), $celle );
            
            /*******************************************************/
            /* MEDIA VOKSENSKJEMA
            /*******************************************************/
            if( $type == 'Media' ) {
                $word->tekst('');
                $word->tekstFet('Slik samarbeider '. $person->getFornavn() .' med andre:');
                $word->tekst($nominasjon->getSamarbeid());
            
                $word->tekst('');
                $word->tekstFet(''. $person->getFornavn() .' sin erfaring og kunnskap:');
                $word->tekst($nominasjon->getErfaring());
            }
            /*******************************************************/
            /* KONFERANSIER VOKSENSKJEMA
            /*******************************************************/
            elseif( $type == 'Konferansier' ) {
                $word->tekst('');
                $word->tekstFet(''. $person->getFornavn() .' nomineres fordi:');
                $word->tekst($nominasjon->getHvorfor());
            
                $word->tekst('');
                $word->tekstFet(''. $person->getFornavn() .' sin erfaring og spesielle egenskaper som konferansier:');
                $word->tekst($nominasjon->getBeskrivelse());
                
                $word->tekst('');
                $word->tekstFet('I playbackmodulen er følgende filer tilknyttet '. $person->getFornavn());
                
                if( $innslag->getPlayback()->getAntall() > 0 ) {
                    $table = $word->tabell();
                    foreach( $innslag->getPlayback()->getAll() as $playback ) {
                        $table->addRow();
                        $word->tekst(
                            $playback->name, 
                            $table->addCell(3000)
                        );	
                        $word->tekst(
                            method_exists($playback, 'download') ? $playback->download() : '-',
                            $table->addCell(8500)
                        );
                    }
                } else {
                    $word->tekst($person->getFornavn() .' har ingen playbackfiler');
                }

                if( !empty( $nominasjon->getFilUrl() ) ) {
                    $word->tekst('');
                    $word->tekstFet($nominasjon->getVoksen()->getNavn() .' har lastet opp denne referanse-filen');
                    $word->link($nominasjon->getFilUrl(), $nominasjon->getFilUrl());
                } else {
                    $word->tekst('');
                    $word->tekstFare($nominasjon->getVoksen()->getNavn() .' har ikke lastet opp ytterligere referanse-filer');
                }
            }
            /*******************************************************/
            /* MEDIA VOKSENSKJEMA
            /*******************************************************/
            elseif( $type == 'Arrangør' ) {
                $word->tekst('');
                $word->tekstFet(''. $person->getFornavn() .' sine positive egenskaper i samarbeid med andre:');
                $word->tekst($nominasjon->getVoksenSamarbeid());
            
                $word->tekst('');
                $word->tekstFet(''. $person->getFornavn() .' sin erfaring og kunnskap:');
                $word->tekst($nominasjon->getVoksenErfaring());

                $word->tekst('');
                $word->tekstFet(
                    'Andre kommentarer eller annet '. 
                    $nominasjon->getVoksen()->getNavn() .
                    ' tror er viktig å få frem om '. 
                    $person->getNavn() .
                    ' sin erfaring og kunnskap:'
                );
                $word->tekst($nominasjon->getVoksenAnnet());

            }
        } else {
            $word->tekstFet('Har ikke levert voksenskjema');
        }
        
        
        /**
         *
         * DELTAKERSKJEMA ARRANGØRER
         *
        **/
        /*******************************************************/
        /* ARRANGØRER DELTAKERSKJEMA
        /*******************************************************/
        if( $type == 'Arrangør' && $nominasjon->harDeltakerskjema() ) {
            $word->h2('');
            $word->h2('Deltakerskjema');
            $table = $word->tabell();
            static::tableData( 
                $word, 
                $table,
                'Samarbeid med andre', 
                $nominasjon->getSamarbeid()
            );
            static::tableData( 
                $word,
                $table,
                'Om egen erfaring',
                $nominasjon->getErfaring()
            );
            static::tableData( 
                $word, 
                $table,
                'Suksesskriterie for UA',
                $nominasjon->getSuksesskriterie()
            );
            static::tableData(
                $word,
                $table,
                'Eventuelle kommentarer	', 
                $nominasjon->getAnnet()
            );
            
            /**
             * LYDTEKNIKK
            **/
            if( $nominasjon->getLydtekniker() ) {
                $word->tekst('');
                $word->tekst('LYDTEKNIKER');
                $table = $word->tabell('bordered');
                
                static::tableData( 
                    $word,
                    $table,
                    'Tidligere erfaring? Brukt hvilke mikser(e)? (kjenner du den godt, eller mindre godt?)',
                    $nominasjon->getLydErfaring1()
                );
                static::tableData( 
                    $word,
                    $table,
                    'Jobbet med lydteknikk utenom UKM? Hvilke? Rolle?',
                    $nominasjon->getLydErfaring2()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Hvordan fungerer stagerack? Hvordan koble det sammen med digitalmikser?',
                    $nominasjon->getLydErfaring3()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Forskjell på dynamisk og kondesator-mikrofon? Når bruke hvilken?',
                    $nominasjon->getLydErfaring4()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Ville du, på kort tid, full lydsjekk band. Monitor + PA + konsert for mange?',
                    $nominasjon->getLydErfaring5()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Eventuelle referanser',
                    $nominasjon->getLydErfaring6()
                );
            }
            
            /**
             * LYSTEKNIKK
            **/
            if( $nominasjon->getLystekniker() ) {
                $word->tekst('');
                $word->tekst('LYSTEKNIKER');
                $table = $word->tabell('bordered');
                
                static::tableData( 
                    $word, 
                    $table,
                    'Tidligere erfaring? Brukt hvilke mikser(e)? (kjenner du den godt, eller mindre godt?)',
                    $nominasjon->getLysErfaring1()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Jobbet med lysteknikk utenom UKM? Hvilke? Rolle?',
                    $nominasjon->getLysErfaring2()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Kan du patche opp til to universer med dmx-adresser på en mikser, på lampene og om nødvendig feilsøke lampe/fixture?',
                    $nominasjon->getLysErfaring3()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Forskjell på beam, wash og profil? Når bruke hvilken?',
                    $nominasjon->getLysErfaring4()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Takler du stress? Klarer du programmere lysshow + gjennomføre live for mange?',
                    $nominasjon->getLysErfaring5()
                );
                static::tableData( 
                    $word, 
                    $table,
                    'Eventuelle referanser',
                    $nominasjon->getLysErfaring6()
                );
            }

        }
        /*******************************************************/
        /* ARRANGØRER UTEN DELTAKERSKJEMA
        /*******************************************************/
        elseif( $type == 'Arrangør' ) {
            $word->tekst('');
            $word->h3('Har ikke levert deltakerskjema');
        }				
        $word->sideskift();
    }

    public static function tableData( Word $word, $table, $tittel, $svar ) {
        $table->addRow();
        $cell = $table->addCell(3500, ['border' => '6']);
        $word->tekst($tittel, $cell, null, ['bold' => true]);
        $cell = $table->addCell(8000, ['border' => '6']);
        $word->tekst( $svar, $cell);
    }
}