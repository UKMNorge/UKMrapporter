<?php
include_once 'UKM/statistikk.class.php';

class valgt_rapport extends rapport {
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
		
		$g = $this->optGrp('h','Velg statistikkgrunnlag', 'radio');
		$this->opt($g, 'f_alle', 'Nasjonal statistikk (alle fylker)');
		$this->opt($g, 'f_crap', '');
		$this->opt($g, 'f_akershus', 'Akershus');
		$this->opt($g, 'f_austagder', 'Aust-Agder');
		$this->opt($g, 'f_buskerud', 'Buskerud');
		$this->opt($g, 'f_finnmark', 'Finnmark');
		$this->opt($g, 'f_hedmark', 'Hedmark');
		$this->opt($g, 'f_hordaland', 'Hordaland');
		$this->opt($g, 'f_moreogromsdal', 'Møre og Romsdal');
		$this->opt($g, 'f_nordtrondelag', 'Nord-Trøndelag');
		$this->opt($g, 'f_nordland', 'Nordland');
		$this->opt($g, 'f_oppland', 'Oppland');
		$this->opt($g, 'f_oslo', 'Oslo');
		$this->opt($g, 'f_rogaland', 'Rogaland');
		$this->opt($g, 'f_sognogfjordane', 'Sogn og Fjordane');
		$this->opt($g, 'f_sortrondelag', 'Sør-Trøndelag');
		$this->opt($g, 'f_telemark', 'Telemark');
		$this->opt($g, 'f_troms', 'Troms');
		$this->opt($g, 'f_vestagder', 'Vest-Agder');
		$this->opt($g, 'f_vestfold', 'Vestfold');
		$this->opt($g, 'f_ostfold', 'Østfold');

		$g = $this->optGrp('t','Personer og innslag');
		$this->opt($g, 't_pers', 'Vis antall personer');
//		$this->opt($g, 't_innslag', 'Vis antall innslag');
		

		$g = $this->formatGrp('s','Sammenligning og sortering', 'radio');
		$this->format($g, 's_alphabet', 'Alle mønstringer, sortert alfabetisk');
		$this->format($g, 's_order', 'Alle mønstringer, sortert etter deltakertall');
		$this->format($g, 's_tidligere', 'Sammenlign med tidligere år, sortert alfabetisk');

		$g = $this->formatGrp('v','Grafer');
		$this->format($g, 'v_graf', 'Vis grafer');


		$this->_postConstruct();
		
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
		$rows = $this->_preGenerate();

		echo $this->html_init(ucfirst($this->name).': '.$this->reportNiceName);

