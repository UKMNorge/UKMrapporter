<?php
require_once('program.report.php');
class extended_rapport extends valgt_rapport {
	function __construct($rapport, $kategori){
		$this->report_extended = 'tekniske_prover';
		parent::__construct($rapport, $kategori);
	}
}
?>