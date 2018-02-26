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

		$g = $this->optGrp('v','Visning');
		$this->opt($g, 'v_status', 'Vis status innlevering');
		$this->opt($g, 'v_levert', 'Kun vis svar fra de som har levert skjema');
	}

	public function generateExcel() {
		
		global $objPHPExcel;
		//$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		
		exSheetName('VIDERESENDINGSSKJEMA');
		foreach( $this->_data() as $key => $val) {
			$$key = $val;
		}
		
		$ark = 1;
		$col = $row = 1;


		$objPHPExcel->createSheet( $ark );
		$objPHPExcel->setActiveSheetIndex( $ark);
		$color = 'f69a9b';
		exSheetName('AVSNITT_'. $ark, $color);

					
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

		foreach( $this->_data() as $key => $val) {
			$$key = $val;
		}

		foreach($sporsmal as $group => $data) {
			woText($section, $group, 'h1');

			$tab = $section->addTable(array('align'=>'center'));
			$tab->addRow();

			$colsize = round( 17000 / sizeof( $data ));

			$c = $tab->addCell($colsize);
			woText($c, 'Mønstring', 'bold');
			foreach( $data as $question ) {
				$c = $tab->addCell($colsize);
				woText( $c, $question['title'], 'bold' );
			}
			foreach( $monstringsnavn as $pl_id => $pl_name ) {
				// Hopp over de som ikke har levert hvis valgt
				if( $this->show('v_levert') && sizeof( $svar[ $pl_id ] ) == 0 ) {
					continue;
				}
				
				// NY RAD
				$tab->addRow();
				
				// Mønstringsnavn
				$c = $tab->addCell($colsize);
				woText( $c,  $pl_name );
				// Alle spørsmål
				foreach( $data as $question ) {
					$c = $tab->addCell($colsize);
					
					$current_svar = $this->_styleAnswer( $svar[ $pl_id ][ $question['id'] ], true );
					if( is_array( $current_svar ) ) {
						$current_svar = implode(' ', $current_svar );
					}
					woText( $c, $current_svar );
				}
			}
			
			$section->addPageBreak();
		}
		return $this->woWrite();
	}

	
	private function _data() {
		# Removed 01.03 because we don't use get_option('fylke') no more.
		#$this->fylke = get_option('fylke');
		$m = new monstring( get_option('pl_id') );
		$this->fylke = $m->get('fylke_id');
		
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
				 . '<span class="UKMSMS">'. str_replace(' ', '', $answer[1]) .'</span><br />'
				 . '<a href="mailto:'.$answer[2].'" class="UKMMAIL">'. $answer[2] .'</a>';
		}
		return $answer;
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

		/**
		 * SETTER:
		 * $sporsmal
		 * $monstringsnavn
		 * $svar
		**/
		foreach( $this->_data() as $key => $val) {
			$$key = $val;
		}
		
		
		
		if($this->show('v_status')) {
			?>
			<h2>Status innlevering</h2>
			<table class="videresendingsskjema">
				<thead>
					<tr>
						<th>Mønstringsnavn</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach( $monstringsnavn as $pl_id => $pl_name ) {
						?>
						<tr class="<?php echo sizeof( $svar[ $pl_id ] ) == 0 ? 'alert-danger' : 'alert-success' ?>">
							<td><?php echo $pl_name ?></td>
							<td><?php echo sizeof( $svar[ $pl_id ] ) == 0 ? 'Ikke levert' : 'Levert' ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
		}
		
		if(!$sporsmal) {
			echo '<h3>Det er ikke laget noe videresendingsskjema for dette fylket.</h3>';
			echo '<p>Velg "Lag skjema for videresending" i venstremenyen.</p>';
		}
		else {
			foreach($sporsmal as $group => $data) { ?>
				<h2><?= $group ?></h2>
				<table class="videresendingsskjema"><?php /*  style="width: <?= 120*sizeof($monstringsnavn) ?>px;"> */ ?>
					<tr class="vss_sporsmal_rad">
						<th>Mønstring</th>
						<?php
						foreach( $data as $question ) {
							echo '<th>'. $question['title'] .'</th>';
						}
						?>
					</tr>
					<?php
					foreach( $monstringsnavn as $pl_id => $pl_name ) {
						// Hopp over de som ikke har levert hvis valgt
						if( $this->show('v_levert') && sizeof( $svar[ $pl_id ] ) == 0 ) {
							continue;
						}
						?>
						<tr>
							<td><?php echo $pl_name ?></td>
							<?php
							foreach( $data as $question ) {
							?>
								<td>
									<?php echo $this->_styleAnswer($svar[ $pl_id ][$question['id']]) ?>
								</td>
							<?php
							}
							?>
						</tr>
					<?php
					}
					?>
				</table>
				<?php
			} 
		}?>
		<script language="javascript">
			jQuery(document).ready(function(){
				jQuery('.videresendingsskjema tr:odd').addClass('odd');
			});
		</script>		
	<?php
	}
}
