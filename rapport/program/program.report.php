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

		if( $this->report_extended == 'tekniske_prover' ) {
			$this->navn = 'Tekniske prøver';
		} elseif( $this->report_extended == 'vurderingsskjema' ) {
			$this->navn = 'Vurderingsskjema for fagpanel';
		} else {
			$this->navn = 'Program'; 
		}

		$m = new monstring($this->pl_id);
		$ramme = $m->forestillinger('c_start',false);
		$r = $this->optGrp('h','Forestillinger');
		$this->det_finnes_ingen_monstringer = sizeof($ramme)==0;
		foreach($ramme as $con)
			$this->opt($r, 'c_'.$con['c_id'], empty($con['c_name']) ? 'Hendelse uten navn': $con['c_name']);
			
		$i = $this->optGrp('i','Info om innslaget');
		$this->opt($i, 'i_kategori', 'Kategori');
		$this->opt($i, 'i_sjanger', 'Sjanger');
		$this->opt($i, 'i_fylke', 'Fylke');
		$this->opt($i, 'i_kommune', 'Kommune');
		$this->opt($i, 'i_oppmote', 'Oppmøtetidspunkt');

		$t = $this->optGrp('t', 'Titler');
		$this->opt($t, 't_vis', 'Vis titler');
		$this->opt($t, 't_detaljer', 'Vis detaljert tittelinfo');
		
		$d = $this->optGrp('d', 'Deltakere');
		if($this->report_extended) {
			$this->opt($d, 'd_kontakt', 'Vis kontaktpersoner');
		}
		$this->opt($d, 'd_vis', 'Vis deltakere');
		$this->opt($d, 'd_alder', 'Vis alder');
		if($this->report_extended) {
			$this->opt($d, 'd_mobil', 'Vis mobilnummer');
		}
		$this->opt($d, 'd_funksjon', 'Vis rolle/funksjon/instrument');
		if( get_option('site_type') == 'land' && $this->report_extended ) {
			$this->opt($d, 'd_summering', 'Summér antall personer per hendelse');
		}
		
		if($this->report_extended) {
			$this->opt($i, 'i_tekniske', 'Tekniske behov');
			$this->opt($i, 'i_konferansier', 'Konferansiertekster (beskrivelse)');
			$this->opt($i, 'i_varighet', 'Varighet');
			$this->opt($i, 'i_playback', 'Playback-filer');
		}
		if($this->report_extended == 'tekniske_prover') {
			$k = $this->formatGrp('l', 'Kommentarbokser', 'radio');
			$this->format($k, 'k_ingen', 'Ingen kommentarbokser');
			$this->format($k, 'k_liten', 'Små kommentarbokser');
			$this->format($k, 'k_stor', 'Store kommentarbokser');
			
			$b = $this->formatGrp('b', 'Hvilke bokser vil du ha?');
			$this->format($b, 'b_generell', 'Generell kommentar');
			$this->format($b, 'b_lyd', 'Lyd');
			$this->format($b, 'b_lys', 'Lys');
			
			$p = $this->formatGrp('p', 'Sideskift');
			$this->format($p, 'p_side', 'Vis ett innslag per ark');
		}
		$p = $this->optGrp('p', 'Info om forestillingen');
		$this->opt($p, 'p_varig', 'Vis beregnet varighet');


		$m = $this->optGrp('m','Visning på skjerm');
		$this->opt($m, 'm_ukmtv', 'Vis UKM-TV');

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
		exSheetName('PROGRAM','6dc6c1');
		$concerts = $this->_program();
		$row = 1;
		if(!is_array($concerts) || sizeof($concerts)==0){
			echo exCell('A1:D1', 'Ingen forestillinger valgt');
		} else {
		
			exCell('A1', 'Hendelse', 'bold');
			exCell('B1', 'Starter', 'bold');
			exCell('C1', 'Sted', 'bold');

			exCell('D1', 'Rekkefølge', 'bold');
			exCell('E1', 'Innslag', 'bold');
			
			$col = 5;
			if($this->show('i_kategori')){
				$col++;
				exCell(i2a($col).'1', 'Kategori', 'bold');
			}
			if($this->show('i_sjanger')){
				$col++;
				exCell(i2a($col).'1', 'Sjanger', 'bold');
			}
			
			if($this->show('i_fylke')){
				$col++;
				exCell(i2a($col).'1', 'Fylke', 'bold');
			}
			if($this->show('i_kommune')){
				$col++;
				exCell(i2a($col).'1', 'Kommune', 'bold');
			}
			if($this->show('i_oppmote')) {
				$col++;
				exCell(i2a($col).'1', 'Oppmøte', 'bold');
			}
			if($this->show('i_varighet')) {
				$col++;
				exCell(i2a($col).'1', 'Varighet (sek)', 'bold');
			}
			if($this->show('i_playback')) {
				$col++;
				exCell(i2a($col).'1', 'Playback', 'bold');
			}
			if($this->show('d_kontakt')){
				$col++;
				exCell(i2a($col).'1', 'Kontaktperson', 'bold');
			}
			if($this->show('t_vis')){
				$col++;
				exCell(i2a($col).'1', 'Titler', 'bold');
			}
			if($this->show('d_vis')){
				$col++;
				exCell(i2a($col).'1', 'Deltakere', 'bold');
			}
			if($this->show('i_konferansier')){
				$col++;
				exCell(i2a($col).'1', 'Konferansiertekster (beskrivelse)', 'bold');
			}

			if($this->show('i_tekniske')){
				$col++;
				exCell(i2a($col).'1', 'Tekniske behov','bold');
			}

		
			foreach($concerts as $c){
				$program = $c->innslag();
				$order = 0;
				$oppmote = (int) $c->g('c_start') - ((int) $c->g('c_before')*60);
				$oppmote = $oppmote - ((int) $c->g('c_delay')*60);
				foreach($program as $inn) {
					$row++;
					$order++;
					$oppmote += ((int) $c->g('c_delay')*60);

					$i = new innslag($inn['b_id']);
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$i->loadGEO();
					
					exCell('A'.$row, $c->g('c_name'));
					exCell('B'.$row, $c->starter());
					exCell('C'.$row, $c->g('c_place'));

					exCell('D'.$row, $order);
					exCell('E'.$row, $i->g('b_name'));
					
					$col = 5;
					$i_kategori = $i->g('kategori');
					if(empty($i_kategori))
						$i_kategori = $i->g('bt_name');
						
					if($this->show('i_kategori')){
						$col++;
						exCell(i2a($col).$row, $i_kategori);
					}
					if($this->show('i_sjanger')){
						$col++;
						exCell(i2a($col).$row, $i->g('b_sjanger'));
					}
					
					if($this->show('i_fylke')){
						$col++;
						exCell(i2a($col).$row, $i->g('fylke'));
					}
					if($this->show('i_kommune')){
						$col++;
						exCell(i2a($col).$row, $i->g('kommune'));
					}
					if($this->show('i_oppmote')) {
						$col++;
						exCell(i2a($col).$row, date('H:i', $oppmote));
					}
					if($this->show('i_varighet')) {
						$col++;
						exCell(i2a($col).$row, $i->varighet(get_option('pl_id')));
					}
					if($this->show('i_playback')) {
						$col++;
						exCell(i2a($col).$row, $i->playbackToString());
					}

					if($this->show('d_kontakt')){
						$col++;
						$d = new person($i->g('b_contact'));
						exCell(i2a($col).$row, $d->g('name') 
											.' ('. $d->g('p_phone')
											.' - '. $d->g('p_email')
											.')'
										);
					}
					
					
					$deltakertext = $titteltext = '';

					if($this->show('t_vis') && !$i->tittellos()){
						$titler = $i->titler($this->pl_id);
						$counter = 0;
						foreach($titler as $t) {
							$counter++;
							$titteltext .= $t->g('tittel')
								.($this->show('t_detaljer')
									? ' ('
										. $t->g('detaljer')
										. (($t->g('detaljer') && ($t->selvlaget || $t->instrumental) ? ', ' : ''))
										. ($t->selvlaget ? 'selvlaget' : '')
										. (($t->selvlaget && $t->instrumental) ? ', ' : '')
										. ($t->instrumental ? 'instrumental' : '')
										.')'
									: ''
									)
								.($counter < sizeof($titler) ? ', ' : '')
								;
						}
						$col++;
						exCell(i2a($col).$row, $titteltext);
					}

					if($this->show('d_vis') ) {#&& !$i->tittellos() ) {
						$counter = 0;
						if(get_option('site_type')!=='kommune')
							$i->videresendte($this->pl_id);

						$deltakere = $i->personer();
						foreach($deltakere as $delt) {
							$counter++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							$deltakertext .= $d->g('name')
								.($this->show('d_alder')
									? ' - '. $d->alder().' år'
									: ''
									)
								.($this->show('d_funksjon')
									? ' ('. $d->g('instrument').')'
									: ''
									)
								.($this->show('d_mobil')
									? ' - mobil: '.$d->g('p_phone')
									: '')
								.($counter < sizeof($deltakere) ? ', ' : '')
								;
						}
						$col++;
						exCell(i2a($col).$row, $deltakertext);
					}
					
					if($this->show('i_konferansier')){
						$col++;
						exCell(i2a($col).$row, $i->g('b_description') .' '. $i->g('td_konferansier'));
					}

					if($this->show('i_tekniske')){
						$col++;
						exCell(i2a($col).$row, $i->g('td_demand'));
					}

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
		$section = $this->word_init();
		$concerts = $this->_program();

		if(!is_array($concerts) || sizeof($concerts)==0){
			echo woText($section, 'Ingen forestillinger valgt', 'grp');
		} else {
			foreach($concerts as $con){
				$sted = $con->g('c_place');
				if(!empty($sted))
					$sted = ' - '. $sted;
				woText($section, $con->g('c_name'), 'grp');
				woText($section, $con->starter(). $sted, 'h2');
				if($this->show('p_varig'))
				woText($section, 'Beregnet varighet: '. $con->tid());
				
				$program = $con->innslag();
				$order = 0;
				$oppmote = (int) $con->g('c_start') - ((int) $con->g('c_before')*60);
				$oppmote = $oppmote - ((int) $con->g('c_delay')*60);

				foreach($program as $inn) {
					$order++;
					$oppmote += ((int) $con->g('c_delay')*60);

					$i = new innslag($inn['b_id']);
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$i->loadGEO();
					
					woText($section, $order .'. '. $i->g('b_name'), 'h3');
					$katogsjan = $i->g('kategori_og_sjanger');
					if(empty($katogsjan))
						$katogsjan = $i->g('bt_name');
						
					if($this->show('i_kategori')&&$this->show('i_sjanger'))
						woText($section, '('. $katogsjan.')');
					elseif($this->show('i_kategori'))
						woText($section, '('. $i->g('kategori').')');
					elseif($this->show('i_sjanger'))
						woText($section, '('. $i->g('b_sjanger').')');
					
					
					if($this->show('i_fylke')&&$this->show('i_kommune'))
						woText($section, $i->g('fylke').' - '. $i->g('kommune'));
					elseif($this->show('i_fylke'))
						woText($section, $i->g('fylke'));
					elseif($this->show('i_kommune'))
						woText($section, $i->g('kommune'));
					
					if($this->show('i_oppmote')) {
						woText($section, 'Oppmøte: ', 'bold');
						woText($section, 'kl. '. date('H:i', $oppmote));
					}
					if($this->show('i_varighet')) {
						woText($section, 'Varighet: ', 'bold');
						woText($section, $i->tid(get_option('pl_id')));
					}
					if($this->show('i_playback')) {
						woText($section, 'Playback: ', 'bold');
						woText($section, $i->playbackToString());
					}
					if($this->show('d_kontakt')){
						$d = new person($i->g('b_contact'));
						woText($section, 'Kontaktperson:', 'bold');
						woText($section, $d->g('name') 
										.' - mobil: '. $d->g('p_phone')
										.' - e-post: '. $d->g('p_email')
										);
					}
					$deltakertext = $titteltext = '';

					if($this->show('t_vis') && !$i->tittellos()){
						woText($section, 'Titler: ', 'bold');
						
						$titler = $i->titler($this->pl_id);
						$counter = 0;
						foreach($titler as $t) {
							$counter++;
							$titteltext .= $t->g('tittel')
								.($this->show('t_detaljer')
									? 	' ('
									  . $t->g('detaljer')
									  . ($i->g('bt_id') == 3 ? ' - '.$t->g('beskrivelse') : '')
									  . (($t->g('detaljer') && ($t->selvlaget || $t->instrumental) ? ', ' : ''))
									  . ($t->selvlaget ? 'selvlaget' : '')
									  . (($t->selvlaget && $t->instrumental) ? ', ' : '')
									  . ($t->instrumental ? 'instrumental' : '')
									  . ')'								
									: ''
									)
								.($counter < sizeof($titler) ? ', ' : '')
								;
						}
						woText($section, $titteltext);
					}

					// Hvis program og innslaget har titler, ELLER hvis extended_report og vis deltakere (uavhengig av titler)
					if( ($this->show('d_vis') && !$i->tittellos() ) || ($this->show('d_vis') && $this->report_extended ) ){
						woText($section, 'Personer: ', 'bold');
						
						if(get_option('site_type')!=='kommune')
							$i->videresendte($this->pl_id);

						$deltakere = $i->personer();
						$counter = 0;
						foreach($deltakere as $delt) {
							$counter++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							$deltakertext .= $d->g('name')
								.($this->show('d_alder')
									? ' - '. $d->alder().' år'
									: ''
									)
								.($this->show('d_funksjon')
									? ' ('. $d->g('instrument').')'
									: ''
									)
								.($this->report_extended && $this->show('d_mobil')
									? ' - mobil: '.$d->g('p_phone')
									: '')
								.($counter < sizeof($deltakere) ? ', ' : '')
								;
						}
						woText($section, $deltakertext);
					}
					
					
					
					///////
					
					
					if($this->show('i_konferansier')){
						woText($section, 'Konferansiertekster: ', 'bold');
						woText($section, $i->g('b_description') .' '. $i->g('td_konferansier'));
					}

					if($this->show('i_tekniske')){
						woText($section, 'Tekniske behov: ', 'bold');
						woText($section, $i->g('td_demand'));
					}

					if($this->showFormat('b_generell')||$this->showFormat('b_lyd')||$this->showFormat('b_lys')){
						$tab = $section->addTable(array('align'=>'center'));
						
						if($this->showFormat('k_liten'))
							$tab->addRow(1500);
						elseif($this->showFormat('k_stor'))
							$tab->addRow(3000);
						else
							$tab->addRow();
						
						$bokser = 0;
						if($this->showFormat('b_generell'))
							$bokser++;
						if($this->showFormat('b_lyd'))
							$bokser++;
						if($this->showFormat('b_lys'))
							$bokser++;
						
						$box = array('borderTopSize'=>9, 'borderTopColor'=>'000000',
									 'borderRightSize'=>9, 'borderRightColor'=>'000000',
									 'borderBottomSize'=>9, 'borderBottomColor'=>'000000',
									 'borderLeftSize'=>9, 'borderLeftColor'=>'000000',
									 );

							
						if($this->showFormat('b_generell')) {
							$c = $tab->addCell((11000/$bokser), $box);
							woText($c, 'Generell kommentar','h1');
						}
						if($this->showFormat('b_lyd')) {
							$c = $tab->addCell((11000/$bokser), $box);
							woText($c, 'LYD-kommentarer','h1');
						}
						if($this->showFormat('b_lys')){
							$c = $tab->addCell((11000/$bokser), $box);
							woText($c, 'LYS-kommentarer','h1');
						}
					}
					
					if($this->report_extended == 'vurderingsskjema') {
						$jurybox = array('borderTopSize'=>9, 'borderTopColor'=>'000000',
									 'borderRightSize'=>9, 'borderRightColor'=>'000000',
									 'borderBottomSize'=>9, 'borderBottomColor'=>'000000',
									 'borderLeftSize'=>9, 'borderLeftColor'=>'000000',
									 );
						woText($section, 'Vurderingsskjema: ', 'h2');

						$tab = $section->addTable(array('align'=>'center'));
						
						$tab->addRow(2000);
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Originalitet','h2');
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Kreativitet','h2');

						$tab->addRow(2000);
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Publikumskontakt / formidling','h2');
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Tekniske ferdigheter','h2');

						$tab->addRow(2000);
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Scenefremtreden / fremføring','h2');
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Kvalitet i forhold til forutsetninger','h2');

						woText($section, 'Basert på ovenstående vurderinger gjør jeg følgende midlertidig vurdering','h3');
						$tab = $section->addTable(array('align'=>'center'));
						$tab->addRow(2000);
						$c = $tab->addCell(11000, $jurybox);
							woText($c, 'Vurdering','h2');

						$section->addPageBreak();

					}
					
					if($this->report_extended == 'tekniske_prover' && $this->showFormat('p_side') ) {
						$section->addPageBreak();
					}

					
					///////
					
					
				}
			}
		}
		return $this->woWrite();
	}

	/**
	 * Generate CSV-file for use in automated graphics-generation.
	 * By @asgeirsh, 26.03.2017.
	 *
	 * Properly formatted CSV is hard to export from Excel (requires manual modification).
	 * This module provides CSV-export of user data in a nice, usable format.
	 * Produces files directly compatible with Adobe software, unlike Excel (UTF-8 must still be manually selected on import).
	 *
	 * Does not allow dynamic data selection - will only use Innslag, Kommune and Titler at the moment.
	 * Can generate lists for more than one concert at a time.
	 *
	 * @access public
	 * @return String download-URL
	 */
	public function generateCSV() {
		$concerts = $this->_program();
		$data = '';
		// TODO: Insert a selector for OS-version... 
		// \r is OS 7, \r\n is Windows, \n is Unix / OS X. Windows can transparently read/convert \n, apparently.
		$eol = "\n";
		
		if( !is_array($concerts) || sizeof($concerts)==0 ) {
			echo 'Ingen forestillinger valgt';
			return;
		}

		// Export a header-line
		$data = 'Innslag,Kommune,Titler'.$eol;

		// Add data for each concert
		foreach( $concerts as $con ) {
			// Innslag:
			$program = $con->innslag();
			foreach ( $program as $i ) {
				$innslag = new innslag( $i['b_id'] );
				$titler = $innslag->titler( get_option('pl_id') );	

				// Hvis innslaget har en eller flere titler genererer vi en linje for hver låt
				foreach ( $titler as $tittel ) {
					$data .= '"'.$innslag->g('b_name').'",';
					$data .= '"'.$innslag->g('b_kommune').'",';
					$data .= '"'.$tittel->g('tittel').'"'.$eol;	
				}
				// Edge-case: Hvis innslaget ikke har noen titler - legg til "Ukjent tittel"
				if ( 0 == count($titler) ) {
					$data .= '"'.$innslag->g('b_name').'",';
					$data .= '"'.$innslag->g('b_kommune').'",';
					$data .= '"Ukjent tittel"'.$eol;		
				}
				
			}
		} // End loops

		return $this->csv_write($data);
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
		$concerts = $this->_program();
		echo $this->html_init();

		if(!is_array($concerts) || sizeof($concerts)==0){
			echo '<h3>Ingen forestillinger valgt</h3>';
			if($this->det_finnes_ingen_monstringer)
				echo '<div class="error"><strong>For at denne rapporten skal fungere må du sette et program for mønstringen din.</strong> <br />Dette gjør du ved å velge program i menyen til venstre</div>';
		} else {
			foreach($concerts as $c){
				$totalt_antall_personer = 0;

				$sted = $c->g('c_place');
				if(!empty($sted))
					$sted = ' - '. $sted;
			 ?>
			 	<div class="clear"></div>
				<h2 id="program-header"><?= $c->g('c_name')?></h2>
				<div class="program-data">
					<div class="tid"><?= $c->starter()?></div>
					<div class="sted"><?= $sted?></div>
				</div>
				<?php if($this->show('p_varig')) { ?>
				<div class="program-varighet">Beregnet varighet: <?= $c->tid() ?></div>
				<?php } ?>
				
				
				<ul class="program">
				<?php
				$oppmote = (int) $c->g('c_start') - ((int) $c->g('c_before')*60);
				$oppmote = $oppmote - ((int) $c->g('c_delay')*60);
				$program = $c->innslag();
				$order = 0;
				foreach($program as $inn) {
					$order++;
					$i = new innslag($inn['b_id']);
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$i->loadGEO();
					$oppmote += ((int) $c->g('c_delay')*60);
					$katogsjan = $i->g('kategori_og_sjanger');
					if(empty($katogsjan))
						$katogsjan = $i->g('bt_name');
					echo '<li>'
						.	'<div class="order">'. $order .'.</div>'
						.	'<div class="name">'. $i->g('b_name') .'</div>'
						.	($this->show('i_kategori')&&$this->show('i_sjanger')
								? '<div class="katogsjan">('. $katogsjan .')</div>'
								: ($this->show('i_kategori')
									? '<div class="katogsjan">('. $i->g('kategori').')</div>'
									: ($this->show('i_sjanger')
										? '<div class="katogsjan">('. $i->g('b_sjanger').')</div>'
										: ''
										)
									)
								)
						.	($this->show('i_fylke')&&$this->show('i_kommune')
								? '<div class="geografi">'. $i->g('fylke').' - '. $i->g('kommune') .'</div>'
								: ($this->show('i_fylke')
									? '<div class="geografi">'. $i->g('fylke').'</div>'
									: ($this->show('i_kommune')
										? '<div class="geografi">'. $i->g('kommune').'</div>'
										: ''
										)
									)
								)
						.	($this->show('i_oppmote') ? '<div class="titler"><div class="titler-label">Oppmøte: </div>kl. '. date('H:i', $oppmote) .'</div>' : '')
						.	($this->show('i_varighet') ? '<div class="titler"><div class="titler-label">Varighet: </div>'.$i->tid(get_option('pl_id')) .'</div>' : '')

						;
					if($this->show('d_kontakt')){
						$d = new person($i->g('b_contact'));
						echo '<div class="kontaktperson">'
							.'<div class="label">Kontaktperson:</div>'
							.''.$d->g('name').'</div>'
							.'<div class="detaljer"> - mobil: <span class="UKMSMS">'. $d->g('p_phone') .'</span></div>'
							.'<div class="detaljer"> - e-post: <a href="mailto:'.$d->g('p_email').'" class="UKMMAIL">'. $d->g('p_email').'</a></div>'
							.'</div>'
							;
					}
					if( $this->show('i_playback')) {
						echo #'<div class="clear clearfix clear-fix"></div>'
							'<div class="kontaktperson">'
							.'<strong>Playback: </strong>' . $i->playbackToString()
							.'</div>';
					}
					
					if($this->show('t_vis') && !$i->tittellos()){
						echo '<div class="titler">'
							.'<div class="titler-label">Titler:</div>';
						$titler = $i->titler($this->pl_id);
						$counter = 0;
						foreach($titler as $t) {
							$counter++;
							echo '<div class="name">'.$t->g('tittel').'</div>'
								.($this->show('t_detaljer')
									? '<div class="detaljer">('. $t->g('detaljer')									  
										. ($i->g('bt_id') == 3 ? ' - '.$t->g('beskrivelse') : '')
										. (($t->g('detaljer') && ($t->selvlaget || $t->instrumental) ? ', ' : ''))
										. ($t->selvlaget ? 'selvlaget' : '')
										. (($t->selvlaget && $t->instrumental) ? ', ' : '')
										. ($t->instrumental ? 'instrumental' : '')
										.')</div>' 
									: ''
								)
								.($counter < sizeof($titler) ? '<div class="separator">,</div>' : '');
						}
						echo '</div>';
					}

					// Hvis program og innslaget har titler, ELLER hvis extended_report og vis deltakere (uavhengig av titler)
					if( ($this->show('d_vis') && !$i->tittellos() ) || ($this->show('d_vis') && $this->report_extended ) ){
						echo '<div class="deltakere">'
							.'<div class="deltakere-label">Personer:</div>';
						if(get_option('site_type')!=='kommune')
							$i->videresendte($this->pl_id);
						$deltakere = $i->personer();
						$counter = 0;
						foreach($deltakere as $delt) {
							$totalt_antall_personer++;
							$counter++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							echo '<div class="name">'.$d->g('name').'</div>'
								.($this->show('d_alder')
									? '<div class="alder"> - '. $d->alder().' år</div>'
									: ''
									)
								.($this->show('d_funksjon')
									? '<div class="detaljer">('. $d->g('instrument').')</div>'
									: ''
									)
								.($this->report_extended && $this->show('d_mobil')
									? '<div class="detaljer">- mobil: <span class="UKMSMS">'. $d->g('p_phone') .'</span></div>'
									: '')
								.($counter < sizeof($deltakere) ? '<div class="separator">,</div>' : '')

								;
						}
						echo '</div>';
					}
						
					if($this->show('i_konferansier')){
						echo '<div class="deltakere">'
							.'<div class="deltakere-label">Konferansiertekster:</div>'
							.$i->g('b_description')
							.$i->g('td_konferansier')
							.'</div>';
					}

					if($this->show('i_tekniske')){
						echo '<div class="deltakere">'
							.'<div class="deltakere-label">Tekniske behov:</div>'
							.$i->g('td_demand')
							.'</div>';
					}
					
					if($this->report_extended == 'vurderingsskjema') {
						echo '<div class="juryskjema">'
								.'<div class="deltakere-label">Vurderingsskjema</div>'

								.'<div class="left">Originalitet</div>'
								.'<div class="right">Kreativitet</div>'
								.'<div class="clear-fix"></div>'
	
								.'<div class="left">Publikumskontakt / formidling</div>'
								.'<div class="right">Tekniske ferdigheter</div>'
								.'<div class="clear-fix"></div>'
								
								.'<div class="left">Scenefremtreden / fremføring</div>'
								.'<div class="right">Kvalitet i forhold til forutsetninger</div>'
								.'<div class="clear-fix"></div>'
								
								.'<h3>Basert på ovenstående vurderinger gjør jeg følgende midlertidig vurdering</h3>'
								.'<div class="wide">Vurdering</div>'
								.'<div class="clear-fix"></div>'
	
							.'</div>'
							.'<div style="clear:both;"></div>'
							.'<div style="page-break-after:always;"></div>';
						
					}


					if($this->showFormat('b_generell')||$this->showFormat('b_lyd')||$this->showFormat('b_lys')){
						if($this->showFormat('k_liten'))
							$class = 'liten';
						elseif($this->showFormat('k_stor'))
							$class = 'stor';
						else
							$class = 'ingen';
						
						$bokser = 0;
						if($this->showFormat('b_generell'))
							$bokser++;
						if($this->showFormat('b_lyd'))
							$bokser++;
						if($this->showFormat('b_lys'))
							$bokser++;
							
						switch($bokser) {
							case 3:
								$class .= ' tredjedel';
								break;
							case 2:
								$class .= ' halv';
								break;
							default:
								$class .= ' hel';
								break;
						}
							
						echo '<div class="bokser">';
						
						if($this->showFormat('b_generell'))
							echo '<div class="'.$class.'">Generell kommentar</div>';
						if($this->showFormat('b_lyd'))
							echo '<div class="'.$class.'">LYD-kommentarer</div>';
						if($this->showFormat('b_lys'))
							echo '<div class="'.$class.'">LYS-kommentarer</div>';
						
						echo '</div>';
					}

					### VIS UKM-TV
					if( $this->show('m_ukmtv') ) {
						$media = $i->related_items();
						echo '<div class="titler">'
							.'<div class="titler-label">UKM-TV:</div>';
						$ukmtv_count = 0;
						if( isset( $media['tv'] ) && sizeof( $media['tv'] ) > 0) {
							foreach( $media['tv'] as $tv_id => $tv ) {
								$ukmtv_count++;
								echo '<div class="UKMTV clickable">'
								   . '	<div class="image"><img src="'. $tv->image_url .'" style="max-width:100%;" /></div>'
								   . '	<div class="embedcontainer" style="display:none;" data-framesource="'. $tv->embed_url .'"></div>'
								   . '</div>';
							}
						}
						if( $ukmtv_count == 0 ) {
							echo 'Ingen filmer lastet opp';
						}
						echo '</div>';
					}
					
					if( $this->report_extended == 'tekniske_prover' && $this->showFormat('p_side') ) {
						echo '<div style="page-break-after: always;"></div>';
					}
									
					echo '<div class="clear"></div>';

					echo '</li>';
				} ?>
				</ul>
				<div class="clear"></div>
				<?php
					if( $this->report_extended == 'tekniske_prover' && $this->show('d_summering') ) {
						echo '<h4 align="right">Antall personer i '. $c->g('c_name') .': '. $totalt_antall_personer .'</h4>';
					}
			}
		}
	}
		/**
	 * generate function
	 * 
	 * Genererer selve rapporten i HTML-visning
	 *
	 * @access public
	 * @return void
	 */	
	 public function _program(){
	 	require_once('UKM/forestilling.class.php');
		$m = new monstring($this->pl_id);
		$ramme = $m->forestillinger('c_start',false);

		foreach($ramme as $con){
			if(!$this->show('c_'.$con['c_id']))
				continue;
			
			$concerts[] = new forestilling($con['c_id']);
		}
		return $concerts;
	 }
}
?>