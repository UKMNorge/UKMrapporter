<?php
require_once('UKM/fylker.class.php');
require_once('UKM/monstringer.class.php');

if( isset( $_GET['type'] ) ) {
	$TWIG['type'] = $_GET['type'];
	$TWIG['fylker'] 	= fylker::getAll();
	$TWIG['festivalen'] = monstringer_v2::land( get_option('season') );
	$TWIG['nominerte'] 	= [];
	
	
	switch( $_GET['type'] ) {
		case 'media': 
			$valgt_type = 5;
			$valgt_type_navn = 'UKM Media';
			break;
		case 'arrangor':
			$valgt_type = 8;
			$valgt_type_navn = 'Arrangører';
			break;
		case 'konferansier':
			$valgt_type = 4;
			$valgt_type_navn = 'Konferansierer';
			break;
		default:
			throw new Exception('Beklager, vi støtter ikke denne typen innslag');
	}
	
	foreach( $TWIG['fylker'] as $fylke ) {
		try {
			$monstring = monstringer_v2::fylke( $fylke->getId(), get_option('season') );
		} catch( Exception $e ) {
			continue;
		}
		
		foreach( $monstring->getInnslag()->getAll() as $innslag ) {
			#echo '<br />'. $innslag->getNavn() .' - '. $innslag->getType()->getNavn();
			// Vis kun valgt type
			if( $innslag->getType()->getId() != $valgt_type ) {
				#echo ' CONTINUE PGA TYPE ('. $innslag->getType()->getId() .')';
				continue;
			}
	
			// Vis kun de som har enten voksen eller ungdomsskjema
			if( !$innslag->getNominasjon( $TWIG['festivalen'] )->harDeltakerskjema() && !$innslag->getNominasjon( $TWIG['festivalen'] )->harVoksenskjema() ) {
				#echo ' CONTINUE PGA NOMINASJON (mangler begge skjema)';
				continue;
			}
			
			$TWIG['nominerte'][ $fylke->getId() ][] = $innslag;
		}
	}
	
	require_once( PLUGIN_DIR .'class.rapport.php');
	/**
	 * INIT WORD
	**/
	$rapport = new rapport('Nominerte '. $valgt_type_navn, 'personer');
	$rapport->setSeason( get_option('season') );
	$section = $rapport->word_init();
	
	global $PHPWord;
	$styleTable = array('borderSize'=>6, 'borderColor'=>'dd9437', 'cellMargin'=>80);
	$PHPWord->addTableStyle('bordered', $styleTable, $styleTable);

	/**
	 * GENERATE WORD
	**/
	foreach( $TWIG['fylker'] as $fylke ) {
		woText($section, '', 'rapportIkonSpacer' );
		woText($section, '', 'rapportIkonSpacer' );
		woText($section, 'Nominerte '. strtolower($valgt_type_navn) .' fra ', 'place');
		woText($section, $fylke->getNavn(), 'rapport');
		$section->addPageBreak();
	
		if( is_array( $TWIG['nominerte'][ $fylke->getId() ] ) ) {
			foreach( $TWIG['nominerte'][ $fylke->getId() ] as $innslag ) {
				$person = $innslag->getPersoner()->getSingle();
				$nominasjon = $innslag->getNominasjon( $TWIG['festivalen'] );
				
				$tab = $section->addTable();
				
				// NAVN OG STED
				$tab->addRow();
				$c = $tab->addCell(8000);
				
				if( !$nominasjon->erNominert() ) {
					woText($c, 'IKKE NOMINERT: '. $innslag->getNavn(),'h1');
				} else {
					woText($c, $innslag->getNavn(),'h1');
				}
				if( $valgt_type == 8 ) {
					woText($c, $innslag->getFylke()->getNavn(), 'bold');
				}
				
				$c = $tab->addCell(3500);
				if( $valgt_type == 8 ) {
					if( $nominasjon->harDeltakerskjema() ) {
						if( $nominasjon->getLydtekniker() ) {
							woText($c, 'LYDTEKNIKER', 'h2');
						}
						if( $nominasjon->getLystekniker() ) {
							woText($c, 'LYSTEKNIKER', 'h2');
						}
						if( $nominasjon->getVertskap() ) {
							woText($c, 'VERTSKAP', 'h2');
						}
					} else {
						woText($c, 'IKKE VALGT', 'h2');
					}
				} else {
					woText($c, $innslag->getFylke()->getNavn(),'h1');
				}

				// PERSONDATA
				$tab->addRow();
				$c = $tab->addCell(8000);
				woText($c, $person->getAlder(),'bold');
				$c = $tab->addCell(3500);
				woText($c, $person->getMobil(),'bold');
				
				
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
					woText($section, $message, 'h3');
					woText($section, 'Deltakeren har gått tilbake i skjemaet og sagt at '. $person->getKjonnspronomen().
						' kan være med likevel, så alt er teoretisk sett i orden, men det er verdt å dobbeltsjekke.' );
				}

				
				/*******************************************************/
				/* MEDIA DELTAKERSKJEMA
				/*******************************************************/
				if( $valgt_type == 5 && $nominasjon->harDeltakerskjema() ) {
					woText($section, '', 'h2');
					woText($section, 'Deltakerskjema', 'h2');

					woText($section, '');
					woText($section, 'Ønsker å jobbe med', 'bold');
					
					woText($section, '1: '. $nominasjon->getPri1() );
					woText($section, '2: '. $nominasjon->getPri2() );
					woText($section, '3: '. $nominasjon->getPri3() );
					woText($section, 'Annet: '. $nominasjon->getAnnet() );
					
					woText($section, '');
					woText($section, 'Beskrivelse', 'bold');
					woText($section, $nominasjon->getBeskrivelse());
				}
				/*******************************************************/
				/* MEDIA UTEN DELTAKERSKJEMA
				/*******************************************************/
				elseif( $valgt_type == 5 ) {
					woText($section, 'Har ikke levert deltakerskjema', 'alert');
				}
	
	
				/**
				 *
				 * VOKSENSKJEMA
				 *
				 *
				**/
				woText($section, '', 'h2');
				woText($section, 'Voksenskjema', 'h2');
				if( $nominasjon->harVoksenskjema() ) {
					$tab = $section->addTable();
					$tab->addRow();
					$c = $tab->addCell(8000);
					woText($c, 'Fylt ut av: '. $nominasjon->getVoksen()->getNavn(), 'bold' );
					$c = $tab->addCell(3500);
					woText($c, $nominasjon->getVoksen()->getMobil(), 'bold' );
					
					/*******************************************************/
					/* MEDIA VOKSENSKJEMA
					/*******************************************************/
					if( $valgt_type == 5 ) {
						woText($section,'');
						woText($section, 'Slik samarbeider '. $person->getFornavn() .' med andre:', 'bold');
						woText($section, $nominasjon->getSamarbeid());
					
						woText($section,'');
						woText($section, ''. $person->getFornavn() .' sin erfaring og kunnskap:', 'bold');
						woText($section, $nominasjon->getErfaring());
					}
					/*******************************************************/
					/* KONFERANSIER VOKSENSKJEMA
					/*******************************************************/
					elseif( $valgt_type == 4 ) {
						woText($section,'');
						woText($section, ''. $person->getFornavn() .' nomineres fordi:', 'bold');
						woText($section, $nominasjon->getHvorfor());
					
						woText($section,'');
						woText($section, ''. $person->getFornavn() .' sin erfaring og spesielle egenskaper som konferansier:', 'bold');
						woText($section, $nominasjon->getBeskrivelse());
						
						
						woText($section,'');
						woText($section, 'I playbackmodulen er følgende filer tilknyttet '. $person->getFornavn(), 'bold');
						
						if( $innslag->getPlayback()->getAntall() > 0 ) {
							$tab = $section->addTable();
							foreach( $innslag->getPlayback()->getAll() as $playback ) {
								$tab->addRow();
								$c = $tab->addCell(3000);
								woText($c, $playback->name, 'bold');	
								$c = $tab->addCell(8500);
								woText($c, $playback->download());
							}
						} else {
							woText($section, $person->getFornavn() .' har ingen playbackfiler');
						}

						if( !empty( $nominasjon->getFilUrl() ) ) {
							woText($section, '');
							woText($section, $nominasjon->getVoksen()->getNavn() .' har lastet opp denne referanse-filen', 'bold');
							woText($section, $nominasjon->getFilUrl());
						} else {
							woText($section, '');
							woText($section, $nominasjon->getVoksen()->getNavn() .' har ikke lastet opp ytterligere referanse-filer');
						}
					}
					/*******************************************************/
					/* MEDIA VOKSENSKJEMA
					/*******************************************************/
					elseif( $valgt_type == 8 ) {
						woText($section,'');
						woText($section, ''. $person->getFornavn() .' sine positive egenskaper i samarbeid med andre:', 'bold');
						woText($section, $nominasjon->getVoksenSamarbeid());
					
						woText($section,'');
						woText($section, ''. $person->getFornavn() .' sin erfaring og kunnskap:', 'bold');
						woText($section, $nominasjon->getVoksenErfaring());

						woText($section,'');
						woText(
							$section, 
							'Andre kommentarer eller annet '. 
								$nominasjon->getVoksen()->getNavn() .
								' tror er viktig å få frem om '. 
								$person->getNavn() .
								' sin erfaring og kunnskap:', 
							'bold');
						woText($section, $nominasjon->getVoksenAnnet());

					}
				} else {
					woText($section, 'Har ikke levert voksenskjema', 'alert');
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
					woText($section, '', 'h2');
					woText($section, 'Deltakerskjema', 'h2');
					$tab = $section->addTable();
					data( $tab, 'Samarbeid med andre', $nominasjon->getSamarbeid());
					data( $tab, 'Om egen erfaring', $nominasjon->getErfaring());
					data( $tab, 'Suksesskriterie for UA', $nominasjon->getSuksesskriterie());
					data( $tab, 'Eventuelle kommentarer	', $nominasjon->getAnnet());
					
					/**
					 * LYDTEKNIKK
					**/
					if( $nominasjon->getLydtekniker() ) {
						woText($section, '', 'bold');
						woText($section, 'LYDTEKNIKER', 'bold');
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
						woText($section, '', 'bold');
						woText($section, 'LYSTEKNIKER', 'bold');
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
					woText($section,'');
					woText($section, 'Har ikke levert deltakerskjema', 'h2');
				}				
				$section->addPageBreak();
			}
		}
	}

	
	/**
	 * RENDER WORD
	**/
	$TWIG['link'] = $rapport->woWrite();
} else {
	$TWIG['type'] = 'velg';
}

function data( $tab, $tittel, $svar ) {
	$tab->addRow();
	$c = $tab->addCell(3500, ['border' => '6']);
	woText($c, $tittel, 'bold');
	$c = $tab->addCell(8000, ['border' => '6']);
	woText($c, $svar);
}