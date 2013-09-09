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
		$this->opt($g, 't_innslag', 'Vis antall innslag');
		

		$g = $this->formatGrp('s','Sammenligning og sortering', 'radio');
		$this->format($g, 's_andre', 'Alle mønstringer sammenstilt, sortert alfabetisk');
		$this->format($g, 's_order', 'Alle mønstringer sammenstilt, sortert etter deltakertall');
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
		if($this->show('t_pers')){
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

			$this->countOrder[$nicename] = $total['persons'];

			ksort($rows[$nicename]);
		}
		
	var_dump($this->countOrder);		
		sort($this->countOrder);
	var_dump($this->countOrder);		

		echo("<br /><br />");

		// Sorter lokalmønstringen etter "navn" (kommuneliste)
		if(!$this->showformat('s_order'))
			@ksort($rows);
		
		$this->antallMonstringer = sizeof($rows);
		
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
		// Hvis man sammenligner med andre mønstringer, vises tabellheader i toppen
		if(!$this->showformat('s_tidligere'))
			$this->_tableheaders();

		// Loop alle mønstringer
		if(is_array($monstringer) && sizeof($monstringer) > 0)
		foreach($monstringer as $nicename => $info) {
			// Hvis man sammenligner med tidligere år, vises tabellheader for hver mønstring
			if($this->showformat('s_tidligere')){
				echo '<tr><th colspan="17" align="left"><br /></th></tr>';
				$this->_tableheaders();
			}
			// Loop alle kolonner for mønstringsraden
			foreach($info as $season => $r){
			
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
				.'<th></th>';
			if(is_array($this->sum) && sizeof($this->sum) > 0)
			foreach($this->sum as $key => $val){
				if(($person && strpos($key, 'p_')!==0) || (!$person && strpos($key, 'p_')===0))
					continue;
				echo '<th align="right">'. $val .'</th>';
			}
			if($this->showformat('v_graf'))
			echo '</tr>'
				.'<tr>'
				.'<td colspan="8" style="height: 400px;" id="graph_sum_pie">graf</td>'
				.'<td colspan="10" style="height: 400px;" id="graph_sum_combo">graf</td>'
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
		UKM_loader('excel');
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

		UKM_loader('word');
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

		// Loop alle mønstringer
		foreach($monstringer as $nicename => $info) {
			// Loop alle kolonner for mønstringsraden
			foreach($info as $season => $r){
				// Lagre statistikk for visning senere
				$row++;
				$col = 0;
				// Loop alle kolonner i rad
				foreach($r as $key => $val){
					if(($person && $this->show('t_pers')) || (!$person && !$this->show('t_pers')))
						$this->_summer($key, $val);
					if($key == 'monstring'){
						$col++;
						exCell(i2a($col).$row, $nicename);
					}else {
						if(($person && strpos($key, 'p_')!==0) || (!$person &&  strpos($key, 'p_')===0))
							continue;
						if(($person && $key != 'season') || (!$person)){
							$col++;
							exCell(i2a($col).$row, $val);
						}
					}
				}
			}
		}
		
		if(!$this->showformat('s_tidligere')){
			$row++;
			$col = 1;
			exCell(i2a($col).$row.':'.i2a($col+1).$row, 'SUM','bold');
			$col++;
			foreach($this->sum as $key => $val){
				if(($person && strpos($key, 'p_')!==0) || (!$person && strpos($key, 'p_')===0))
					continue;
				$col++;
				exCell(i2a($col).$row, '=SUM('.i2a($col).'2:'.i2a($col).($row-1).')');
			}
		}
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
		$tab = $section->addTable();

		// Hvis man sammenligner med andre mønstringer, vises tabellheader i toppen
		if(!$this->showformat('s_tidligere'))
			$this->_tableheadersWord($tab);

		// Loop alle mønstringer
		foreach($monstringer as $nicename => $info) {
			// Hvis man sammenligner med tidligere år, vises tabellheader for hver mønstring
			if($this->showformat('s_tidligere')){
				$this->_tableheadersWord($tab);
			}
			// Loop alle kolonner for mønstringsraden
			foreach($info as $season => $r){
				// Lagre statistikk for visning senere
				$this->_stat($nicename,$season, $r['p_deltakere'], $r['innslag']);
				$tab->addRow();
				// Loop alle kolonner i rad
				foreach($r as $key => $val){
					if(($person && $this->show('t_pers')) || (!$person && !$this->show('t_pers')))
						$this->_summer($key, $val);
					if($key == 'monstring'){
						woCell($tab, $this->_thww(), $nicename);
					}else {
						if(($person && strpos($key, 'p_')!==0) || (!$person &&  strpos($key, 'p_')===0))
							continue;
						if(($person && $key != 'season') || (!$person)){
							woCell($tab, $this->_thww(), $val);
						}
					}
				}
			}
		}
		
		if(!$this->showformat('s_tidligere')){
			$tab->addRow();
			woCell($tab, $this->_thww(), 'SUM','bold');
			woCell($tab, $this->_thww(), ' ','bold');
			foreach($this->sum as $key => $val){
				if(($person && strpos($key, 'p_')!==0) || (!$person && strpos($key, 'p_')===0))
					continue;
				woCell($tab, $this->_thww(), $val);
			}
		}
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
	private function _drawStat($person){ ?>
		<script type="text/javascript" language="javascript">
			 var data = google.visualization.arrayToDataTable([
					['Mønstring', 'Påmeldte'],
					<?php
					foreach($this->stat as $monstring => $data){
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
				<td style="background-color: #ccc;">Annet</td>
				<td style="background-color: #ccc;">Dans</td>
				<td style="background-color: #ccc;">Litteratur</td>
				<td style="background-color: #ccc;">Musikk</td>
				<td style="background-color: #ccc;">Teater</td>
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
		woCell($tab, $this->_thww(), 'Annet');
		woCell($tab, $this->_thww(), 'Dans');
		woCell($tab, $this->_thww(), 'Litteratur');
		woCell($tab, $this->_thww(), 'Musikk');
		woCell($tab, $this->_thww(), 'Teater');
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
		exCell('N'.$row, 'Scene: Annet');
		exCell('O'.$row, 'Scene: Dans');
		exCell('P'.$row, 'Scene: Litteratur');
		exCell('Q'.$row, 'Scene: Musikk');
		exCell('R'.$row, 'Scene: Teater');
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
			$this->goodNames[$this->_statname($plname)][] = $r['name'];
		}
	}
	
	
	/**
	 * _qry function
	 * 
	 * Genererer SQL-spørring for uthenting av statistikkdata
	 *
	 * @access private
	 * @return string SQL-query
	 */
	 private function _qry(){
		return "SELECT
				`smartukm_place`.`pl_name` AS `monstring`,
				`smartukm_place`.`season` AS `season`,
				`smartukm_place`.`season` AS `p_season`,
				SUM(`count_b`) AS `innslag`,
				SUM(`count_p`) AS `p_deltakere`,
				SUM(`bt_id_2`) AS `bt_2`,
				SUM(`bt_id_3`) AS `bt_3`,
				SUM(`bt_id_4`) AS `bt_4`,
				SUM(`bt_id_5`) AS `bt_5`,
				SUM(`bt_id_6`) AS `bt_6`,
				SUM(`bt_id_7`) AS `bt_7`,
				SUM(`bt_id_8`) AS `bt_8`,
				SUM(`bt_id_9`) AS `bt_9`,
				SUM(`bt_id_10`) AS `bt_10`,
				SUM(`bt_id_1`) AS `bt_1`,
				SUM(`annet`) AS `annet`,
				SUM(`dans`) AS `dans`,
				SUM(`litteratur`) AS `litteratur`,
				SUM(`musikk`) AS `musikk`,
				SUM(`teater`) AS `teater`,
				SUM(`p_bt_id_2`) AS `p_bt_2`,
				SUM(`p_bt_id_3`) AS `p_bt_3`,
				SUM(`p_bt_id_4`) AS `p_bt_4`,
				SUM(`p_bt_id_5`) AS `p_bt_5`,
				SUM(`p_bt_id_6`) AS `p_bt_6`,
				SUM(`p_bt_id_7`) AS `p_bt_7`,
				SUM(`p_bt_id_8`) AS `p_bt_8`,
				SUM(`p_bt_id_9`) AS `p_bt_9`,
				SUM(`p_bt_id_10`) AS `p_bt_10`,
				SUM(`p_bt_id_1`) AS `p_bt_1`,
				SUM(`p_annet`) AS `p_annet`,
				SUM(`p_dans`) AS `p_dans`,
				SUM(`p_litteratur`) AS `p_litteratur`,
				SUM(`p_musikk`) AS `p_musikk`,
				SUM(`p_teater`) AS `p_teater`
			  FROM `ukmno_statistics_subscription`
			  JOIN `ukmno_statistics_subscription_details` ON (`ukmno_statistics_subscription_details`.`day_id`=`ukmno_statistics_subscription`.`day_id`)
			  JOIN `smartukm_place` ON (`smartukm_place`.`pl_id` = `ukmno_statistics_subscription`.`pl_id`)
			  WHERE `ukmno_statistics_subscription`.`pl_id` IN (".implode(',',$this->pl_ids).")
  			  GROUP BY `ukmno_statistics_subscription`.`pl_id`
  			  ".($this->showformat('s_order') ? "ORDER BY `p_deltakere` DESC" : "ORDER BY `smartukm_place`.`pl_name` ASC, `smartukm_place`.`season` ASC")."
			  ";
	}
}