<?php
require_once('UKM/monstring.class.php');
require_once('UKM/innslag_typer.class.php');

class renderData {
	public function __construct( $innslag, $person ) {
		$this->type = $innslag->getType()->getKey();
		$this->typeNavn = $innslag->getType()->getNavn();
		$this->kommune = $innslag->getKommune()->getNavn();
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
		$g = $this->optGrp('geo','Deltakere fra');
		
		if( 'kommune' == $this->getMonstring()->getType() && $this->getMonstring()->erFellesMonstring() ) {
			foreach( $this->getMonstring()->getKommuner() as $kommune ) {
				$this->opt($g, 'geo_'. $kommune->getId(), $kommune->getNavn() );
			}
			
		}

		$e = $this->optGrp('er','som er');
		$this->opt($e, 'er_kontaktperson', 'kontaktperson');
		$this->opt($e, 'er_deltaker', 'deltaker');

		
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
		echo TWIG('sms.html.twig', $TWIG, dirname( dirname(__FILE__) ), true);
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

		$grupperte_innslag = $this->_innslag();
		
		$tab = $section->addTable(array('align'=>'center'));
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
		exCell('B'.$row, 'Navn');
		exCell('C'.$row, 'Mobil');
		exCell('D'.$row, 'E-post');
		exCell('E'.$row, 'Type');
		exCell('F'.$row, 'Kommune');
		
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
	

	
	
	
	private function _person( $person ) {
		return $person->getNavn() 
			.  ' - <div class="mobil UKMSMS">'. $person->getMobil() .'</div>'
			.  ' - <a href="mailto:'.$person->getEpost().'" class="UKMMAIL">'.$person->getEpost().'</a>';
	}
	
	
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
				$renderData[] = new renderData( $innslag, $innslag->getKontaktPerson() );
			}
			if( $this->show('er_deltaker') ) {
				foreach( $innslag->getPersoner()->getAll() as $person ) {
					$renderData[] = new renderData( $innslag, $person );
				}
			}
		}
		return $renderData;
	}
	
	
	private function _skip( $innslag ) {
		$skip = false;
		// SJEKK OM DEN GEOGRAFISKE TILHØRIGHETEN ER OK
		switch( $this->validateGeo ) {
			case 'all':
				$skip = false;
				break;
			case 'kommune':
				$skip = !in_array( $innslag->getKommune()->getId(), $this->kommuner );
		}
		// Feilet geografi => skip it!
		if( $skip ) {
			return true;
		}
		
		// SJEKK OM TYPE INNSLAG ER OK
		return !in_array( $innslag->getType()->getKey(), $this->selectedType );
	}
	
	private function _prepare() {
		// HAR VALGT PERSONER ELLER KONTAKTPERSONER
		if( !$this->show('er_kontaktperson') && !$this->show('er_deltaker') ) {
			throw new Exception('Du må velge enten kontaktpersoner, deltakere eller begge!');
		}
		
		// HAR VALGT TYPE?
		$this->selectedType = [];
		foreach( $this->getMonstring()->getInnslagTyper() as $innslagType ) {
			if( 1 == $innslagType->getId() ) {
				foreach( innslag_typer::getAllScene() as $innslagTypeScene ) {
					if( $this->show('type_'. $innslagTypeScene->getKey()) ) {
						$this->selectedType[] = $innslagTypeScene->getKey();
					}
				}
			} else {
				if( $this->show('type_'. $innslagType->getKey() ) ) {
					$this->selectedType[] = $innslagTypeScene->getKey();
				}
			}
		}
		if( 0 == sizeof( $this->selectedType ) ) {
			throw new Exception('Du må velge minst én type innslag!');
		}
		
		// HAR VALGT GEOGRAFI
		if( 'kommune' == $this->getMonstring()->getType() && $this->getMonstring()->erFellesmonstring() ) {
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
		} else {
			$this->validateGeo = 'all';
		}
	}
	
	private function getMonstring() {
		if( null == $this->monstring ) {
			$this->monstring = new monstring_v2( get_option('pl_id') );
		}
		return $this->monstring;
	}
}