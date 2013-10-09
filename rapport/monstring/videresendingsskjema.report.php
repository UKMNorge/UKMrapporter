<?php
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
	}

	public function generateExcel() {
		global $objPHPExcel;
		//$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		
		exSheetName('VIDERESENDINGSSKJEMA');
		foreach( $this->_data() as $key => $val)
			$$key = $val;
			
		$col = $row = 1;
		exCell(i2a($col).$row, 'Avsnitt', 'bold');
		$col++;
		exCell(i2a($col).$row, 'Spørsmål', 'bold');
		foreach($monstringsnavn as $pl_name) {
			$col++;
			exCell(i2a($col).$row.':'.i2a($col+2).$row, $pl_name, 'bold');
			$col = $col+2;
		}
		$row++;
		$col = 1;
		foreach($sporsmal as $group => $data) {
			foreach( $data as $question ) {
				exCell(i2a($col).$row, $group, 'bold');
				$col++;
				exCell(i2a($col).$row, $question['title'], 'bold');
				foreach($monstringsnavn as $lokalid => $pl_name) {
					$answer = $this->_styleAnswer($svar[$lokalid][$question['id']], true);
					$col++;
					if(is_array($answer)) {
						exCell(i2a($col).$row, $answer[0]);
						$col++;
						exCell(i2a($col).$row, $answer[1]);
						$col++;
						exCell(i2a($col).$row, $answer[2]);
					} else {
						exCell(i2a($col).$row.':'.i2a($col+2).$row, $answer);
						$col += 2;
					}
				}
				$col = 1;
				$row++;
			}
		}
		return $this->exWrite();
	}


	public function generateWord() {
		global $PHPWord;		
		$section = $this->word_init('landscape');

		foreach( $this->_data() as $key => $val)
			$$key = $val;
			
		$colsize = round(17000 / sizeof($monstringsnavn));

		foreach($sporsmal as $group => $data) {
			woText($section, $group, 'h1');

			$tab = $section->addTable(array('align'=>'center'));
			$tab->addRow();

			$c = $tab->addCell($colsize);
			woText($c, 'Spørsmål','bold');
			foreach($monstringsnavn as $pl_name) {
				$c = $tab->addCell($colsize);
				woText($c, $pl_name, 'bold');
			}
			foreach( $data as $question ) {
				$tab->addRow();
				
				$c = $tab->addCell($colsize);
				woText($c, $question['title'],'bold');
				foreach($monstringsnavn as $lokalid => $pl_name) {
					$c = $tab->addCell($colsize);
					$answer = $this->_styleAnswer($svar[$lokalid][$question['id']], true);
					if(is_array($answer))
						foreach($answer as $a)
							woText($c, $a);
					else
						woText($c, $answer);
				}
			}
			$section->addPageBreak();
		}
		return $this->woWrite();
	}

	
	private function _data() {
		$this->fylke = get_option('fylke');
		$sql = new SQL("SELECT * FROM `smartukm_videresending_fylke_sporsmal`
						WHERE `f_id` = '#fylke'
						ORDER BY `order` ASC", 
						array('fylke' => $this->fylke));
		$res = $sql->run();
		
		$sporsmal_key = '';
		if(!$res) {
			echo '<h3>Det er ikke laget noe videresendingsskjema for dette fylket</h3>';
		} else {
			while( $r = mysql_fetch_assoc( $res )) {
				if( $r['q_type'] == 'overskrift')
					$sporsmal_key = utf8_encode($r['q_title']);
				else 
					$sporsmal[$sporsmal_key][] = array('title' => utf8_encode($r['q_title']), 'id' => $r['q_id']);
			}
		}
		
		$pl = new monstring(get_option('pl_id'));
		$monstringer = $pl->hent_lokalmonstringer();
		
		$svar = array();
		$monstringsnavn = array();
		foreach( $monstringer as $plid ) {
			$lokal = new monstring($plid);
			$svar[$plid] = $lokal->data_videresendingsskjema();
			$monstringsnavn[$plid] = $lokal->g('pl_name');
		}
		
		return array('sporsmal' => $sporsmal, 'monstringsnavn' => $monstringsnavn, 'svar' => $svar);
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
		echo $this->html_init();

		foreach( $this->_data() as $key => $val)
			$$key = $val;
	
	#	echo '<pre>'; var_dump($sporsmal); echo '</pre>';
	
			?>
		<?php
		foreach($sporsmal as $group => $data) { ?>
			<h2><?= $group ?></h2>
			<table class="videresendingsskjema"><?php /*  style="width: <?= 120*sizeof($monstringsnavn) ?>px;"> */ ?>
				<tr class="vss_header">
					<th class="vss_sporsmal">Spørsmål</th>
					<?php
					foreach($monstringsnavn as $pl_name) { ?>
						<th class="vss_monstring"><?= $pl_name ?></th>
					<?php
					} ?>
				</tr>
			<?php
			foreach( $data as $question ) { ?>
				<tr class="vss_sporsmal_rad">
					<th class="vss_sporsmal"><?= $question['title'] ?></th>
					<?php
					foreach($monstringsnavn as $lokalid => $pl_name) { ?>
						<td class="vss_monstring"><?= $this->_styleAnswer($svar[$lokalid][$question['id']]) ?></td>
					<?php } ?>
				</tr>
			<?php
			} ?>
			</table>
			<?php
		} ?>
		<script language="javascript">
			jQuery(document).ready(function(){
				jQuery('.videresendingsskjema tr:odd').addClass('odd');
			});
		</script>		
	<?php
	}
	
	private function _styleAnswer($answer, $clean=false) {
		if($answer == 'true')
			return 'JA';
		if($answer == 'false')
			return 'NEI';
		
		if(strpos($answer, '__||__') !== false) {
			$answer = explode('__||__', $answer);
			if($clean)
				return $answer;
			return $answer[0].'<br />'
				 . contact_sms(str_replace(' ', '', $answer[1]), 'rapporten').'<br />'
				 . contact_mail($answer[2]);
		}
	}
}
















