<?php

use UKMNorge\Geografi\Fylker;

require_once('UKM/aviser.class.php');
require_once('UKM/monstring.class.php');
require_once('UKM/Autoloader.php');
require_once(ABSPATH . 'wp-content/plugins/UKMpr/class/MobilnummerForSted.class.php');

class valgt_rapport extends rapport
{

	public function __construct($rapport, $kategori)
	{
		parent::__construct($rapport, $kategori);
		$this->monstring = new monstring_v2(get_option('pl_id'));
		$this->_postConstruct();
	}

	public function generate()
	{
		echo $this->html_init('Kontaktinfo til lokalaviser');

		$aviser = new aviser();
		$adresser = [];

		if (get_option('site_type') == 'land') {
			$fylker = Fylker::getAll();
		} else {
			$fylker = [$this->monstring->getFylke()];
		}

		foreach ($fylker as $fylke) {
			$aviser->reset();
			foreach ($aviser->getAllByFylke($fylke->getId()) as $avis) {
				$adresser[] = $avis->getEmail();
			}
		}

		echo '<h2>Komma-separerte e-postadresser (MAC)</h2>' .
			implode(', ', $adresser) .
			'<h2>Semikolon-separerte e-postadresser (PC)</h2>' .
			implode('; ', $adresser);
	}
}
