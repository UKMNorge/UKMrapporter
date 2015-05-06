<?php
require_once('UKM/innslag.class.php');
require_once('UKM/inc/toolkit.inc.php');
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
		$g = $this->optGrp('i','Info om innslaget');
		$this->opt($g, 'i_katogsjan', 'Vis kategori og sjanger');
		$this->opt($g, 'i_varig', 'Vis innslagets varighet');
		$this->opt($g, 'i_fylke', 'Vis fylke');
		$this->opt($g, 'i_kommune', 'Vis kommune');
		$this->opt($g, 'i_tekn', 'Vis tekniske behov');
		$this->opt($g, 'i_konf', 'Vis konferansiertekster');

		$g = $this->optGrp('p','Info om enkeltpersoner');
		$this->opt($g, 'p_kontaktp', 'Vis kontaktperson');
		$this->opt($g, 'p_vis', 'Vis alle deltakere');
		$this->opt($g, 'p_instrument', 'Vis instrument');
		$this->opt($g, 'p_alder', 'Vis alder');
		$this->opt($g, 'p_mobil', 'Vis mobilnummer');
		$this->opt($g, 'p_epost', 'Vis e-post (hvis oppgitt)');


		$g = $this->optGrp('t','Info om titler i innslaget');
		$this->opt($g, 't_vis', 'Vis titler');
		$this->opt($g, 't_varig', 'Vis varighet');
		$this->opt($g, 't_detaljer', 'Vis detaljert tittel-info');

		$m = $this->optGrp('m','Visning på skjerm');
		$this->opt($m, 'm_ukmtv', 'Vis UKM-TV');

		$g = $this->formatGrp('g', 'Gruppering', 'radio');
		$this->format($g, 'g_ingen', 'Alfabetisk sortert');
		$this->format($g, 'g_type', 'Type og navn<br />'
								.   ' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b> type innslag<br />'
								.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter navn');
		if(get_option('site_type')!='land') {
			$this->format($g,  'g_type_kommune', 'Type, kommune og navn<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b> type<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter kommune og navn');
			$this->format($g,  'g_kommune', 'Kommune og navn<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b> kommune<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter navn');
			$this->format($g,  'g_kommune_type', 'Kommune, type og navn<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b> kommune<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter type og navn');
		} else {
			$this->format($g,  'g_type_fylke', 'Type, fylke og navn<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b> type<br />'
										.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter fylke og navn');
			$this->format($g, 'g_fylke', 'Fylke og navn<br />'
									.	' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b> fylke<br />'
									.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter navn');
			$this->format($g, 'g_fylke_type', 'Fylke, type og navn<br />'
									.	' &nbsp; &nbsp; &nbsp; <b>Grupppert:</b>fylke<br />'
									.	' &nbsp; &nbsp; &nbsp; <b>Sortert:</b> alfabetisk etter type og navn');
		}
		$g = $this->formatGrp('op', 'Oppsett');
		$this->format($g, 'op_p_break', 'Bruk linjeskift mellom hver deltaker');
		$this->format($g, 'op_t_break', 'Bruk linjeskift mellom hver tittel');

		$g = $this->formatGrp('n', 'Nøkkeltall');
		$this->format($g, 'n_tall', 'Vis nøkkeltall for rapporten');
		$this->format($g, 'n_gtall', 'Vis nøkkeltall for hver gruppering');
		
#		$this->helper('http://download.ukm.no/UKM_diplommal_lokal.dot', 'diplommal lokal');
#		$this->helper('http://download.ukm.no/UKM_diplommal_fylke.dot', 'diplommal fylke');
		
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
		$navn = 'Alle innslag';
		global $objPHPExcel;
		$this->excel_init('landscape');
		
		exSheetName('INNSLAG');
		
		$objPHPExcel->createSheet(1);
		$objPHPExcel->setActiveSheetIndex(1);
		exSheetName('DELTAKERE','f69a9b');
		
		$objPHPExcel->createSheet(2);
		$objPHPExcel->setActiveSheetIndex(2);
		exSheetName('TITLER','6dc6c1');

		$rad = $p2rad = $p3rad = 1;
		$headerRad = 1;
		$grupperte_innslag = $this->_innslag();
		foreach($grupperte_innslag as $grp => $innslag) {
			foreach($innslag as $inn) {
				$objPHPExcel->setActiveSheetIndex(0);
				$kontakt = $inn->kontaktperson();
				if($this->show('i_fylke')||$this->show('i_kommune'))
					$inn->loadGeo();
					
				$col = 0;
				$rad++;
				if($grp !== 0) {
					$col++;
					excell(i2a($col).$headerRad, 'Gruppering','bold');
					excell(i2a($col).$rad, $grp);
				}
				
				$col++;
				excell(i2a($col).$headerRad, 'Innslag','bold');
				excell(i2a($col).$rad, $inn->g('b_name'));
				if($this->show('i_katogsjan')) {
					$col++;
					excell(i2a($col).$headerRad, 'Kategori','bold');
					excell(i2a($col).$rad, $inn->g('kategori'));
					$col++;
					excell(i2a($col).$headerRad, 'Sjanger','bold');
					excell(i2a($col).$rad, $inn->g('b_sjanger'));
				}
				
				// Geografi
				if($this->show('i_fylke')){
					$col++;
					excell(i2a($col).$headerRad, 'Fylke','bold');
					excell(i2a($col).$rad, $inn->g('fylke'));
				}
				
				if($this->show('i_kommune')){
					$col++;
					excell(i2a($col).$headerRad, 'Kommune','bold');
					excell(i2a($col).$rad, $inn->g('kommune'));
				}
				
				// Varighet
				if($this->show('i_varig')){
					$col++;
					excell(i2a($col).$headerRad, 'Tid','bold');
					excell(i2a($col).$rad, $inn->tid($this->pl_id));
					$col++;
					excell(i2a($col).$headerRad, 'Sekunder','bold');
					excell(i2a($col).$rad, $inn->varighet($this->pl_id));
				}
				
				// TEKNISKE KRAV OG KONFERANSIERTEKSTER
				if($this->show('i_tekn')) {
					$col++;
					excell(i2a($col).$headerRad, 'Tekniske behov','bold');
					excell(i2a($col).$rad, $inn->g('td_demand'));
				}

				if($this->show('i_konf')){
					$col++;
					excell(i2a($col).$headerRad, 'Tekst til konferansierer','bold');
					excell(i2a($col).$rad, $inn->g('td_konferansier'));
				}
#				excell('A1:'.i2a($col).'1', 'Innslagsinformasjon');

				// Kontaktperson
				if($this->show('p_kontaktp')){
					$col++;
#					$cp_start = $col;
					excell(i2a($col).$headerRad, 'Fullt navn','bold');
					excell(i2a($col).$rad, $kontakt->g('name'));
					$col++;
					excell(i2a($col).$headerRad, 'Alder','bold');
					excell(i2a($col).$rad, $kontakt->alder());
					$col++;
					excell(i2a($col).$headerRad, 'Mobil','bold');
					excell(i2a($col).$rad, $kontakt->g('p_phone'));
					$col++;
					excell(i2a($col).$headerRad, 'E-post','bold');
					excell(i2a($col).$rad, $kontakt->g('p_email'));
					$col++;
					excell(i2a($col).$headerRad, 'Fornavn','bold');
					excell(i2a($col).$rad, $kontakt->g('p_firstname'));
					$col++;
					excell(i2a($col).$headerRad, 'Etternavn','bold');
					excell(i2a($col).$rad, $kontakt->g('p_lastname'));
#					$cp_stop = $col;
#					excell(i2a($cp_start).'1:'.i2a($cp_stop).'1', 'Kontaktperson');
				}

				// DELTAKERE I INNSLAGET
				if($this->show('p_vis')){
					$objPHPExcel->setActiveSheetIndex(1);
					$personer = $inn->personObjekter();
					foreach($personer as $p) {
						$p2col = 0;
						$p2rad++;
						if($grp !== 0) {
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Gruppering','bold');
							excell(i2a($p2col).$p2rad, $grp);
						}
						
						$p2col++;
						excell(i2a($p2col).$headerRad, 'Innslag','bold');
						excell(i2a($p2col).$p2rad, $inn->g('b_name'));
						if($this->show('i_katogsjan')) {
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Kategori','bold');
							excell(i2a($p2col).$p2rad, $inn->g('kategori'));
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Sjanger','bold');
							excell(i2a($p2col).$p2rad, $inn->g('b_sjanger'));
						}
						
						// Geografi
						if($this->show('i_fylke')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Fylke','bold');
							excell(i2a($p2col).$p2rad, $inn->g('fylke'));
						}
						
						if($this->show('i_kommune')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Kommune','bold');
							excell(i2a($p2col).$p2rad, $inn->g('kommune'));
						}
						
						// Varighet
						if($this->show('i_varig')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Tid','bold');
							excell(i2a($p2col).$p2rad, $inn->tid($this->pl_id));
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Sekunder','bold');
							excell(i2a($p2col).$p2rad, $inn->varighet($this->pl_id));
						}
		
#						excell('A1:'.i2a($p2col).'1', 'Innslagsinformasjon');

						$p2col++;
#						$p_start = $p2col;
						excell(i2a($p2col).$headerRad, 'Fullt navn','bold');
						excell(i2a($p2col).$p2rad, $p->g('name'));
						if($this->show('p_alder')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Alder','bold');
							excell(i2a($p2col).$p2rad, $p->alder());
						}
						if($this->show('p_instrument')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Instrument','bold');
							excell(i2a($p2col).$p2rad, $p->g('instrument'));
						}
						if($this->show('p_mobil')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'Mobil','bold');
							excell(i2a($p2col).$p2rad, $p->g('p_phone'));
						}
						if($this->show('p_epost')){
							$p2col++;
							excell(i2a($p2col).$headerRad, 'E-post','bold');
							excell(i2a($p2col).$p2rad, $p->g('p_email'));
						}
						$p2col++;
						excell(i2a($p2col).$headerRad, 'Fornavn','bold');
						excell(i2a($p2col).$p2rad, $p->g('p_firstname'));
						$p2col++;
						excell(i2a($p2col).$headerRad, 'Etternavn','bold');
						excell(i2a($p2col).$p2rad, $p->g('p_lastname'));
#						$p_stop = $p2col;
#						excell(i2a($p_start).'1:'.i2a($p_stop).'1', 'Deltakerinfo');
					}
				}

				// TITLER I INNSLAGET
				if($this->show('t_vis')){
					$objPHPExcel->setActiveSheetIndex(2);
					$titler = $inn->titler($this->pl_id);
					foreach($titler as $t) {
						$p3col = 0;
						$p3rad++;
						
						// OM INNSLAGET
						if($grp !== 0) {
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Gruppering','bold');
							excell(i2a($p3col).$p3rad, $grp);
						}

						$p3col++;
						excell(i2a($p3col).$headerRad, 'Innslag','bold');
						excell(i2a($p3col).$p3rad, $inn->g('b_name'));
						if($this->show('i_katogsjan')) {
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Kategori','bold');
							excell(i2a($p3col).$p3rad, $inn->g('kategori'));
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Sjanger','bold');
							excell(i2a($p3col).$p3rad, $inn->g('b_sjanger'));
						}
						
						// Geografi
						if($this->show('i_fylke')){
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Fylke','bold');
							excell(i2a($p3col).$p3rad, $inn->g('fylke'));
						}
						
						if($this->show('i_kommune')){
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Kommune','bold');
							excell(i2a($p3col).$p3rad, $inn->g('kommune'));
						}
						
						// Varighet
						if($this->show('i_varig')){
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Tid','bold');
							excell(i2a($p3col).$p3rad, $inn->tid($this->pl_id));
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Sekunder','bold');
							excell(i2a($p3col).$p3rad, $inn->varighet($this->pl_id));
						}
		
#						excell('A1:'.i2a($p3col).'1', 'Innslagsinformasjon');
						// OM TITLENE

						$p3col++;
#						$t_start = $p3col;
						excell(i2a($p3col).$headerRad, 'Tittel','bold');
						excell(i2a($p3col).$p3rad, $t->g('tittel'));
						
						if($this->show('t_varig')){
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Varighet','bold');
							excell(i2a($p3col).$p3rad, $t->g('tid'));
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Sekunder','bold');
							excell(i2a($p3col).$p3rad, $t->g('varighet'));
						}
						
						if($this->show('t_detaljer')){
							$p3col++;
							excell(i2a($p3col).$headerRad, 'Detaljer','bold');
							$parentes = $t->g('parentes');
							$parentesfri = substr($parentes, 1, strlen($parentes)-2);
							excell(i2a($p3col).$p3rad, $parentesfri);
						}
#						$t_stop = $p3col;
#						excell(i2a($t_start).'1:'.i2a($t_stop).'1', 'Tittelinfo');
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

		$headerCellStyle = array('borderBottomColor'=>'1e4a45', 'borderBottomSize'=>20);
		$grupperte_innslag = $this->_innslag();
		if($this->showFormat('n_tall')){
			$col1 = 3500;
			$col2 = $col3 = 1500;
			$nt = $this->_nokkeltall($grupperte_innslag);	
			
			$section->addTextBreak(9);
			woText($section, 'Nøkkeltall', 'place');
			woText($section, '(basert på innslag som vises i denne rapporten)', 'center');
			$section->addTextBreak(1);

			//INNSLAGS-TABELL			
			$tab = $section->addTable(array('align'=>'center'));
			$tab->addRow();

			// Navn
			$c = $tab->addCell($col1, $headerCellStyle);
			woText($c, 'Kategori','h1');
			$c = $tab->addCell($col2, $headerCellStyle);
			woText($c, 'Innslag');
			$c = $tab->addCell($col2, $headerCellStyle);
			woText($c, 'Personer');
			$c = $tab->addCell($col2, $headerCellStyle);
			woText($c, 'Titler');
			$c = $tab->addCell($col2, $headerCellStyle);
			woText($c, 'Varighet');

			foreach($nt['typer'] as $ntkey => $ntval) {
				$tab->addRow();
				
				$c = $tab->addCell($col1);
				woText($c, $ntkey, 'bold');
				$c = $tab->addCell($col2);
				woText($c, $ntval['inn'], 'bold');
				$c = $tab->addCell($col2);
				woText($c, $ntval['pers'], 'bold');
				$c = $tab->addCell($col2);
				woText($c, $ntval['titler_ant'], 'bold');
				$c = $tab->addCell($col2);
				woText($c, UKMN_tid($ntval['titler_tid']), 'bold');
				
				if($ntkey == 'Scene') {
					foreach($nt['scenekat'] as $skkey => $skval){
						$tab->addRow();
						$c = $tab->addCell($col1);
						woText($c, '  '.$skkey);
						$c = $tab->addCell($col2);
						woText($c, $skval['inn']);
						$c = $tab->addCell($col2);
						woText($c, $skval['pers']);
						$c = $tab->addCell($col2);
						woText($c, $skval['titler_ant']);
						$c = $tab->addCell($col2);
						woText($c, UKMN_tid($skval['titler_tid']));
					}
				}
				if($ntkey == 'Andre innslag') {
					foreach($nt['annetkat'] as $skkey => $skval){
						$tab->addRow();	
						$c = $tab->addCell($col1);
						woText($c, '  '.$skkey);
						$c = $tab->addCell($col2);
						woText($c, $skval['inn']);
						$c = $tab->addCell($col2);
						woText($c, $skval['pers']);
					}
				}
			}
			
			$tab->addRow();	
			$c = $tab->addCell($col1);
			woText($c, '');
			$c = $tab->addCell($col2);
			woText($c, '');
			$c = $tab->addCell($col2);
			woText($c, '');
			
			$tab->addRow();	
			$c = $tab->addCell($col1);
			woText($c, 'Sum');
			$c = $tab->addCell($col2);
			woText($c, $nt['innslag']);
			$c = $tab->addCell($col2);
			woText($c, $nt['personer']);
			$c = $tab->addCell($col2);
			woText($c, $nt['titler_ant']);
			$c = $tab->addCell($col2);
			woText($c, UKMN_tid($nt['titler_tid']));

			$tab->addRow();	
			$c = $tab->addCell($col1);
			woText($c, 'Unike personer');
			$c = $tab->addCell($col2);
			woText($c, '');
			$c = $tab->addCell($col2);
			woText($c, $nt['unike']);
		$section->addPageBreak();

		}

		
		
		foreach($grupperte_innslag as $grp => $innslag) {
			if($grp !== 0)
				woText($section, $grp, 'grp');
			$group_sum_time = 0;
			$group_sum_bands = 0;
			$group_sum_titles = 0;
			$group_sum_all_p = array();
			$group_sum_uni_p = array();
			foreach($innslag as $inn) {
				$group_sum_bands++;
				$kontakt = $inn->kontaktperson();
				## LAST INN GEOGRAFI KUN HVIS NØDVENDIG
				if($this->show('i_fylke')||$this->show('i_kommune'))
					$inn->loadGeo();

				//INNSLAGS-TABELL			
				$tab = $section->addTable();
				$tab->addRow();

				// Navn
				$c = $tab->addCell(4000, $headerCellStyle);
				woText($c, $inn->g('b_name'),'h1');

				// Kategori og sjanger
				$c = $tab->addCell(2000, $headerCellStyle);
				if($this->show('i_katogsjan'))
					woText($c, ($inn->g('bt_id')==2	?	'Film - '	:	'')
							  .($inn->g('bt_id')==3	?	'Utstilling - '	:	'')
							  .$inn->g('kategori_og_sjanger'));

				// Geografi
				$c = $tab->addCell(2000, $headerCellStyle);				
				if($this->show('i_fylke'))
					woText($c, $inn->g('fylke').($this->show('i_kommune') ? ' - '.$inn->g('kommune') : ''));
				
				if(!$this->show('i_fylke') && $this->show('i_kommune'))
					woText($c, $inn->g('kommune'));

				// Varighet
				$c = $tab->addCell(2000, $headerCellStyle);
				if($this->show('i_varig') && !$inn->tittellos())
					woText($c, $inn->tid($this->pl_id));
				if($this->show('i_varig') && $inn->tittellos())
					woText($c, ' ');
				if($this->show('p_kontaktp')){
					woText($section, 'Kontaktperson: '. $kontakt->g('name')
							.	' ('.$kontakt->alder().' år) '
							.	'- mobil: '.$kontakt->g('p_phone') .' - e-post: '. $kontakt->g('p_email')
						,'bold');
				}

				// DELTAKERE I INNSLAGET
				if($this->show('p_vis')){
					woText($section, 'Deltakere:', 'bold');
					$personer = $inn->personObjekter();
					$personText = array();
					foreach($personer as $p) {
						$group_sum_all_p[] = $p->g('p_id');
						$group_sum_uni_p[ $p->g('p_id') ] = 1;
						$person = $p->g('name')
							.($this->show('p_alder') 	? ' ('.$p->alder().' år) '			: '')
							.($this->show('p_instrument') 	? ' '.$p->g('instrument').' '			: '')
							.($this->show('p_mobil')	? ' - mobil: '.$p->g('p_phone')		: '')
							.($this->show('p_epost')	? ' - e-post: '.$p->g('p_email')		: '')
							;
						// Gruppering av personer
						if($this->showFormat('op_p_break'))
							woText($section, $person);
						else
							$personText[] = $person;
					}
					if(sizeof($personText)>0)
						woText($section, implode(', ', $personText));
				}

				// TITLER I INNSLAGET
				if($this->show('t_vis') && !$inn->tittellos()){
					woText($section, 'Titler: ', 'bold');
					$titler = $inn->titler($this->pl_id);
					$tittelText = array();
					foreach($titler as $t) {
						$group_sum_time += $t->g('varighet');
						$group_sum_titles++;
						$tittel = $t->g('tittel')
							.($this->show('t_varig')		? ' - varighet: '.$t->g('tid').''	: '')
							.($this->show('t_detaljer')		? ' '.$t->g('parentes').' '			: '')
							;
						if($this->showFormat('op_t_break'))
							woText($section, $tittel);
						else
							$tittelText[] = $tittel;
					}
					if(sizeof($tittelText)>0)
						woText($section, implode(', ', $tittelText));
				}	

				// TEKNISKE KRAV OG KONFERANSIERTEKSTER
				if($this->show('i_tekn')) {
					woText($section, 'Tekniske behov:', 'bold');
					woText($section, $inn->g('td_demand'));
				}

				if($this->show('i_konf')){
					woText($section, 'Tekst til konferansierer; ', 'bold');
					woText($section, $inn->g('td_konferansier'));
				}

				$section->addTextBreak(2);
			}
			if($this->showFormat('n_gtall') && $grp !== 0) {

				$tab = $section->addTable();

				$tab->addRow();
				$c = $tab->addCell(9000);
				$c = $tab->addCell(5000);
				woText($c, 'Nøkkeltall for "'.$grp.'"', 'h4');

				// Navn
				$tab->addRow();
				$c = $tab->addCell(9000);
				$c = $tab->addCell(3000);
				woText($c, 'Antall innslag');
				$c = $tab->addCell(2000);
				woText($c, $group_sum_bands, 'bold');

				if($this->show('t_vis')) {
					$tab->addRow();
					$c = $tab->addCell(9000);
					$c = $tab->addCell(3000);
					woText($c, 'Antall titler');
					$c = $tab->addCell(2000);
					woText($c, $group_sum_titles, 'bold');
				}

				if($this->show('p_vis')) {
					$tab->addRow();
					$c = $tab->addCell(9000);
					$c = $tab->addCell(3000);
					woText($c, 'Antall personer');
					$c = $tab->addCell(2000);
					woText($c, sizeof($group_sum_all_p), 'bold');

					$tab->addRow();
					$c = $tab->addCell(9000);
					$c = $tab->addCell(3000);
					woText($c, 'Antall unike personer');
					$c = $tab->addCell(2000);
					woText($c, sizeof($group_sum_uni_p), 'bold');
				}

				if($this->show('t_vis')) {
					$tab->addRow();
					$c = $tab->addCell(9000);
					$c = $tab->addCell(3000);
					woText($c, 'Total varighet');
					$c = $tab->addCell(2000);
					woText($c, UKMN_tid($group_sum_time), 'bold');
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
		$grupperte_innslag = $this->_innslag();
		$nt = $this->_nokkeltall($grupperte_innslag);
		echo $this->html_init('Innslag og personer');
		?>
		<ul class="rapporten">
		<?php
		if(!is_array($grupperte_innslag)){
			echo '<strong>Ingen innslag</strong>';
		} else {
			if($this->showFormat('n_tall')){
				?>
			<div class="nokkeltallcontainer">
				<h1 class="nokkeltall">Nøkkeltall</h1>
				(Basert på innslag som vises i denne rapporten)
				
				<ul class="nokkeltall">
					<li class="header">
						<div class="label">Kategori</div>
						<div class="header innslag">Innslag</div>
						<div class="header personer">Personer</div>
						<div class="header titler">Titler</div>
						<div class="header varighet">Varighet</div>
					</li>
				<?php
				foreach($nt['typer'] as $ntkey => $ntval) {
					?><li>
						<div class="label"><?= $ntkey ?></div>
						<div class="innslag bold"><?= $ntval['inn']?></div>
						<div class="personer bold"><?= $ntval['pers']?></div>
						<div class="titler bold"><?= $ntval['titler_ant']?></div>
						<div class="varighet bold"><?= UKMN_tid($ntval['titler_tid'])?></div>
					</li>
				<?php
					if($ntkey == 'Scene') {
						foreach($nt['scenekat'] as $skkey => $skval){
							?><li class="low">
								<div class="normallabel"><?= $skkey ?></div>
								<div class="innslag"><?= $skval['inn']?></div>
								<div class="personer"><?= $skval['pers']?></div>
								<div class="titler"><?= $skval['titler_ant']?></div>
								<div class="varighet"><?= UKMN_tid($skval['titler_tid'])?></div>
							</li>
				<?php
						}
					}
					if($ntkey == 'Andre innslag') {
						if(is_array($nt['annetkat']))
						foreach($nt['annetkat'] as $skkey => $skval){
							?><li class="low">
								<div class="normallabel"><?= $skkey ?></div>
								<div class="innslag"><?= $skval['inn']?></div>
								<div class="personer"><?= $skval['pers']?></div>
							</li>
				<?php
						}
					}
				}
				?>
					<li>
						<div class="label">&nbsp;</div>
						<div class="innslag">&nbsp;</div>
						<div class="personer">&nbsp;</div>
					</li>
					<li>
						<div class="label">Sum</div>
						<div class="innslag"><?= $nt['innslag']?></div>
						<div class="personer bold"><?= $nt['personer']?></div>
						<div class="personer bold"><?= $nt['titler_ant']?></div>
						<div class="personer bold"><?= UKMN_tid($nt['titler_tid'])?></div>
					</li>
					<li>
						<div class="label">Unike personer</div>
						<div class="innslag">&nbsp;</div>
						<div class="personer"><?= $nt['unike']?></div>
						<div class="clear"></div>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
			<script>
				jQuery(document).on('click', '.UKMTV img', function(){
					var container = jQuery(this).parents('div.UKMTV');
					var embedcontainer = container.find('div.embedcontainer');
					embedcontainer.html('<iframe src="' +
										container.find('div.embedcontainer').attr('data-framesource') +
										'" frameborder width="'+ jQuery(this).width() +
										'" height="'+ jQuery(this).height() +
										'" style="max-width: 100%; border:none;"></iframe>').slideDown();
					jQuery(this).slideUp();
				});
			</script>
			<?php
			}
			foreach($grupperte_innslag as $grp => $innslag){
				if($grp !== 0)
					echo '<li class="grp">'.$grp.'</li>';
				$group_sum_time = 0;
				$group_sum_bands = 0;
				$group_sum_titles = 0;
				$group_sum_all_p = array();
				$group_sum_uni_p = array();
				foreach($innslag as $inn) {
					$group_sum_bands++;
					$kontakt = $inn->kontaktperson();
					## LAST INN GEOGRAFI KUN HVIS NØDVENDIG
					if($this->show('i_fylke')||$this->show('i_kommune'))
						$inn->loadGeo();
						
					$UKMTVhtml = '';
					if( $this->show('m_ukmtv') ) {
						$media = $inn->related_items();
						if( isset( $media['tv'] ) && sizeof( $media['tv'] ) > 0) {
							foreach( $media['tv'] as $tv_id => $tv ) {
#								$tv->iframe('1100px');
#								$UKMTVhtml .= '<div class="UKMTV">'. $tv->embedcode('1100px') .'</div>';
								$UKMTVhtml .= '<div class="UKMTV clickable">'
											. '	<div class="image"><img src="'. $tv->image_url .'" style="max-width:100%;" /></div>'
											. '	<div class="embedcontainer" style="display:none;" data-framesource="'. $tv->embed_url .'">'
											.'</div>';
							}
						}
						if( empty( $UKMTVhtml ) ) {
							$UKMTVhtml = 'Ingen filmer lastet opp';
						}
					}
			
					echo '<li class="innslag">'
						### NAVN, KAT&SJAN, FYLKE OG KOMMUNE FOR INNSLAG 
						.	'<div class="b_name_cont">'
						### NAVN
						.		'<div class="b_name">'.$inn->g('b_name').'</div>'
						
						### VARIGHET
						.		($this->show('i_varig')
									? '<div class="meta small">'.(!$inn->tittellos()?$inn->tid($this->pl_id):'&nbsp;').'</div>'
									: '')
									
						### FYLKE
						.		($this->show('i_fylke')
									? '<div class="meta">'.$inn->g('fylke').($this->show('i_kommune') ? ' - '.$inn->g('kommune') : '').'</div>'
									: '')
		
						### KOMMUNE
						.		(!$this->show('i_fylke') && $this->show('i_kommune')
									? '<div class="meta">'.$inn->g('kommune').'</div>'
									: '')
						### KATEGORI OG SJANGER
						.		($this->show('i_katogsjan')
									? '<div class="meta">'
										.($inn->g('bt_id')==2	?	'Film - '	:	'')
										.($inn->g('bt_id')==3	?	'Utstilling - '	:	'')
										. $inn->g('kategori_og_sjanger').'</div>'
									: '')
						
		
						.	'<div class="clear"></div>'
						.	'</div>'
						.	'<div class="clear"></div>'
						
						### KONTAKTPERSON
						. ($this->show('p_kontaktp')
								? '<div class="label">Kontaktperson: '
								.	$kontakt->g('name')
								.	' ('.$kontakt->alder().' år) '
								.	'- mobil: <span class="UKMSMS">'.$kontakt->g('p_phone') .'</span> - e-post: <a href="mailto:'. $kontakt->g('p_email').'" class="UKMMAIL">'.$kontakt->g('p_email').'</a>'
								.	'</div>'
								: '')
						;
						if($this->show('p_vis')){
							echo '<div class="label">Deltakere: </div>'
								.'<div class="desc">';
							$personer = $inn->personObjekter();
							$i=0;
							foreach($personer as $p) {
								$group_sum_all_p[] = $p->g('p_id');
								$group_sum_uni_p[ $p->g('p_id') ] = 1;
								$i++;
								echo $p->g('name')
									.($this->show('p_alder')
										? ' ('.$p->alder().' år) '
										: '')
									.($this->show('p_instrument')
										? ' <em>'.$p->g('instrument').'</em> '
										: '')
									.($this->show('p_mobil')
										? ' - mobil: <span class="UKMSMS">'.$p->g('p_phone').'</span>'
										: '')
									.($this->show('p_epost')
										? ' - e-post: <a href="mailto:'.$p->g('p_email').'" class="UKMMAIL">'.$p->g('p_email').'</a>'
										: '')

									.($this->showFormat('op_p_break')
										? '<br />'
										: ($i < sizeof($personer) ? ', ':'')
										)
								;
							}
							echo '</div>';
						}
						
						if($this->show('t_vis') && !$inn->tittellos()){
							echo '<div class="label">Titler: </div>'
								.'<div class="desc">';
							$titler = $inn->titler($this->pl_id);
							$i=0;
							foreach($titler as $t) {
								$group_sum_time += $t->g('varighet');
								$group_sum_titles++;
								$i++;
								echo $t->g('tittel')
									.($this->show('t_varig')
										? ' - varighet: '.$t->g('tid').''
										: '')
									.($this->show('t_detaljer')
										? ' '.$t->g('parentes').' '
										: '')
		
									.($this->showFormat('op_t_break')
										? '<br />'
										: ($i < sizeof($titler) ? ', ':'')
										)
								;
							}
							echo '</div>';
						}
		
						
						echo ''
						### VIS TEKNISKE KRAV
						. ($this->show('i_tekn')
							? '<div class="label">Tekniske behov: </div>'
							. '<div class="desc">'. $inn->g('td_demand') .'</div>'
							: '')
						
						### VIS KONFERANSIERTEKSTER
						. ($this->show('i_konf')
							? '<div class="label">Tekst til konferansierer: </div>'
							. '<div class="desc">'. $inn->g('td_konferansier') .'</div>'
							: '')

						### VIS UKM-TV
						. ($this->show('m_ukmtv')
							? '<div class="label">Opplastede filmer i UKM-TV</div>'
							. '<div class="desc">'. $UKMTVhtml .'</div>'
							: '')

						
						. '</li>';
				}
				if($this->showFormat('n_gtall') && $grp !== 0)
					echo '<li class="sum">
							<div class="label-head">Nøkkeltall for "'.$grp.'":</div>
							
							<div class="clear clearfix clear-fix"></div>
							<div class="data">'.$group_sum_bands.'</div>
							<div class="label">Antall innslag:</div>'

							.($this->show('t_vis') 
							? '<div class="clear clearfix clear-fix"></div>
							<div class="data">'.$group_sum_titles.'</div>
							<div class="label">Antall titler:</div>'
							: '')

							.($this->show('p_vis') 
							? '<div class="clear clearfix clear-fix"></div>
								<div class="data">'.sizeof($group_sum_all_p).'</div>
								<div class="label">Antall personer:</div>
								
								<div class="clear clearfix clear-fix"></div>
								<div class="data">'.sizeof($group_sum_uni_p).'</div>
								<div class="label">Antall unike personer:</div>'
							: '')

							.($this->show('t_varig') && $this->show('t_vis')
							? '<div class="clear clearfix clear-fix"></div>
								<div class="data-time">'.UKMN_tid($group_sum_time).'</div>
								<div class="label-time">Total varighet:</div>'
							: '')
							
							
							.'</li>'
						;
				//$group_sum_time = 0;	

			}
		} ?>
		</ul>
		<?php
	}
	
	/********************************************************************************/
	/*								PRIVATE Nøkkeltall								*/
	/********************************************************************************/
	/**
	 * _nokkeltall function
	 * 
	 * Genererer et array med innslags-statistikk
	 *
	 * @access private
	 * @return array
	 */	
	public function _nokkeltall($grupperte_innslag){
		// Loop alle innslag
		$unike = array();
		$nt = array('typer'=>array('Scene'=>array('inn'=>0,'pers'=>0),
								   'Film'=>array('inn'=>0,'pers'=>0),
								   'Utstilling'=>array('inn'=>0,'pers'=>0),
								   'Andre innslag'=>array('inn'=>0,'pers'=>0)));
		foreach($this->innslag as $inn){
			$btid = $inn->g('bt_id');
			// Antall innslag
			switch($btid){
				case 1:
				case 2:
				case 3:
						$key = $inn->g('bt_name');		break;
						break;
				default:
						$key = 'Andre innslag';			break;
			}
			$nt['typer'][$key]['inn']++;
			
			// Antall personer
			$personer = $inn->personer();
			
			foreach($personer as $p_arr)
				$unike[$p_arr['p_id']] = true;

			$nt['typer'][$key]['pers'] += sizeof($personer);
			$nt['personer'] += sizeof($personer);
			$nt['innslag']++;
			
			$titler = $inn->titler($this->pl_id);
			$innslag_tid = 0;
			foreach($titler as $t) {
				$innslag_tid += $t->g('varighet');
				$nt['typer'][$key]['titler_tid'] += $innslag_tid;
				$nt['titler_tid'] += $innslag_tid;
				$nt['typer'][$key]['titler_ant']++;
				$nt['titler_ant']++;
			}
			
			if($btid==1) {
				$nt['scenekat'][ucfirst($inn->g('b_kategori'))]['inn']++;
				$nt['scenekat'][ucfirst($inn->g('b_kategori'))]['pers'] += sizeof($personer);
				$nt['scenekat'][ucfirst($inn->g('b_kategori'))]['titler_ant'] += sizeof($titler);
				$nt['scenekat'][ucfirst($inn->g('b_kategori'))]['titler_tid'] += $innslag_tid;
			}
			if($key == 'Andre innslag') {
				$nt['annetkat'][ucfirst($inn->g('bt_name'))]['inn']++;
				$nt['annetkat'][ucfirst($inn->g('bt_name'))]['pers'] += sizeof($personer);
				#$nt['annetkat'][ucfirst($inn->g('bt_name'))]['titler_ant'] += sizeof($titler);

			}
		}
		$nt['unike'] = sizeof($unike);


		return $nt;
	}

	/********************************************************************************/
	/*								PRIVATE Innslag									*/
	/********************************************************************************/
	/**
	 * _innslag function
	 * 
	 * Genererer et array med innslag som loopes i rapporten basert på rapportinnstillinger
	 * for sortering.
	 *
	 * @access private
	 * @return array
	 */	
	public function _innslag(){
		if($this->showFormat('g_type'))
			return $this->_innslag_gruppert_type();
		if($this->showFormat('g_kommune'))
			return $this->_innslag_gruppert_geo('kommune');
		if($this->showFormat('g_fylke'))
			return $this->_innslag_gruppert_geo('fylke');
		if($this->showFormat('g_kommune_type'))
			return $this->_innslag_gruppert_geo_type('kommune');
		if($this->showFormat('g_fylke_type'))
			return $this->_innslag_gruppert_geo_type('fylke');
		if($this->showFormat('g_type_kommune'))
			return $this->_innslag_gruppert_type_geo('kommune');
		if($this->showFormat('g_type_fylke'))
			return $this->_innslag_gruppert_type_geo('fylke');


		return $this->_innslag_sortert_navn();
	}
	
	public function storekey($key, &$storage_array) {
		if(isset($storage_array[$key])) {
			while(isset($storage_array[$key])) {
				$key = $key.' ';
			}
		}
		return $key;
	}
	
	/**
	 * _innslag_sortert_navn function
	 * 
	 * Lager en liste over alle innslag på mønstringen, sortert etter navn
	 *
	 * @access private
	 * @return array
	 */	
	public function _innslag_sortert_navn(){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array){
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$this->innslag[$inn->g('b_id')] = $inn;

			$storekey = $this->storekey($inn_array['b_name'], $innslagene);
			$innslagene[$storekey] = $inn;
		}
		$this->_deep_ksort($innslagene);
		
		return array(0=>$innslagene);
	}

	/**
	 * _innslag_gruppert_type function
	 * 
	 * Lager en liste over alle innslag på mønstringen, gruppert etter bt_name, sortert alfabetisk
	 *
	 * @access private
	 * @return array
	 */	
	public function _innslag_gruppert_type(){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array) {
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$this->innslag[$inn->g('b_id')] = $inn;
			$storekey = $this->storekey($inn_array['b_name'], $innslagene[$inn->g('bt_name')]);
			$innslagene[$inn->g('bt_name')][$storekey] = $inn;
		}
		$this->_deep_ksort($innslagene);
		return $innslagene;
	}

	/**
	 * _innslag_gruppert_geo function
	 * 
	 * Lager en liste over alle innslag på mønstringen, gruppert etter geografi (kommune/fylke), sortert alfabetisk
	 *
	 * @access private
	 * @param field kommune/fylke
	 * @return array
	 */	
	public function _innslag_gruppert_geo($field='kommune'){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array) {
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$inn->loadGEO();
			$this->innslag[$inn->g('b_id')] = $inn;
			$storekey = $this->storekey($inn_array['b_name'], $innslagene[$inn->g($field)]);

			$innslagene[$inn->g($field)][$storekey] = $inn;
		}
		$this->_deep_ksort($innslagene);
		return $innslagene;
	}
	
	/**
	 * _innslag_gruppert_geo_type function
	 * 
	 * Lager en liste over alle innslag på mønstringen, gruppert etter geografi (kommune/fylke), sortert etter type, deretter alfabetisk
	 *
	 * @access private
	 * @param field kommune/fylke
	 * @return array
	 */	
	public function _innslag_gruppert_geo_type($field='kommune'){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array) {
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$inn->loadGEO();
			$this->innslag[$inn->g('b_id')] = $inn;
			$storekey = $this->storekey($inn->g('bt_id').' '.$inn_array['b_name'], $innslagene[$inn->g($field)]);

			$innslagene[$inn->g($field)][$storekey] = $inn;
		}
		$this->_deep_ksort($innslagene);
		return $innslagene;
	}
	
	/**
	 * _innslag_gruppert_type _geofunction
	 * 
	 * Lager en liste over alle innslag på mønstringen, gruppert etter bt_name, sortert alfabetisk etter geografi og navn
	 *
	 * @access private
	 * @return array
	 */	
	public function _innslag_gruppert_type_geo($field='kommune'){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array) {
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$inn->loadGEO();
			$this->innslag[$inn->g('b_id')] = $inn;
			$storekey = $this->storekey($inn->g($field).' '.$inn_array['b_name'], $innslagene[$inn->g('bt_name')]);
			$innslagene[$inn->g('bt_name')][$storekey] = $inn;
		}
		$this->_deep_ksort($innslagene);
		return $innslagene;
	}

}
?>