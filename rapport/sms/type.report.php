<?php
require_once('UKM/monstring.class.php');
require_once('UKM/innslag_typer.class.php');

/**
 * renderData
 * Et person-objekt som genereres for alle personer som skal 
 * vises i rapporten. Sendes så til generate (HTML + Word + Excel)
 * for gjenbruk av kode
**/
class renderData {
	public function __construct( $innslag, $person, $type ) {
		$this->type = $innslag->getType()->getKey();
		$this->typeNavn = $innslag->getType()->getNavn();
		if( 'land' == $type ) {
			$this->kommune = $innslag->getFylke()->getNavn();
		} else {
			$this->kommune = $innslag->getKommune()->getNavn();
		}
		$this->innslag = $innslag->getNavn();
		$this->navn = $person->getNavn();
		$this->mobil = $person->getMobil();
		$this->epost = $person->getEpost();
		$this->id = $innslag->getId() .' - '. $person->getId();
	}
	public function getTypeNavn() {
		return $this->typeNavn;
	}
	public function getType() {
		return $this->type;
	}
	public function getKommune() {
		return $this->kommune;
	}
	public function getId() {
		return $this->id;
	}
	public function getInnslag() {
		return $this->innslag;
	}
	public function getNavn() {
		return $this->navn;
	}
	public function getMobil() {
		return $this->mobil;
	}
	public function getEpost() {
		return $this->epost;
	}
}

class valgt_rapport extends rapport {
	
	var $monstring = null;
	/**
	 * class constructor
	 * 
	 * Initierer parent og sier hvilke options som er mulig å velge i rapporten
	 *
	 * @access public
	 * @param rapport navn på rapport
	 * @param kategori navn på sorteringskategori for rapport
	 * @return class object
	 */
	public function __construct($rapport, $kategori){
		parent::__construct($rapport, $kategori);

		// Sett annet navn hvis det er en utvidet rapport (hent ut fra hendelser)
		if($this->report_extended == 'hendelse') {
			$this->navn = 'SMS-lister fra hendelser';
		}

		$g = $this->optGrp('geo','Deltakere fra');
		// UKM-FESTIVALEN BRUKER FYLKER
		if( 'land' == $this->getMonstring()->getType() ) {
			foreach( fylker::getAll() as $fylke ) {
				$this->opt($g, 'geo_'. $fylke->getId(), $fylke->getNavn() );
			}
		// FYLKE OG LOKALMØNSTRING BRUKER KOMMUNER
		} else {
			foreach( $this->getMonstring()->getKommuner() as $kommune ) {
				$this->opt($g, 'geo_'. $kommune->getId(), $kommune->getNavn() );
			}
		}
		
		// TYPE PERSONER
		$e = $this->optGrp('er','som er');
		$this->opt($e, 'er_kontaktperson', 'kontaktperson');
		$this->opt($e, 'er_deltaker', 'deltaker');

		// HENT ALLE HENDELSER
		if($this->report_extended == 'hendelse') {
			$t = $this->optGrp('type', 'er med i');
			foreach( $this->getMonstring()->getProgram()->getAllInkludertSkjulte() as $hendelse ) {
				$this->opt($t, 'hendelse_'.$hendelse->getId(), $hendelse->getNavn());
			}
		// HENT ALLE TYPER INNSLAG
		} else {
			$t = $this->optGrp('type', 'av typen');
			foreach( $this->getMonstring()->getInnslagTyper() as $innslagType ) {
				if( 1 == $innslagType->getId() ) {
					foreach( innslag_typer::getAllScene() as $innslagTypeScene ) {
						$this->opt( $t, 'type_'. $innslagTypeScene->getKey(), 'Scene: '. $innslagTypeScene->getNavn() );
					}
				} else {
					$this->opt( $t, 'type_'. $innslagType->getKey(), $innslagType->getNavn() );
				}
			}
		}
		$this->_postConstruct();
	}
	
