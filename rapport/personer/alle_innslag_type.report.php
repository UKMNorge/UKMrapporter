<?php
	
require_once('alle_innslag.report.php');
class extended_rapport extends valgt_rapport {
	function __construct($rapport, $kategori){
		$this->report_extended = 'innslag_type';

		$g = $this->optGrp('y', 'Type innslag');
		$this->opt($g, 'y_alle', 'Alle innslag (overstyrer andre valg)');
		$this->opt($g, 'y_scene', 'Scene');
		$this->opt($g, 'y_film', 'Film');
		$this->opt($g, 'y_utstilling', 'Utstilling');
		$this->opt($g, 'y_arrangor', 'Arrangør');
		$this->opt($g, 'y_konferansier', 'Konferansier');
		$this->opt($g, 'y_media', 'UKM Media');
		$this->opt($g, 'y_ressurs', 'UKM-ressurs');


		parent::__construct($rapport, $kategori);
	}
}