		## PERSONER-TABELLEN
		if($this->show('t_pers')||!$this->show('t_innslag')){
			echo '<h1>Personer</h1>';
			$this->_table($rows);
		}
		## INNSLAG-TABELLEN
		if($this->show('t_innslag')){
			echo '<h1>Innslag</h1>';
			$this->_table($rows, false);
		}
	}
	
	private function _preGenerate(){
		$this->_plids();
		foreach($this->pl_ids as $pl_id) {
			$monstring = new monstring($pl_id);
			$stats = $monstring->statistikk();
			$total = $stats->getTotal($monstring->get('season'));
			$nicename = $this->_correctMonstringName($monstring->get('pl_name'));
			$season = $monstring->get('season');

			$rows[$nicename][$season] = $stats->getStatArrayPerson($monstring->get('season'));
			$rows[$nicename][$season]['total'] = $total['persons'];

			$this->_stat($nicename, $season, $total['persons']);

			$thisCountOrder = $total['persons'];
			
			while(isset($this->countOrder[$thisCountOrder])) {
				$thisCountOrder += 0.001;
			}
			$this->countOrder[$thisCountOrder] = $nicename;

			ksort($rows[$nicename]);
		}
		krsort($this->countOrder);

		echo("<br /><br />");

		// Sorter lokalmønstringen etter "navn" (kommuneliste)
		if(!$this->showformat('s_order'))
			@ksort($rows);
		
		$this->antallMonstringer = sizeof($rows);
		
		// CALC (+SEASON) TOTALS
		foreach($rows as $monstring => $data) {
			foreach($data as $season => $r) {
				$subcats = $this->_subcats($r);
				$this->_totals($r, $subcats, $season);
			}
		}

		
		return $rows;
	}
	
	/**
	 * _table function
	 * 
	 * Generer en HTML-tabell for enten personer eller innslag
	 *
	 * @access private
	 * @return void
	 */	
	private function _table($monstringer,$person=true) {
		echo '<table cellpadding="2" cellspacing="2">';
		/*
if($this->showformat('s_tidligere')){
			echo '<tr><th colspan="17" align="left"><h1>Totalt</h1></th></tr>';
			$this->_tableheaders();

			foreach($this->seasonTotals as $season => $data){
				echo '<tr>'
						. '<td align="left">SUM</td>'
						. '<td align="left">'.$season.'</td>'
						. '<td align="left">'.$data['total'].'</td>'
						. '<td align="left">'.$data['bt_2'].'</td>'
						. '<td align="left">'.$data['bt_3'].'</td>'
						. '<td align="left">'.$data['bt_4'].'</td>'
						. '<td align="left">'.$data['bt_5'].'</td>'
						. '<td align="left">'.$data['bt_6'].'</td>'
						. '<td align="left">'.$data['bt_7'].'</td>'
						. '<td align="left">'.$data['bt_8'].'</td>'
						. '<td align="left">'.$data['bt_9'].'</td>'
						. '<td align="left">'.$data['bt_10'].'</td>'
						. '<td align="left">'.$data['bt_1'].'</td>'
						. '<td align="left">'.$data['sub_musikk'].'</td>'
						. '<td align="left">'.$data['sub_dans'].'</td>'
						. '<td align="left">'.$data['sub_litteratur'].'</td>'
						. '<td align="left">'.$data['sub_teater'].'</td>'
						. '<td align="left">'.$data['sub_annet'].'</td>'
					. '</tr>'
				;
			}
			echo '<tr><th colspan="17" align="left"><h1>Per m&oslash;nstring</h1></th></tr>';
		}	
*/
	
	
		// Hvis man sammenligner med andre mønstringer, vises tabellheader i toppen
		if(!$this->showformat('s_tidligere'))
			$this->_tableheaders();

		if($this->showformat('s_order')) {
			$loopArray = $this->countOrder;
		} else {
			$loopArray = $monstringer;
		}

		// Loop alle mønstringer
		if(is_array($monstringer) && sizeof($monstringer) > 0)
		foreach($loopArray as $key => $val) {
			if($this->showFormat('s_order')) {
				$nicename = $val;
				$info = $monstringer[$val];
			} else {
				$nicename = $key;
				$info = $val;
			}
		//foreach($monstringer as $nicename => $info) {
			// Hvis man sammenligner med tidligere år, vises tabellheader for hver mønstring
			if($this->showformat('s_tidligere')){
				echo '<tr><th colspan="17" align="left"><br /></th></tr>';
				$this->_tableheaders();
			}
			// Loop alle kolonner for mønstringsraden
			foreach($info as $season => $r){
				$subcats = $this->_subcats($r);
//				$this->_totals($r, $subcats, $season);
							
				echo '<tr>'
					. '<td align="left">'.$nicename.'</td>'
					. '<td align="left">'.$season.'</td>'
					. '<td align="left">'.$r['total'].'</td>'
					. '<td align="left">'.$r['bt_2'].'</td>'
					. '<td align="left">'.$r['bt_3'].'</td>'
					. '<td align="left">'.$r['bt_4'].'</td>'
					. '<td align="left">'.$r['bt_5'].'</td>'
					. '<td align="left">'.$r['bt_6'].'</td>'
					. '<td align="left">'.$r['bt_7'].'</td>'
					. '<td align="left">'.$r['bt_8'].'</td>'
					. '<td align="left">'.$r['bt_9'].'</td>'
					. '<td align="left">'.$r['bt_10'].'</td>'
					. '<td align="left">'.$r['bt_1'].'</td>'
					. '<td align="left">'.$subcats['musikk'].'</td>'
					. '<td align="left">'.$subcats['dans'].'</td>'
					. '<td align="left">'.$subcats['litteratur'].'</td>'
					. '<td align="left">'.$subcats['teater'].'</td>'
					. '<td align="left">'.$subcats['annet'].'</td>'
				. '</tr>';
			}
			if($this->showformat('v_graf')&&$this->showformat('s_tidligere')){
				echo '<tr><th colspan="17" align="right"><div style="width: 600px; height: 200px;" id="graph_'.$this->_statname($nicename).'">graf</div></th></tr>';
			}
		}
		
		if(!$this->showformat('s_tidligere')){
			echo '<tr>'
				.'<th align="left">SUM</th>'
				.'<th></th>'
				. '<th align="left">'.$this->totals['total'].'</th>'
				. '<th align="left">'.$this->totals['bt_2'].'</th>'
				. '<th align="left">'.$this->totals['bt_3'].'</th>'
				. '<th align="left">'.$this->totals['bt_4'].'</th>'
				. '<th align="left">'.$this->totals['bt_5'].'</th>'
				. '<th align="left">'.$this->totals['bt_6'].'</th>'
				. '<th align="left">'.$this->totals['bt_7'].'</th>'
				. '<th align="left">'.$this->totals['bt_8'].'</th>'
				. '<th align="left">'.$this->totals['bt_9'].'</th>'
				. '<th align="left">'.$this->totals['bt_10'].'</th>'
				. '<th align="left">'.$this->totals['bt_1'].'</th>'
				. '<th align="left">'.$this->totals['sub_musikk'].'</th>'
				. '<th align="left">'.$this->totals['sub_dans'].'</th>'
				. '<th align="left">'.$this->totals['sub_litteratur'].'</th>'
				. '<th align="left">'.$this->totals['sub_teater'].'</th>'
				. '<th align="left">'.$this->totals['sub_annet'].'</th>'
				.'</tr>';


			if($this->showformat('v_graf'))
			echo '<tr>'
				.'<td colspan="18" style="height: 750px; width: 100%;" id="graph_sum_pie">graf</td>'
				.'</tr>'
				.'<tr>'
				.'<td colspan="18" style="height: 750px; width: 900px;" id="graph_sum_combo">graf</td>'
				.'</tr>';
		}
		echo '</table>';	

		if($this->showformat('v_graf')&&$this->showformat('s_tidligere'))
			$this->_drawGraphs($person);
		elseif($this->showformat('v_graf'))
			$this->_drawStat($person);
		
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
		global $objPHPExcel;
		$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		
		$rows = $this->_preGenerate();

		if($this->show('t_pers')){
			exSheetName('PERSONER','6dc6c1');
			$this->_tableExcel($rows);
		}
		
		// HVis begge vises, legg til ark
		if($this->show('t_pers')&&$this->show('t_innslag')){
			$objPHPExcel->createSheet(1);
			$objPHPExcel->setActiveSheetIndex(1);
		}
		
		## INNSLAG-TABELLEN
		if($this->show('t_innslag')){
			exSheetName('INNSLAG','f3776f');
			$this->_tableExcel($rows,false);
		}		
	
		return $this->exWrite();
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
		$rows = $this->_preGenerate();

/* 		require('UKM/inc/word.inc.php'); */

		global $PHPWord;
		$section = $this->word_init('landscape', ucfirst($this->name).': '.$this->reportNiceName);
		## PERSONER-TABELLEN
		
		if($this->show('t_pers')){
			woText($section, 'Personer','grp');
			$this->_tableWord($section, $rows);
		}
		if($this->show('t_pers')&&$this->show('t_innslag'))
			$section->addPageBreak();
		## INNSLAG-TABELLEN
		if($this->show('t_innslag')){
			woText($section, 'Innslag','grp');
			$this->_tableWord($section, $rows, false);
		}
		return $this->woWrite();
	}

	/**
	 * _tableExcel function
	 * 
	 * Generer en Excel-tabell for enten personer eller innslag
	 *
	 * @access private
	 * @return void
	 */	
	private function _tableExcel($monstringer, $person=true) {
		$row = 1;
		// Hvis man sammenligner med andre mønstringer, vises tabellheader i toppen
		$this->_tableheadersExcel($row);


		if($this->showformat('s_order')) {
			$loopArray = $this->countOrder;
		} else {
			$loopArray = $monstringer;
		}

		// Loop alle mønstringer
		if(is_array($monstringer) && sizeof($monstringer) > 0)
		$row = 2;

		foreach($loopArray as $key => $val) {
			if($this->showFormat('s_order')) {
				$nicename = $val;
				$info = $monstringer[$val];
			} else {
				$nicename = $key;
				$info = $val;
			}
			foreach($info as $season => $r){
				$subcats = $this->_subcats($r);
//				$this->_totals($r, $subcats, $season);
				
				exCell('A'.$row, $nicename);
				exCell('B'.$row, $season);
				exCell('C'.$row, $r['total']);
				exCell('D'.$row, $r['bt_2']);
				exCell('E'.$row, $r['bt_3']);
				exCell('F'.$row, $r['bt_4']);
				exCell('G'.$row, $r['bt_5']);
				exCell('H'.$row, $r['bt_6']);
				exCell('I'.$row, $r['bt_7']);
				exCell('J'.$row, $r['bt_8']);
				exCell('K'.$row, $r['bt_9']);
				exCell('L'.$row, $r['bt_10']);
				exCell('M'.$row, $r['bt_1']);
				exCell('N'.$row, $subcats['musikk']);
				exCell('O'.$row, $subcats['dans']);
				exCell('P'.$row, $subcats['litteratur']);
				exCell('Q'.$row, $subcats['teater']);
				exCell('R'.$row, $subcats['annet']);
				$row++;
			}
		}
		

		if(!$this->showformat('s_tidligere')){
			exCell('A'.$row.':B'.$row, 'SUM','bold');
			for($i=1; $i<17; $i++) {
				exCell(i2a($i+2).$row, '=SUM('.i2a($i+2).'2:'.i2a($i+2).($row-1).')');
			}
		}

	}
	
	private function _totals($r, $subcats, $season) {
		$this->totals['total']			+= $r['total'];
		$this->totals['bt_1'] 			+= $r['bt_1'];
		$this->totals['bt_2'] 			+= $r['bt_2'];
		$this->totals['bt_3']		 	+= $r['bt_3'];
		$this->totals['bt_4'] 			+= $r['bt_4'];
		$this->totals['bt_5']		 	+= $r['bt_5'];
		$this->totals['bt_6']		 	+= $r['bt_6'];
		$this->totals['bt_7']		 	+= $r['bt_7'];
		$this->totals['bt_8'] 			+= $r['bt_8'];
		$this->totals['bt_9']		 	+= $r['bt_9'];
		$this->totals['bt_10']		 	+= $r['bt_10'];
		$this->totals['sub_musikk']		+=	$subcats['musikk'];
		$this->totals['sub_dans']		+=	$subcats['dans'];
		$this->totals['sub_litteratur']	+=	$subcats['litteratur'];
		$this->totals['sub_teater']		+=	$subcats['teater'];
		$this->totals['sub_annet']		+=	$subcats['annet'];
		
		$this->seasonTotals[$season]['total']			+= $r['total'];
		$this->seasonTotals[$season]['bt_1'] 			+= $r['bt_1'];
		$this->seasonTotals[$season]['bt_2'] 			+= $r['bt_2'];
		$this->seasonTotals[$season]['bt_3']			+= $r['bt_3'];
		$this->seasonTotals[$season]['bt_4'] 			+= $r['bt_4'];
		$this->seasonTotals[$season]['bt_5']			+= $r['bt_5'];
		$this->seasonTotals[$season]['bt_6']			+= $r['bt_6'];
		$this->seasonTotals[$season]['bt_7']			+= $r['bt_7'];
		$this->seasonTotals[$season]['bt_8'] 			+= $r['bt_8'];
		$this->seasonTotals[$season]['bt_9']		 	+= $r['bt_9'];
		$this->seasonTotals[$season]['bt_10']		 	+= $r['bt_10'];
		$this->seasonTotals[$season]['sub_musikk']		+=	$subcats['musikk'];
		$this->seasonTotals[$season]['sub_dans']		+=	$subcats['dans'];
		$this->seasonTotals[$season]['sub_litteratur']	+=	$subcats['litteratur'];
		$this->seasonTotals[$season]['sub_teater']		+=	$subcats['teater'];
		$this->seasonTotals[$season]['sub_annet']		+=	$subcats['annet'];
	}

	/**
	 * _tableWord function
	 * 
	 * Generer en WORD-tabell for enten personer eller innslag
	 *
	 * @access private
	 * @return void
	 */	
	private function _tableWord(&$section, $monstringer,$person=true) {
		if($this->showformat('s_tidligere')){
			woText($section, 'Totalt', 'h1');
			$tab = $section->addTable();
			$this->_tableheadersWord($tab);

			foreach($this->seasonTotals as $season => $data){
				$tab->addRow();
				woCell($tab, $this->_thww(), 'SUM','bold');
				woCell($tab, $this->_thww(), ' ','bold');
				woCell($tab, $this->_thww(), $data['total']);
				woCell($tab, $this->_thww(), $data['bt_2']);
				woCell($tab, $this->_thww(), $data['bt_3']);
				woCell($tab, $this->_thww(), $data['bt_4']);
				woCell($tab, $this->_thww(), $data['bt_5']);
				woCell($tab, $this->_thww(), $data['bt_6']);
				woCell($tab, $this->_thww(), $data['bt_7']);
				woCell($tab, $this->_thww(), $data['bt_8']);
				woCell($tab, $this->_thww(), $data['bt_9']);
				woCell($tab, $this->_thww(), $data['bt_10']);
				woCell($tab, $this->_thww(), $data['bt_1']);
				woCell($tab, $this->_thww(), $data['sub_musikk']);
				woCell($tab, $this->_thww(), $data['sub_dans']);
				woCell($tab, $this->_thww(), $data['sub_litteratur']);
				woCell($tab, $this->_thww(), $data['sub_teater']);
				woCell($tab, $this->_thww(), $data['sub_annet']);
			}
			$section->addPageBreak();
			woText($section, 'Per mønstring', 'h1');
		}	


		$tab = $section->addTable();

		// Hvis man sammenligner med andre mønstringer, vises tabellheader i toppen
		if(!$this->showformat('s_tidligere'))
			$this->_tableheadersWord($tab);

		if($this->showformat('s_order')) {
			$loopArray = $this->countOrder;
		} else {
			$loopArray = $monstringer;
		}

		// Loop alle mønstringer
		if(is_array($monstringer) && sizeof($monstringer) > 0)
		$row = 2;

		foreach($loopArray as $key => $val) {
			if($this->showFormat('s_order')) {
				$nicename = $val;
				$info = $monstringer[$val];
			} else {
				$nicename = $key;
				$info = $val;
			}
			// Hvis man sammenligner med tidligere år, vises tabellheader for hver mønstring
			if($this->showformat('s_tidligere')){
				$this->_tableheadersWord($tab);
			}
			// Loop alle kolonner for mønstringsraden
			foreach($info as $season => $r){
				$subcats = $this->_subcats($r);
//				$this->_totals($r, $subcats, $season);
				$tab->addRow();
				// Loop alle kolonner i rad
				woCell($tab, $this->_thww(), $nicename);
				woCell($tab, $this->_thww(), $season);
				woCell($tab, $this->_thww(), $r['total']);
				woCell($tab, $this->_thww(), $r['bt_2']);
				woCell($tab, $this->_thww(), $r['bt_3']);
				woCell($tab, $this->_thww(), $r['bt_4']);
				woCell($tab, $this->_thww(), $r['bt_5']);
				woCell($tab, $this->_thww(), $r['bt_6']);
				woCell($tab, $this->_thww(), $r['bt_7']);
				woCell($tab, $this->_thww(), $r['bt_8']);
				woCell($tab, $this->_thww(), $r['bt_9']);
				woCell($tab, $this->_thww(), $r['bt_10']);
				woCell($tab, $this->_thww(), $r['bt_1']);
				woCell($tab, $this->_thww(), $subcats['musikk']);
				woCell($tab, $this->_thww(), $subcats['dans']);
				woCell($tab, $this->_thww(), $subcats['litteratur']);
				woCell($tab, $this->_thww(), $subcats['teater']);
				woCell($tab, $this->_thww(), $subcats['annet']);

			}
		}
		
		if(!$this->showformat('s_tidligere')){
			$tab->addRow();
			woCell($tab, $this->_thww(), 'SUM','bold');
			woCell($tab, $this->_thww(), ' ','bold');
			woCell($tab, $this->_thww(), $this->totals['total']);
			woCell($tab, $this->_thww(), $this->totals['bt_2']);
			woCell($tab, $this->_thww(), $this->totals['bt_3']);
			woCell($tab, $this->_thww(), $this->totals['bt_4']);
			woCell($tab, $this->_thww(), $this->totals['bt_5']);
			woCell($tab, $this->_thww(), $this->totals['bt_6']);
			woCell($tab, $this->_thww(), $this->totals['bt_7']);
			woCell($tab, $this->_thww(), $this->totals['bt_8']);
			woCell($tab, $this->_thww(), $this->totals['bt_9']);
			woCell($tab, $this->_thww(), $this->totals['bt_10']);
			woCell($tab, $this->_thww(), $this->totals['bt_1']);
			woCell($tab, $this->_thww(), $this->totals['sub_musikk']);
			woCell($tab, $this->_thww(), $this->totals['sub_dans']);
			woCell($tab, $this->_thww(), $this->totals['sub_litteratur']);
			woCell($tab, $this->_thww(), $this->totals['sub_teater']);
			woCell($tab, $this->_thww(), $this->totals['sub_annet']);
		}
	}

	private function _subcats($r) {
		$subcats = array('musikk'=>0,
				 'teater'=>0,
				 'litteratur'=>0,
				 'dans'=>0,
				 'annet'=>0);

		foreach($r as $cat => $count) {
			if(strpos($cat, 'bt_') === false && $cat != 'total') {
				switch($cat) {
					case 'musikk':
					case 'music':
						$subcats['musikk'] += $count;
						break;
					case 'teater':
					case 'theater':
						$subcats['teater'] += $count;
						break;
					case 'dans':
						$subcats['dans'] += $count;
						break;
					case 'litteratur':
					case 'litterature':
						$subcats['litteratur'] += $count;
						break;
					default:
						$subcats['annet'] += $count;
						break;
				}
			}
		}
		return $subcats;
	}


	/**
	 * _correctMonstringName function
	 * 
	 * Korrigerer mønstringsnavn ved å bytte ut med kommuneliste / fylkesnavn
	 * Dette fordi brukerne herper mønstringsnavn, og ødelegger sammenligningsgrunnlaget
	 *
	 * @access private
	 * @return string safename
	 */	
	private function _correctMonstringName($monstring){
		if($this->show('f_alle'))
			return $monstring;
		$name = array_unique($this->goodNames[$this->_statname($monstring)]);
		return implode(', ', $name);
	}	
	
	/**
	 * _stat function
	 * 
	 * Lagrer statistikk for alle mønstringer per sesong, samt brukervennlige navn
	 *
	 * @access private
	 * @return void
	 */	
	private function _stat($monstring, $season, $total){
		$this->stat_nicename[$this->_statname($monstring)] = $monstring;
		$this->stat[$this->_statname($monstring)][$season] = $total;
		$this->statSum[$season] += $total;
	}

	/**
	 * _drawGraphs function
	 * 
	 * Generer javascriptkode for å tegne grafer
	 *
	 * @access private
	 * @return void
	 */	
	private function _drawGraphs($person){
		foreach($this->statSum as $season => $total)
			$this->statSum[$season] = round($total/$this->antallMonstringer);
		?>
		<script type="text/javascript" language="javascript">
			<?php
			foreach($this->stat as $monstring => $data){ ?>
				 var data = google.visualization.arrayToDataTable([
					['År', 'Påmeldte', 'TREND: <?= ($this->show('f_alle')?'Snitt':'Snitt i fylket')?>'],
					<?php
					 foreach($data as $season => $count){?>
					['<?= $season?>',<?= $count ?>,<?= $this->statSum[$season]?>],
					<?php } ?>
					]);
					
				var options = {
					title: '<?= $this->stat_nicename[$monstring] ?>',
					legend: {position: 'top'},
					colors: ['#1e4a45','#f3776f']
				};
				
				if(jQuery('#graph_<?= $monstring ?>').html() == 'graf')
					drawChart('graph_<?= $monstring ?>', data, options);
			<?php 
			} ?>
		</script>
		<?php
	}
	/**
	 * _drawStat function
	 * 
	 * Generer javascriptkode for å tegne SUM-statistikk
	 *
	 * @access private
	 * @return void
	 */	
	private function _drawStat($person){
		if($this->showformat('s_alphabet')) {
			ksort($this->stat);
			$statAray = $this->stat;
		}
		if($this->showformat('s_order')) {
			foreach($this->stat as $monstring => $data) {
				foreach($data as $season => $count) {
					$order[$count] = $monstring;
					// BUG IF SAME COUNT TWICE!
				}
			}
			krsort($order);
			foreach($order as $count => $monstring)  {
				$statArray[$monstring] = $this->stat[$monstring];
			}
		}
		
/*
		echo '<h1>THIS STAT</h1>';
		var_dump($this->stat);
		
		echo '<h1>STATARRAY</h1>';
		var_dump($statArray);
*/
		
	 ?>
		<script type="text/javascript" language="javascript">
			 var data = google.visualization.arrayToDataTable([
					['Mønstring', 'Påmeldte'],
					<?php
					foreach($statArray as $monstring => $data){
						foreach($data as $season => $count){
							echo '[\''.$this->stat_nicename[$monstring].'\','.$count.'],'."\r\n";
						}
					} ?>
					]);
					
				var options = {
					title: 'Mønstringer i tabellen',
					legend: {position: 'none'},
				};
				
				drawPie('graph_sum_pie', data, {title: 'Mønstringer i tabellen'});
				drawCombo('graph_sum_combo', data, options);
		</script>
		<?php
	}

	
	/**
	 * _statname function
	 * 
	 * Generer et mønstringsnavn som er trygt å bruke i grafens container-id
	 *
	 * @access private
	 * @return string safename
	 */	
	private function _statname($monstring){
		return preg_replace('/[^a-z]/i', '', strtolower($monstring));
	}
	

	
	
	/**
	 * _summer function
	 * 
	 * Summerer de forskjellige kolonnene i tabellen, for bruk til eventuell SUM-rad
	 *
	 * @access private
	 * @return void
	 */		
	private function _summer($key, $val){
		if(in_array($key, array('monstring','season','p_season')))
			return;
			
		if(!isset($this->sum[$key]))
			$this->sum[$key] = (int)$val;
		else
			$this->sum[$key] += (int)$val;
	}
	
	/**
	 * _tableheaders function
	 * 
	 * Genererer tabell-headers
	 *
	 * @access private
	 * @return void
	 */		
	 private function _tableheaders($array=false){
#		if($array)
#			return array('TOTAL','Scene','Video');
		?>
			<tr style="background-color: #ddd;">
				<th colspan="13"></th>
				<th colspan="5" align="center" style="background-color: #ccc;">Underkategorier av scene</th>
			</tr>
			<tr style="background-color: #ddd;">
				<th></th>
				<th>Sesong</th>
				<th>TOTAL</th>
				<th>Video</th>
				<th>Utstilling</th>
				<th>Konfer.</th>
				<th>Nettred.</th>
				<th>Matkultur</th>
				<th>Annet</th>
				<th>Arrang&oslash;r</th>
				<th>Scenet.</th>
				<th>Web-TV</th>
				<th>Scene</th>
				<td style="background-color: #ccc;">Musikk</td>
				<td style="background-color: #ccc;">Dans</td>
				<td style="background-color: #ccc;">Litteratur</td>
				<td style="background-color: #ccc;">Teater</td>
				<td style="background-color: #ccc;">Annet</td>
			</tr>
		<?php
	}

	/**
	 * _thww function
	 * 
	 * Table Headers Word Witdh
	 *
	 * @access private
	 * @return width in TWIPS (@!"#"*@æ)
	 */		

	private function _thww(){
		return 720;
	}
	/**
	 * _tableheaders function
	 * 
	 * Genererer tabell-headers
	 *
	 * @access private
	 * @return void
	 */		
	 private function _tableheadersWord(&$tab){
		$tab->addRow();
		$tab->addCell($this->_thww()*13, array('gridSpan'=>13))->addText('Type', array('bold'=>true), array('align'=>'center','bold'=>true));
		$tab->addCell($this->_thww()*5, array('gridSpan'=>5))->addText('Underkategorier av scene', array('bold'=>true), array('align'=>'center','bold'=>true));

		$tab->addRow();
		woCell($tab, $this->_thww(), '', 'bold');
		woCell($tab, $this->_thww(), 'Sesong', 'bold');
		woCell($tab, $this->_thww(), 'TOTAL', 'bold');
		woCell($tab, $this->_thww(), 'Video', 'bold');
		woCell($tab, $this->_thww(), 'Utstilling', 'bold');
		woCell($tab, $this->_thww(), 'Konfer.', 'bold');
		woCell($tab, $this->_thww(), 'Nettred..', 'bold');
		woCell($tab, $this->_thww(), 'Matkult.', 'bold');
		woCell($tab, $this->_thww(), 'Annet', 'bold');
		woCell($tab, $this->_thww(), 'Arrangør', 'bold');
		woCell($tab, $this->_thww(), 'Scenet.', 'bold');
		woCell($tab, $this->_thww(), 'Web-TV', 'bold');
		woCell($tab, $this->_thww(), 'Scene', 'bold');
		woCell($tab, $this->_thww(), 'Musikk');
		woCell($tab, $this->_thww(), 'Dans');
		woCell($tab, $this->_thww(), 'Litteratur');
		woCell($tab, $this->_thww(), 'Teater');
		woCell($tab, $this->_thww(), 'Annet');
	}
	/**
	 * _tableheaders function
	 * 
	 * Genererer tabell-headers
	 *
	 * @access private
	 * @return void
	 */		
	 private function _tableheadersExcel($row=1){
		exCell('A'.$row, '', 'bold');
		exCell('B'.$row, 'Sesong', 'bold');
		exCell('C'.$row, 'TOTAL', 'bold');
		exCell('D'.$row, 'Video', 'bold');
		exCell('E'.$row, 'Utstilling', 'bold');
		exCell('F'.$row, 'Konfer.', 'bold');
		exCell('G'.$row, 'Nettred..', 'bold');
		exCell('H'.$row, 'Matkult.', 'bold');
		exCell('I'.$row, 'Annet', 'bold');
		exCell('J'.$row, 'Arrangør', 'bold');
		exCell('K'.$row, 'Scenet.', 'bold');
		exCell('L'.$row, 'Web-TV', 'bold');
		exCell('M'.$row, 'Scene: total', 'bold');
		exCell('Q'.$row, 'Scene: Musikk');
		exCell('O'.$row, 'Scene: Dans');
		exCell('P'.$row, 'Scene: Litteratur');
		exCell('R'.$row, 'Scene: Teater');
		exCell('N'.$row, 'Scene: Annet');
	}
	
	/**
	 * _plids function
	 * 
	 * Henter ut en kommaseparert liste over aktuelle PL-ID'er basert på rapportvalg
	 *
	 * @access private
	 * @return void
	 */
	private function _plids(){
		if($this->show('f_akershus')) {
			$this->reportNiceName = 'Lokalmønstringer i Akershus';
			$fylke = 2;
		} elseif($this->show('f_austagder')){
			$this->reportNiceName = 'Lokalmønstringer i Aust-Agder';
			$fylke = 9;
		} elseif($this->show('f_buskerud')){
			$this->reportNiceName = 'Lokalmønstringer i Buskerud';
			$fylke = 6;
		} elseif($this->show('f_finnmark')){
			$this->reportNiceName = 'Lokalmønstringer i Finnmark';
			$fylke = 20;
		} elseif($this->show('f_hedmark')){
			$this->reportNiceName = 'Lokalmønstringer i Hedmark';
			$fylke = 4;
		} elseif($this->show('f_hordaland')){
			$this->reportNiceName = 'Lokalmønstringer i Hordaland';
			$fylke = 12;
		} elseif($this->show('f_moreogromsdal')){
			$this->reportNiceName = 'Lokalmønstringer i Møre og Romsdal';
			$fylke = 15;
		} elseif($this->show('f_nordtrondelag')){
			$this->reportNiceName = 'Lokalmønstringer i Nord-Trøndelag';
			$fylke = 17;
		} elseif($this->show('f_nordland')){
			$this->reportNiceName = 'Lokalmønstringer i Nordland';
			$fylke = 18;
		} elseif($this->show('f_oppland')){
			$this->reportNiceName = 'Lokalmønstringer i Oppland';
			$fylke = 5;
		} elseif($this->show('f_oslo')){
			$this->reportNiceName = 'Lokalmønstringer i Oslo';
			$fylke = 3;
		} elseif($this->show('f_rogaland')){
			$this->reportNiceName = 'Lokalmønstringer i Rogaland';
			$fylke = 11;
		} elseif($this->show('f_sognogfjordane')){
			$this->reportNiceName = 'Lokalmønstringer i Sogn og Fjordane';
			$fylke = 14;
		} elseif($this->show('f_sortrondelag')){
			$this->reportNiceName = 'Lokalmønstringer i Sør-Trøndelag';
			$fylke = 16;
		} elseif($this->show('f_telemark')){
			$this->reportNiceName = 'Lokalmønstringer i Telemark';
			$fylke = 8;
		} elseif($this->show('f_troms')){
			$this->reportNiceName = 'Lokalmønstringer i Troms';
			$fylke = 19;
		} elseif($this->show('f_vestagder')){
			$this->reportNiceName = 'Lokalmønstringer i Vest-Agder';
			$fylke = 10;
		} elseif($this->show('f_vestfold')){
			$this->reportNiceName = 'Lokalmønstringer i Vestfold';
			$fylke = 7;
		} elseif($this->show('f_ostfold')){
			$this->reportNiceName = 'Lokalmønstringer i Østfold';
			$fylke = 1;
		} else {
			$this->reportNiceName = 'Alle fylker';
			$fylke = 99;
		}

		if($fylke == 99) {
			$qry = new SQL("SELECT `pl_id`, `pl_name`
							FROM `smartukm_place` 
							WHERE `pl_fylke` != '0' 
							AND `pl_fylke` != '123456789'
							AND `pl_fylke` < 21
							".($this->showformat('s_tidligere') ? '': "AND `season` = '#season'")."
							", array('season'=>$this->m->g('season')));
		} else {
			$qry = new SQL("SELECT 
					`smartukm_place`.`pl_id`, 
					`smartukm_place`.`pl_name`
				   FROM `smartukm_place` 
				   JOIN `smartukm_rel_pl_k` ON (`smartukm_rel_pl_k`.`pl_id` = `smartukm_place`.`pl_id`)
				   JOIN `smartcore_kommune` ON (`smartcore_kommune`.`id` = `smartukm_rel_pl_k`.`k_id`)
				   WHERE `smartcore_kommune`.`idfylke` = '#idfylke'
				   ".($this->showformat('s_tidligere') ? '':  "AND `smartukm_place`.`season` = '#season'")."
				   GROUP BY `smartukm_place`.`pl_id`",
		   array('idfylke'=>$fylke, 'season'=>$this->m->g('season')));
		}
		$res = $qry->run();
		while($r = mysql_fetch_assoc($res)){
			$this->pl_nametrans[$r['pl_name']] = $r['pl_id'];
			$this->pl_ids[] = $r['pl_id'];
			$this->_finn_kommuner($r['pl_id'], $r['pl_name']);
		}
	}	
	
	/**
	 * _finn_kommuner function
	 * 
	 * Henter ut en kommaseparert liste over kommuner i mønstringen, som senere vil utgjøre
	 * safename (for sammenligning og output i tabellen)
	 *
	 * @access private
	 * @return void
	 */
	 private function _finn_kommuner($plid, $plname){
		$qry = new SQL("SELECT `name` 	
						FROM `smartukm_rel_pl_k` AS `rel`
						JOIN `smartukm_kommune` AS `k` ON (`rel`.`k_id` = `k`.`id`)
						WHERE `pl_id` = '#plid'
						ORDER BY `pl_k_id` ASC",
						array('plid'=>$plid));
		$res = $qry->run();
		while($r = mysql_fetch_assoc($res)){
			$this->goodNames[$this->_statname($plname)][] = utf8_encode($r['name']);
		}
	}
}