	/**
	 * generate function
	 * 
	 * Genererer selve rapporten i HTML-visning
	 *
	 * @access public
	 * @return void
	 */
	public function generate() {
		$TWIG['personer'] = $this->_getRenderData();
		echo TWIG('sms.html.twig', $TWIG, dirname( dirname( dirname(__FILE__) ) ), true);
	}

	/**
	 * generateWord function
	 * 
	 * Genererer et word-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */	
	public function generateWord(){
		global $PHPWord;		
		$section = $this->word_init('landscape');
		
		$tab = $section->addTable(array('align'=>'center'));
		$tab->addRow();
		woCell($tab, 3000, 'Innslag', 'bold');
		woCell($tab, 3000, 'Navn', 'bold');
		woCell($tab, 3000, 'Mobil', 'bold');
		woCell($tab, 3000, 'Epost', 'bold');
		woCell($tab, 3000, 'Type', 'bold');
		woCell($tab, 3000, 'Kommune', 'bold');

		foreach($this->_getRenderData() as $person){
			$tab->addRow();
			woCell($tab, 3000, $person->getInnslag(), 'bold');
			woCell($tab, 3000, $person->getNavn());
			woCell($tab, 3000, $person->getMobil());
			woCell($tab, 3000, $person->getEpost());
			woCell($tab, 3000, $person->getTypeNavn());
			woCell($tab, 3000, $person->getKommune());
		}
		return $this->woWrite();
	}
	
	/**
	 * generateExcel function
	 * 
	 * Genererer et excel-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */		
	public function generateExcel(){
		global $objPHPExcel;
		$this->excel_init('landscape');
		exSheetName('PERSONER','6dc6c1');
		
		$row = 1;
		exCell('A'.$row, 'Innslag', 'bold');
		exCell('B'.$row, 'Navn', 'bold');
		exCell('C'.$row, 'Mobil', 'bold');
		exCell('D'.$row, 'E-post', 'bold');
		exCell('E'.$row, 'Type', 'bold');
		exCell('F'.$row, 'Kommune', 'bold');
		
		foreach($this->_getRenderData() as $person){
			$row++;
			exCell('A'.$row, $person->getInnslag(), 'bold');
			exCell('B'.$row, $person->getNavn());
			exCell('C'.$row, $person->getMobil());
			exCell('D'.$row, $person->getEpost());
			exCell('E'.$row, $person->getTypeNavn());
			exCell('F'.$row, $person->getKommune());
		}
		return $this->exWrite();
	}
	
	/**
	 * getRenderData
	 * Genererer rapport-grunnlaget som sendes inn til generate-funksjonene
	 * 
	**/
	private function _getRenderData() {
		try {
			$this->_prepare();
		} catch( Exception $e ) {
			die('<h2>Kunne ikke generere rapport!</h2>'
				.'<b>Fikk følgende feilmelding: </b>'. $e->getMessage());
		}
		
		$renderData = [];
		foreach( $this->getMonstring()->getInnslag()->getAll() as $innslag ) {
			if( $this->_skip( $innslag ) ) {
				continue;
			}
			if( $this->show('er_kontaktperson') ) {
				$renderData[] = new renderData( $innslag, $innslag->getKontaktPerson(), $this->getMonstring()->getType() );
			}
			if( $this->show('er_deltaker') ) {
				foreach( $innslag->getPersoner()->getAll() as $person ) {
					$renderData[] = new renderData( $innslag, $person, $this->getMonstring()->getType() );
				}
			}
		}
		return $renderData;
	}
	
