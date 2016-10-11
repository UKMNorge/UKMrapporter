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
	
	public function __construct( $monstring_type ) {
		$this->type = $monstring_type;
		$this->kategorier = [];
		switch( $this->type ) {
			case 'land':
				$this->add('monstring', 'statistikk');

				$this->add('festival', 'reise');
				$this->add('festival', 'mat');
				$this->add('festival', 'ledere');
				$this->add('festival', 'ledermiddag');
				$this->add('festival', 'hotell');
				$this->add('festival', 'deltakerovernatting');

				$this->add('kontakt', 'sms_type');
				$this->add('kontakt', 'sms_hendelse');
				$this->add('kontakt', 'kontaktliste');
				$this->add('kontakt', 'duplikat');
				$this->add('kontakt', 'sveveeksport');
				
				$this->add('personer', 'alle_innslag');
				$this->add('personer', 'inn_og_utlevering');
				$this->add('personer', 'diplomer');

				$this->add('program', 'program');
				$this->add('program', 'tekniske_prover');
				$this->add('program', 'kunstkatalog');
				$this->add('program', 'media');
				$this->add('program', 'juryskjema');
				$this->add('program', 'fylkestimeplan');
				
				$this->add('helarig', 'husk');
				$this->add('helarig', 'rsvp');
			break;
			case 'fylke':
				$this->add('monstring', 'statistikk');
				$this->add('monstring', 'videresendingsskjema');
				$this->add('monstring', 'lokalkontakter');

				$this->add('kontakt', 'sms_type');
				$this->add('kontakt', 'sms_hendelse');
				$this->add('kontakt', 'kontaktliste');
				$this->add('kontakt', 'duplikat');

				$this->add('personer', 'alle_innslag');
				$this->add('personer', 'inn_og_utlevering');
				$this->add('personer', 'turneliste');
				$this->add('personer', 'diplomer');
				$this->add('personer', 'videresendte');
				
				$this->add('program', 'program');
				$this->add('program', 'tekniske_prover');
				$this->add('program', 'kunstkatalog');
				$this->add('program', 'media');
				$this->add('program', 'juryskjema');
				
				$this->add('helarig', 'husk');
				$this->add('helarig', 'rsvp');
			break;
			default:
				$this->add('monstring', 'statistikk');

				$this->add('kontakt', 'sms_type');
				$this->add('kontakt', 'sms_hendelse');
				$this->add('kontakt', 'kontaktliste');
				$this->add('kontakt', 'duplikat');
				
				$this->add('personer', 'alle_innslag');
				$this->add('personer', 'inn_og_utlevering');
				$this->add('personer', 'diplomer');
				$this->add('personer', 'videresendte');

				$this->add('program', 'program');
				$this->add('program', 'tekniske_prover');
				$this->add('program', 'kunstkatalog');
				$this->add('program', 'juryskjema');
				
				$this->add('helarig', 'husk');
				$this->add('helarig', 'rsvp');
			break;
		}
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
			case 'helarig':		return new kategori('helarig', 'Helårig', 'group-meeting');
		}
	}
	
	public function createRapport( $rapportID ) {
		$rapport = new stdClass();
		switch( $rapportID ) {
			case 'reise':
				$rapport->icon = 'buss';
				$rapport->link = 'festival=reise';
				$rapport->navn = 'Reise';
				$rapport->beskrivelse = 'Ankomst- og avreiseinformasjon fra fylkenes videresending';
			break;
			case 'mat':
				$rapport->icon = 'medical-case';
				$rapport->link = 'festival=tilrettelegging';
				$rapport->navn = 'Mat og tilrettelegging';
				$rapport->beskrivelse = 'Allergier og andre behov for tilrettelegging';
			break;
			case 'ledere':
				$rapport->icon = 'user-black';
				$rapport->link = 'festival=ledere';
				$rapport->navn = 'Ledere';
				$rapport->beskrivelse = 'Kontaktinfo til fylkenes registrerte ledere';
			break;
			case 'ledermiddag':
				$rapport->icon = 'chef';
				$rapport->link = 'festival=ledermiddag';
				$rapport->navn = 'Ledermiddag';
				$rapport->beskrivelse = 'Hvilke ledere skal være med på ledermiddagen';
			break;
			case 'hotell':
				$rapport->icon = 'hotel';
				$rapport->link = 'festival=overnatting_ledere';
				$rapport->navn = 'Hotell-overnatting';
				$rapport->beskrivelse = 'Bestillingsskjema for ledernes hotellovernattinger';
			break;
			case 'deltakerovernatting':
				$rapport->icon = 'tent';
				$rapport->link = 'festival=overnatting_deltakere';
				$rapport->navn = 'Deltakerovernatting';
				$rapport->beskrivelse = 'Ledere som sover i deltakerovernattingen per natt';
			break;


			case 'statistikk':
				$rapport->icon = 'graph';
				$rapport->link = 'stat=home';
				$rapport->navn = 'Statistikksenter';
				$rapport->beskrivelse = 'Alt om din mønstring i tall og grafer';
			break;


			case 'sms_type':
				$rapport->icon = 'mobile';
				$rapport->link = 'rapport=type&kat=sms';
				$rapport->navn = 'Mobilnummer etter type';
				$rapport->beskrivelse = 'Kontaktinfo til innslag av gitt innslagstype';
			break;
			case 'sms_hendelse':
				$rapport->icon = 'mobile';
				$rapport->link = 'rapport=hendelse&kat=sms';
				$rapport->navn = 'Mobilnummer etter hendelser';
				$rapport->beskrivelse = 'Kontaktinfo til innslag i en/flere hendelser';
			break;
			case 'kontaktliste':
				$rapport->icon = 'contact';
				$rapport->link = 'rapport=kontaktlister&kat=personer';
				$rapport->navn = 'Kontaktlister';
				$rapport->beskrivelse = 'Oversikt over alle personer på din mønstring';
			break;
			case 'duplikat':
				$rapport->icon = 'banana-twins';
				$rapport->link = 'rapport=duplikate_mobilnummer&kat=personer';
				$rapport->navn = 'Duplikate mobilnummer';
				$rapport->beskrivelse = 'Mobilnummer på din mønstring som brukes av to eller flere personer';
			break;
			case 'sveveeksport':
				$rapport->icon = 'excel';
				$rapport->link = 'festival=sveveeksport';
				$rapport->navn = 'Sveve-eksport';
				$rapport->beskrivelse = 'Excel-ark for bruk til import i Sveve og planlagte SMS';
			break;


			case 'alle_innslag':
				$rapport->icon = 'toy-blue';
				$rapport->link = 'rapport=alle_innslag&kat=personer';
				$rapport->navn = 'Alle innslag';
				$rapport->beskrivelse = 'Alle innslag på mønstringen med tekniske krav og konferansiertekster';
			break;
			case 'inn_og_utlevering':
				$rapport->icon = 'tasklist';
				$rapport->link = 'rapport=inn_og_utlevering&kat=personer';
				$rapport->navn = 'Inn- og utlevering';
				$rapport->beskrivelse = 'Hold oversikt over inn- og utlevering av kunst og/eller film på din mønstring';
			break;
			case 'turneliste':
				$rapport->icon = 'adium';
				$rapport->link = 'rapport=turneliste&kat=personer';
				$rapport->navn = 'Duplikate deltakere i fylket';
				$rapport->beskrivelse = 'Innslag som deltar på flere av dine lokalmønstringer';
			break;
			case 'diplomer':
				$rapport->icon = 'diplom';
				$rapport->link = 'rapport=diplomer&kat=personer';
				$rapport->navn = 'Diplomer';
				$rapport->beskrivelse = 'Word-fil som skrives ut på de trykte diplomene';
			break;
			case 'videresendte':
				$rapport->icon = 'smiley-smile-big';
				$rapport->link = 'rapport=videresendte&kat=personer';
				$rapport->navn = 'Videresendte fra din mønstring';
				$rapport->beskrivelse = 'Kontaktinfo for deltakere videresendt fra din mønstring';
			break;
			case 'videresendingsskjema':
				$rapport->icon = 'buss';
				$rapport->link = 'rapport=videresendingsskjema&kat=monstring';
				$rapport->navn = 'Videresendingsskjema';
				$rapport->beskrivelse = 'Viser hva kommunene har skrevet i videresendingsskjemaet';
			break;
			case 'lokalkontakter':
				$rapport->icon = 'user-black';
				$rapport->link = 'rapport=lokalkontakter&kat=monstring';
				$rapport->navn = 'Lokalkontakter';
				$rapport->beskrivelse = 'Lister ut alle lokalkontakter i fylket';
			break;


			case 'program':
				$rapport->icon = 'list';
				$rapport->link = 'rapport=program&kat=program';
				$rapport->navn = 'Program';
				$rapport->beskrivelse = 'Med omslaget fra <a href="admin.php?page=UKMmateriell">designgeneratoren</a> er dette alt du trenger!';
			break;
			case 'tekniske_prover':
				$rapport->icon = 'settings';
				$rapport->link = 'rapport=tekniske_prover&kat=program';
				$rapport->navn = 'Tekniske prøver';
				$rapport->beskrivelse = 'Skreddersy en oversikt til dine teknikere for en best mulig mønstring';
			break;
			case 'kunstkatalog':
				$rapport->icon = 'art-supplies';
				$rapport->link = 'rapport=kunstkatalog&kat=program';
				$rapport->navn = 'Kunstkatalog';
				$rapport->beskrivelse = 'Alt (unntatt bilder) for å lage kunstkatalog. Bruk excel-rapporen for å flette etiketter i word';
			break;
			case 'juryskjema':
				$rapport->icon = 'gavel';
				$rapport->link = 'rapport=analogt_juryskjema&kat=program';
				$rapport->navn = 'Juryskjema';
				$rapport->beskrivelse = 'Juryskjema for utskrift';
			break;
			case 'fylkestimeplan':
				$rapport->icon = 'schedule';
				$rapport->link = 'fylkestimeplan=generate';
				$rapport->navn = 'Fylkestimeplaner';
				$rapport->beskrivelse = 'Genererer en fylkesvis timeplan for alle hendelser';
			break;
			case 'media':
				$rapport->icon = 'media';
				$rapport->link = 'festival=media';
				$rapport->navn = 'Mediefiler';
				$rapport->beskrivelse = 'Last ned mediefiler som skal brukes til program og lignende';
			break;

			case 'husk':
				$rapport->icon = 'megaphone';
				$rapport->link = 'rapport=husk&kat=helarig';
				$rapport->navn = 'Husk UKM';
				$rapport->beskrivelse = 'Mobilnummer fra rekrutterings-verktøyet "Husk UKM"';
			break;

			case 'rsvp':
				$rapport->icon = 'group-meeting';
				$rapport->link = 'rapport=rsvp&kat=helarig';
				$rapport->navn = 'Helårig UKM';
				$rapport->beskrivelse = 'Alt om dine helårige arrangementer';
			break;
			
			default:
			case 'default':
				$rapport->icon = 'ladybug';
				$rapport->link = 'link';
				$rapport->navn = 'Tittel';
				$rapport->beskrivelse = 'Beskrivelse';
			break;

		}
		return $rapport;
	}
	
}

$TWIG['rapporter'] = new rapporter( get_option('site_type') );