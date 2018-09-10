<?php
require_once('program.report.php');
class extended_rapport extends valgt_rapport {
	function __construct($rapport, $kategori){
		$this->report_extended = 'vurderingsskjema';
		parent::__construct($rapport, $kategori);
	}
	
/*
	private function _removeOptions(){
		foreach($this->opts as $grp => $options) {
			if($grp !== 'h') {
				unset($this->optGrps[$grp]);
				unset($this->opts[$grp]);
			}		
		}
*/
/*
		$j = $this->optGrp('j', 'Jurymedlemmer');
		foreach( jury_medlemmer() as $medlem ) {
			$this->opt($j, $medlem, $medlem);
		}
*/
}
