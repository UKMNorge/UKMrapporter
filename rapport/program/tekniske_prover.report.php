<?php
require_once('program.report.php');
class extended_rapport extends valgt_rapport {
	function __construct($rapport, $kategori){
		$this->report_extended = 'tekniske_prover';
		parent::__construct($rapport, $kategori);
	}

	function frontInfo() {
		// Returnerer kontaktperson-informasjon som så printes på forsiden av rapporten i Word.
		if (!$this->pl_id) 
			$this->pl_id = get_option('pl_id');

		$m = new monstring($this->pl_id);
		$kontakt = $m->kontakter_pamelding();

		$t = array();
		$v = array();
		$ignoreList = array();
		foreach ($kontakt as $k) {
			if (!in_array($k->info['id'], $ignoreList)) {
				$ignoreList[] = $k->info['id'];
				$t[] = $k->info['name'].", tlf: ".$k->info['tlf'];
			}
		}

		if (count($t) > 1) {
			$v[] = "Kontaktpersoner:";
		} else {
			$v[] = "Kontaktperson:";
		}
		$v = array_merge($v, $t);

		return $v;
	}
}
?>