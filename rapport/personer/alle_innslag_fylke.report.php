<?php
	
require_once('alle_innslag.report.php');
require_once('UKM/fylker.class.php');

class extended_rapport extends valgt_rapport {
	function __construct($rapport, $kategori){
		$this->report_extended = 'innslag_fylke';

		$g = $this->optGrp('y', 'Type innslag');
		$g = $this->optGrp('f', 'Fylke');
		foreach( fylker::getAll() as $fylke ) {
			$this->opt( $g, 'f_'. $fylke->getId() , $fylke->getNavn() );
		}

		parent::__construct($rapport, $kategori);
	}
}