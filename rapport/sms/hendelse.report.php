<?php
require_once('type.report.php');

class extended_rapport extends valgt_rapport {
	function __construct($rapport, $kategori){
		$this->report_extended = 'hendelse';
		parent::__construct($rapport, $kategori);
	}

	function frontInfo() {
		return 'SMS-lister fra hendelser';
	}
}
?>