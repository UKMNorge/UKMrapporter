<?php
require_once('UKM/innslag.class.php');
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
		
		$this->navn = 'Inn- og utlevering';


		$i = $this->optGrp('i','Typer innslag');
		$this->opt($i, 'i_utstilling', 'Utstilling');
		$this->opt($i, 'i_film', 'Film');
		
		$this->_postConstruct();	
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
		//$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		$objekter = $this->_objektene();
		$color = '6dc6c1';
		
		$loop = 0;
		if(!is_array($objekter) || sizeof($objekter)==0){
			exCell('A1:D1', 'Fant ingen slike innslag på din mønstring');
		} else {
			foreach($objekter as $type => $titler){
				$loop++;
				exSheetName($type, $color);
				$row = 1;
				exCell('A'.$row, 'Inn', 'bold');
				exCell('B'.$row, 'Ut', 'bold');
				exCell('C'.$row, 'Navn på '.($type == 'film' ? 'film' : 'kunstverk'), 'bold');
				exCell('D'.$row, 'Navn på innslag/gruppe', 'bold');
				exCell('E'.$row, 'Detaljer', 'bold');
				exCell('F'.$row, 'Kommune', 'bold');
				exCell('G'.$row, 'Signatur', 'bold');

				foreach($titler as $tittel => $objektarray) {
					foreach($objektarray as $tittelen) { 
						$inn = new innslag($tittelen->g('b_id'));
						$inn->loadGEO();
						$row++;
						
						exCell('A'.$row, '');
						exCell('B'.$row, '');
						exCell('C'.$row, $tittelen->g('tittel'));
						exCell('D'.$row, $inn->g('b_name'));
						exCell('E'.$row, $inn->info['kommune']);
						exCell('F'.$row, $tittelen->g('detaljer'));
						exCell('G'.$row, '');
					}
				}
				if($loop < sizeof($objekter)){
					$objPHPExcel->createSheet(1);
					$objPHPExcel->setActiveSheetIndex(1);
					$color = 'f69a9b';
				}
			}
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
		global $PHPWord;
		$section = $this->word_init('landscape');

		$objekter = $this->_objektene();

		$col_inn	 = 500;
		$col_navn	 = 4000;
		$col_innslag = 3000;
		$col_detaljer= 3000;
		$col_sign 	 = 2000;

/* 		$cellmargin = 180; */

		$box = array('borderTopSize'=>9, 'borderTopColor'=>'000000',
					 'borderRightSize'=>9, 'borderRightColor'=>'000000',
					 'borderBottomSize'=>9, 'borderBottomColor'=>'000000',
					 'borderLeftSize'=>9, 'borderLeftColor'=>'000000',
					 );
		$loop = 0;
		if(!is_array($objekter) || sizeof($objekter)==0){
			woText($section, 'Fant ingen slike innslag på din mønstring','bold');
		} else {
			foreach($objekter as $type => $titler){
				$loop++;
				woText($section, 'Inn- og utlevering av '. $type, 'grp');
				$tab = $section->addTable();
				$tab->addRow();
	
				$c = $tab->addCell($col_inn);
				woText($c, 'Inn','bold');
				$c = $tab->addCell($col_inn/2);
				$c = $tab->addCell($col_inn);
				woText($c, 'Ut','bold');
				$c = $tab->addCell($col_inn/2);
				$c = $tab->addCell($col_navn);
				woText($c, 'Navn på '.($type == 'film' ? 'film' : 'kunstverk'),'bold');
				$c = $tab->addCell($col_innslag);
				woText($c, 'Navn på innslag/gruppe','bold');
				$c = $tab->addCell($col_detaljer);
				woText($c, 'Detaljer','bold');
				$c = $tab->addCell($col_detaljer);
				woText($c, 'Kommune','bold');
				$c = $tab->addCell($col_sign);
				woText($c, 'Signatur','bold');

				foreach($titler as $tittel => $objektarray) {
					foreach($objektarray as $tittelen) { 
						$inn = new innslag($tittelen->g('b_id'));
						$tab->addRow(500);
			
						$c = $tab->addCell($col_inn, $box);
						woText($c, '');
						$c = $tab->addCell($col_inn/2);
						$c = $tab->addCell($col_inn, $box);
						woText($c, '');
						$c = $tab->addCell($col_inn/2);
						$c = $tab->addCell($col_navn);
						woText($c, $tittelen->g('tittel'));
						$c = $tab->addCell($col_innslag);
						woText($c, $inn->g('b_name'));
						$c = $tab->addCell($col_detaljer);
						woText($c, $tittelen->g('detaljer'));

						$inn->loadGEO(); 
						$c = $tab->addCell($col_detaljer);
						woText($c, $inn->info['kommune']);

						$c = $tab->addCell($col_sign, array('borderBottomSize'=>9, 'borderBottomColor'=>'000000'));
						woText($c, '');
					}
				}
				if($loop < sizeof($objekter))
					$section->addPageBreak();
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
		$objekter = $this->_objektene();
		
		echo $this->html_init('Inn- og utlevering');
	#	echo '<pre>'; var_dump($objekter); echo '</pre>';

		if(!is_array($objekter) || sizeof($objekter)==0){
			echo '<strong>Fant ingen slike innslag på din mønstring</strong>';
		} else {
			foreach($objekter as $type => $titler){ ?>
				<h3 class="levering-header">Inn- og utlevering av <?= $type ?></h3>
				<ul class="levering">
					<li class="header">
						<div class="inn">Inn</div>
						<div class="ut">Ut</div>
						<div class="navn">Navn på <?= $type == 'film' ? 'film' : 'kunstverk' ?></div>
						<div class="inn_navn">Navn på innslag / gruppe</div>
						<div class="detaljer">Detaljer</div>
						<div class="detaljer">Kommune</div>
						<div class="sign">Signatur</div>
					</li>
				<?php
				foreach($titler as $tittel => $objektarray) {
					foreach($objektarray as $tittelen) { 
						$inn = new innslag($tittelen->g('b_id'));
					?>
					<li class="item">
						<div class="inn"></div>
						<div class="ut"></div>
						<div class="navn"><?= $tittelen->g('tittel')?></div>
						<div class="inn_navn"><?= $inn->g('b_name')?></div>
						<div class="detaljer"><?= $tittelen->g('detaljer')?></div>
						<div class="detaljer"><?php $inn->loadGEO(); echo $inn->info['kommune']; ?></div>
						<div class="sign"></div>
					</li>
				<?php
					}
				}
				 ?>
				</ul>
				<div style="page-break-after:always;"></div>
				<?php
			}
		}
	}
	
	/**
	 * _innslagene function
	 * 
	 * Lager to lister over titler på mønstringen (kunst og film)
	 *
	 * @access public
	 * @return void
	 */	
	private function _objektene() {
		$innslagene = $this->m->innslag();
		foreach($innslagene as $innslag) {
			if($innslag['bt_id'] !=2 && $innslag['bt_id'] != 3)
				continue;
			
			if(!$this->show('i_film') && $innslag['bt_id'] == 2)
				continue;

			if(!$this->show('i_utstilling') && $innslag['bt_id'] == 3)
				continue;
			
			$inn = new innslag($innslag['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$titler = $inn->titler($this->pl_id);
			foreach($titler as $tittel){
				$rapport[strtolower($inn->g('bt_name'))][$tittel->g('tittel')][] = $tittel;
			}
		}
		$this->_deep_ksort($rapport);
		return $rapport;
	}
}	
?>