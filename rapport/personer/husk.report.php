<?php

require_once('UKM/monstring.class.php');
require_once(ABSPATH.'wp-content/plugins/UKMpr/class/MobilnummerForSted.class.php');
require_once('UKM/inc/twig-admin.inc.php');

class valgt_rapport extends rapport {
	
	public function __construct($rapport, $kategori) {
		parent::__construct($rapport, $kategori);

		$this->monstring = new monstring_v2(get_option('pl_id'));

		// Denne rapporten har ingen options eller optionGroups. Enda.
		if(get_option('site_type') == 'fylke') {
			$f = $this->optGrp('f', 'Hent mobilnummer for');
			$this->opt($f, 'f_fylke', 'Fylket');
			$this->opt($f, 'f_kommuner', 'Alle kommuner i fylket');
		}
		$this->_postConstruct();
	}

	public function generate() {

		echo $this->html_init('Mobilnummer fra husk.ukm.no');

		if( !($this->show('f_fylke') || $this->show('f_kommuner')) && get_option('site_type') == 'fylke') {
			echo '<h3>Ingen kilde valgt!</h3>';
		}


		if( get_option('site_type') == 'kommune' || get_option('site_type') == 'land' || ( get_option('site_type') == 'fylke' && $this->show('f_fylke') ) ) {
			$alle_nummer = $this->_getNumbersForPlace()->data;
			
			if(empty($alle_nummer)) {
				echo '<h3>Ingen mobilnummer</h3>';
				echo '<p>Les mer om husk.ukm.no på <a href="?page=UKMrekruttering">Rekruttering</a>.</p>';
				return;
			}

			echo '<h2>'.$this->monstring->getNavn().'</h2>';	
			echo '<ul class="rapporten">';

			$alle_nummer = $this->sortByNewest($alle_nummer);
			$oldDate = '';
			foreach ($alle_nummer as $nummer) {
				// Echo date if not equal to the previous date
				$newDate = new DateTime($nummer->timestamp->date);
				$newDate = TWIG_date($newDate->getTimestamp(), 'd. M');
				if($newDate != $oldDate) {
					echo '<h4>'.$newDate.'</h4>';
				}
				$oldDate = $newDate;

				// Echo current number
				echo '<li class="">';
				echo '<span class="UKMSMS">'.$nummer->mobil.'</span>';
				echo '</li>';
			}

			echo '</ul>';
		}

		if( get_option('site_type') == 'fylke' && $this->show('f_kommuner')) {
			echo '<h3>Ikke implementert</h3>';
			echo '<p>Det er dessverre ikke mulig å hente ut mobilnummer fra alle kommunene i fylket enda. Funksjonalitet kommer!</p>';
		}

		echo '<p>Les mer om husk.ukm.no på <a href="?page=UKMrekruttering">Rekruttering</a>.</p>';
		
	}

	private function _getNumbersForPlace() {
		// For dette fylke, kommune eller alle alle.
		if(get_option('site_type') == 'land') 
			$fylke_id = 0;
		else {
			$fylke = $this->monstring->getFylke();
			$fylke_id = $fylke->getId();
		}
		
		if(get_option('site_type') == 'kommune') {
			$kommune_id = $this->monstring->getKommuner()->first()->getId();
		}
		else 
			$kommune_id = 0;

		$mobilnummerCollection = new MobilnummerForSted($fylke_id, $kommune_id);

		#var_dump($mobilnummerCollection);
		$result = $mobilnummerCollection->fetchAll();
		#var_dump($result);
		return $result;
	}

	private function sortByNewest($numbers) {
		usort($numbers, function($a, $b) {
			// Returner positiv int om $a (element 1) er nyere enn ($b) element 2
			$a = new DateTime($a->timestamp->date);
			$b = new DateTime($b->timestamp->date);
		    return $a->getTimestamp() - $b->getTimestamp();;
		});
		return $numbers;
	}
}