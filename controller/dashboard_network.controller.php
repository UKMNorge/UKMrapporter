<?php 
$raport_icon_size = 50; 
$category_icon_size = 32;

class kategori {
	var $id = null;
	var $navn = null;
	var $icon = null;
	var $rapporter = null;
	
	public function __construct( $id, $navn, $icon ) {
		$this->id = $id;
		$this->navn = $navn;
		$this->icon = $icon;
		$this->rapporter = [];
	}
	
	public function getId() {
		return $this->id;
	}
	public function getNavn() {
		return $this->navn;
	}
	public function getIcon() {
		return $this->icon;
	}
	public function addRapport( $rapport ) {
		$this->rapporter[] = $rapport;
	}
	public function getRapporter() {
		return $this->rapporter;
	}
}

class rapporter {
	var $type = null;
	var $kategorier = null;
	
	public function __construct( ) {
		$this->kategorier = [];
		$this->add('monstring', 'antall_monstringer');
	}
	
	public function getAll() {
		return $this->kategorier;
	}	
	
	public function add( $kategoriID, $rapportID ) {
		$this->getKategori( $kategoriID )->addRapport( $this->createRapport( $rapportID ) );
	}
	
	public function getKategori( $kategoriID ) {
		if( !isset( $this->kategorier[ $kategoriID ] ) ) {
			$this->kategorier[ $kategoriID ] = $this->createKategori( $kategoriID );
		}
		return $this->kategorier[ $kategoriID ];
	}
	
	public function createKategori( $kategoriID ) {
		switch( $kategoriID ) {
			case 'festival':	return new kategori('festival', 'Festivalen', 'palm-tree');
			case 'monstring':	return new kategori('monstring', 'Mønstring', 'hus');
			case 'kontakt':		return new kategori('megaphone', 'Kontakt', 'megaphone');
			case 'personer':	return new kategori('personer', 'Personer', 'people');
			case 'program':		return new kategori('program', 'Program', 'chart');
		}
	}
	
	public function createRapport( $rapportID ) {
		$rapport = new stdClass();
		switch( $rapportID ) {
			case 'antall_monstringer':
				$rapport->icon = 'abacus';
				$rapport->link = 'network=monstringer_per_fylke';
				$rapport->navn = 'Mønstringer per fylke';
				$rapport->beskrivelse = 'Antall mønstringer som har vært arrangert de siste årene, gruppert per fylke';
			break;
		}
		return $rapport;
	}
}

$TWIG['rapporter'] = new rapporter( get_option('site_type') );