	/**
	 * _skip
	 *
	 * Basert på valg i rapporten, skal innslaget inkluderes eller ekskluderes?
	 *
	 * @param $innslag
	 * @return bool
	**/
	private function _skip( $innslag ) {
		$skip = false;
		// SJEKK OM DEN GEOGRAFISKE TILHØRIGHETEN ER OK
		switch( $this->validateGeo ) {
			case 'fylke':
				$skip = !in_array( $innslag->getFylke()->getId(), $this->fylker );
				break;
			case 'kommune':
				$skip = !in_array( $innslag->getKommune()->getId(), $this->kommuner );
		}

		// Feilet geografi => skip it!
		if( $skip ) {
			return true;
		}
		
		// SJEKK OM INNSLAGET ER MED I PROGRAMMET
		// HVIS TREG: Skriv om denne til å laste inn alle aktive forestillinger, og loop disse.
		// Denne tilnærmingen laster inn veldig mange forestillingsobjekter..
		if($this->report_extended == 'hendelse') {
			return 0 == sizeof( array_intersect( $innslag->getProgram( $this->getMonstring()->getId() )->getIdArray( 'getAllInkludertSkjulte' ), $this->selectedType ));
		}

		// SJEKK OM TYPE INNSLAG ER OK
		return !in_array( $innslag->getType()->getKey(), $this->selectedType );
	}
	
	/**
	 * _prepare
	 * Har brukeren valgt minst én boks i hver kategori?
	 *
	**/
	private function _prepare() {
		// HAR VALGT PERSONER ELLER KONTAKTPERSONER
		if( !$this->show('er_kontaktperson') && !$this->show('er_deltaker') ) {
			throw new Exception('Du må velge enten kontaktpersoner, deltakere eller begge!');
		}

		$this->selectedType = [];
		// DETTE ER HENDELSE-RAPPORTEN, VELG HENDELSE
		if($this->report_extended == 'hendelse') {
			$counter == 0;
			foreach( $this->getMonstring()->getProgram()->getAllInkludertSkjulte() as $hendelse ) {
				$counter++;
				if( $this->show('hendelse_'.$hendelse->getId()) ) {
					$this->selectedType[] = $hendelse->getId();
				}
			}
			if( 0 == $counter ) {
				throw new Exception('For å kunne bruke denne rapporten må du sette opp minst én hendelse i <a href="?page=UKMprogram_admin">programmet</a>');
			}
			if( 0 == sizeof( $this->selectedType ) ) {
				throw new Exception('Du må velge minst én hendelse!');
			}
		// DETTE ER TYPE-RAPPORTEN
		} else {
			foreach( $this->getMonstring()->getInnslagTyper() as $innslagType ) {
				if( 1 == $innslagType->getId() ) {
					foreach( innslag_typer::getAllScene() as $innslagTypeScene ) {
						if( $this->show('type_'. $innslagTypeScene->getKey()) ) {
							$this->selectedType[] = $innslagTypeScene->getKey();
						}
					}
				} else {
					if( $this->show('type_'. $innslagType->getKey() ) ) {
						$this->selectedType[] = $innslagType->getKey();
					}
				}
			}
			if( 0 == sizeof( $this->selectedType ) ) {
				throw new Exception('Du må velge minst én type innslag!');
			}
		}
		
		// HAR VALGT GEOGRAFI
		if( 'land' == $this->getMonstring()->getType() ) {
			$this->validateGeo = 'fylke';
			foreach( fylker::getAll() as $fylke ) {
				// Hvis krysset av for kommune, legg til i kommune-array
				if( $this->show('geo_'. $fylke->getId() ) ) {
					$this->fylker[] = $fylke->getId();
				}				
			}
			if( 0 == sizeof( $this->fylker ) ) {
				throw new Exception('Du må velge minst ett fylke');
			}
		} else {
			$this->validateGeo = 'kommune';
			## Loop alle kommuner og sjekk om de er krysset av
			$this->kommuner = array();
			foreach( $this->getMonstring()->getKommuner() as $kommune ) {
				// Hvis krysset av for kommune, legg til i kommune-array
				if( $this->show('geo_'. $kommune->getId() ) ) {
					$this->kommuner[] = $kommune->getId();
				}
			}
			if( 0 == sizeof( $this->kommuner ) ) {
				throw new Exception('Du må velge minst én kommune/bydel!');
			}
		}
	}
	
	/**
	 * getMonstring
	 * Hjelper for å hente mønstringsdata
	**/
	private function getMonstring() {
		if( null == $this->monstring ) {
			$this->monstring = new monstring_v2( get_option('pl_id') );
		}
		return $this->monstring;
	}
}