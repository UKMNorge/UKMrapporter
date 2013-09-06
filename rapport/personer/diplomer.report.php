<?php
require_once('alle_innslag.report.php');
class extended_rapport extends valgt_rapport {
	/**
	 * class constructor
	 * 
	 * Initierer parent og sier hvilke options som er mulig å velge i rapporten
	 *
	 * @access public
	 * @param rapport navn på rapport
	 * @param kategori navn på sorteringskategori for rapport
	 * @return class object
	 */
	public function __construct($rapport, $kategori){
		parent::__construct($rapport, $kategori);
		
		unset($this->optGrps['t']);
		unset($this->opts['t']);

		unset($this->optGrps['p']);
		unset($this->opts['p']);
		
		unset($this->formatGrps['op']);
		unset($this->format['op']);
		
		unset($this->formatGrps['n']);
		unset($this->format['n']);

		unset($this->formatGrps['n']);
		unset($this->format['n']);

		unset($this->helper_files);
		
		unset($this->optGrps['i']);
		unset($this->opts['i']);		
/*
		$unset_opts['i']['i_katogsjan']	= true;
		$unset_opts['i']['i_varig']		= true;
		$unset_opts['i']['i_tekn']		= true;
		$unset_opts['i']['i_konf']		= true;

		foreach($unset_opts as $grp => $options)
			foreach($options as $opt => $tull)
				unset($this->opts[$grp][$opt]);
*/
	}

	/**
	 * generateExcel function
	 * 
	 * Genererer et excel-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */		
	public function generateExcel(){
		return false;
	}
		
	/**
	 * generateWord function
	 * 
	 * Genererer et word-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */	
	public function generateWord(){
		UKM_loader('word');
		global $PHPWord;	
		$this->wordWithoutHeaders = true;
		$section = $this->word_init();
		$grupperte_innslag = $this->_innslag();
		$genererte_personer = array();

		foreach($grupperte_innslag as $grp => $innslag) {
			if($grp !== 0) {
				// TABELLEN OG TOM ØVERSTE CELLE (placeholder / spacetaker? :p)
				$tab = $section->addTable();
				$tab->addRow(12600);
				$c = $tab->addCell(11000);
				woText($c, ' ');
				// DIPLOM-CELLE
				$tab->addRow();
				$c = $tab->addCell(10000);
				woText($c, $grp, 'page_divider');
				$section->addPageBreak();
			}
			foreach($innslag as $inn) {
				$kontakt = $inn->kontaktperson();
				$inn->loadGeo();
				$personer = $inn->personer();
				$personText = array();
				foreach($personer as $pers) {
					if(in_array($pers['p_id'], $genererte_personer))
						continue;
					$genererte_personer[] = $pers['p_id'];
					$p = new person($pers['p_id']);
					
					// TABELLEN OG TOM ØVERSTE CELLE (placeholder / spacetaker? :p)
					$tab = $section->addTable();
					$tab->addRow(12300);
					$c = $tab->addCell(11000);
					woText($c, ' ');
					
					// DIPLOM-CELLE
					$tab->addRow();
					$c = $tab->addCell(10000);
					woText($c, $p->g('name'), 'diplom_navn');
					woText($c, ' har deltatt på ', 'diplom_mellom');
					if($this->m->g('type') == 'land')
						woText($c, ''. $this->m->g('pl_name'), 'diplom_monstring');
					else
						woText($c, 'UKM i '. $this->m->g('pl_name'), 'diplom_monstring');
#					woText($c, $inn->g('fylke').($this->show('i_kommune') ? ' - '.$inn->g('kommune') : ''));
#					woText($c, $inn->g('kommune'));
				}
			}
		}
		return $this->woWrite();
	}
	
	
	/**
	 * generate function
	 * 
	 * Genererer selve rapporten i HTML-visning
	 *
	 * @access public
	 * @return void
	 */	
	public function generate() {
		echo $this->html_init('Innslag og personer');
		echo '<h2>Rapport klar!</h2>'
			.'<a href="'.$this->generateWord().'" id="downloadLink">Klikk her for å laste ned rapporten i word-format</a>'
			;
	}	
}
?>