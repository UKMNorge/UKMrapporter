<?php

class rapport{
	/********************************************************************************/
	/*									PUBLIC										*/
	/********************************************************************************/
	/**
	 * __construct function.
	 * 
	 * Ingen funksjon
	 *
	 * @access public 
	 * @param none
	 * @return class
	 */
	public function __construct($name, $kat){
		$this->name = $name;
		$this->kat = $kat;
		$this->pl_id = get_option('pl_id');
	}
	
	public function setSeason($season, $current=false){
		if($season == $current) {
			$m = new monstring($this->pl_id);
		} else {
			$m = new tidligere_monstring($this->pl_id, $season);
			$m = $m->monstring_get();
		}
		$this->m = $m;

		if(!is_object($m))
			return false;

		$this->pl_id = $m->g('pl_id');
		return true;
	}
	
	public function _postConstruct(){	
		$this->_logStat();
	}

	public function setShow($opts){
		$this->_logReport();
		if(!is_array($opts))
			$opts = array();
#			return false;
		foreach($opts as $opt){
			$this->_logSetting($this->optKeys[$opt]);
			$this->show_opts[$this->optKeys[$opt]] = true;
		}
		return true;
	}
	
	public function setShowFormat($formats){
		if(!is_array($formats))
			return false;
		foreach($formats as $opt){
			$this->_logSetting($this->formatKeys[$opt]);
			$this->show_formats[$this->formatKeys[$opt]] = true;
		}
		return true;
	}
	
	
	/********************************************************************************/
	/*								LOG (private)								*/
	/********************************************************************************/
	
	private function _logStat(){
		$qry = new SQL("SELECT COUNT(`ubrv_id`) AS `ant_visninger`
						FROM `log_rapporter_visninger`
						WHERE `ubrv_pl_id` = '#plid'
						AND `ubrv_rapport` = '#rapport'",
						array('plid'=>$this->pl_id,
							  'rapport'=>$this->name));
		$this->log_antall_visninger['rapporten'] = (int)$qry->run('field', 'ant_visninger');

		$qry = new SQL("SELECT `ubrc_valg`,
								COUNT(`ubrc_valg`) AS `ant_visninger`
						FROM `log_rapporter_checkbox`
						WHERE `ubrc_pl_id` = '#plid'
						AND `ubrc_rapport` = '#rapport'
						GROUP BY `ubrc_valg`",
						array('plid'=>$this->pl_id,
							  'rapport'=>$this->name));
		$res = $qry->run();
		while($r = mysql_fetch_assoc($res)) {
			$this->log_antall_visninger[$r['ubrc_valg']] = (int)$r['ant_visninger'];
		}
	}
	private function _logReport() {
		$qry = new SQLins('log_rapporter_visninger');
		$qry->add('ubrv_rapport', $this->name);
		$qry->add('ubrv_pl_id', $this->pl_id);
		$qry->run();
	}
	
	private function _logSetting($setting) {
		$qry = new SQLins('log_rapporter_checkbox');
		$qry->add('ubrc_rapport', $this->name);
		$qry->add('ubrc_valg', $setting);
		$qry->add('ubrc_pl_id', $this->pl_id);
		$qry->run();
	}
	
	private function _log_mostPop($options, $selected) {
		$max = 0;
		$mostPop = '';
		foreach($options as $optKey => $optName) {
			if($this->log_antall_visninger[$optKey] > $max)
				$mostPop = $optKey;
				$max = $this->log_antall_visninger[$optKey];
		}
		return $mostPop == $selected ? 1 : 0;
	}

	/********************************************************************************/
	/*								OPTIONS (public)								*/
	/********************************************************************************/
	
	/**
	 * show function.
	 * 
	 * Returnerer en innstilling 
	 *
	 * @access public 
	 * @param string $show[var_name]
	 * @return bool
	 */
	public function show($opt){
		return isset($this->show_opts[$opt]);
	}

	/**
	 * optGrp function.
	 * 
	 * Lager en option-gruppe for rapportinnstillinger
	 *
	 * @access public
	 * @param shortname (bruk i opt())
	 * @param name
	 * @return void
	 */
	public function optGrp($shortname, $name, $type='checkbox') {
		$this->optGrps[$shortname] = $name;
		$this->optGrps_type[$shortname] = $type;
		return $shortname;
	}
	
	/**
	 * opt function.
	 * 
	 * Lager en option for rapportinnstillinger
	 *
	 * @access public
	 * @param shortname (bruk i opt())
	 * @param name
	 * @return void
	 */
	 public function opt($optGrp, $shortname, $name) {
		$this->opts[$optGrp][$shortname] = $name;
		$this->optKeys[$name] = $shortname;
	}
	
	/**
	 * get_options function.
	 * 
	 * Returnerer array med options
	 *
	 * @access public
	 * @return array options and states
	 */
	 public function get_options() {
	 	if(is_array($this->optGrps))
 		foreach($this->optGrps as $key => $name) {
 			if(is_array($this->opts[$key])) {
				foreach($this->opts[$key] as $optKey => $optName) {
					$state = @round($this->log_antall_visninger[$optKey] / $this->log_antall_visninger['rapporten']);
		 			$options[$name][$optKey] = array('checked'=>$state,
		 											 'name'=>$optName,
		 											 'type'=>$this->optGrps_type[$key]
		 											 );
				}
			}
		}	
		return $options;
	}

	
	/********************************************************************************/
	/*								FORMATS (public)								*/
	/********************************************************************************/
	
	/**
	 * format function.
	 * 
	 * Returnerer status for et formatvalg
	 *
	 * @access public 
	 * @param string $show[var_name]
	 * @return bool
	 */
	public function showFormat($opt){
		return isset($this->show_formats[$opt]);
	}

	/**
	 * formatGrp function.
	 * 
	 * Lager en format-gruppe for rapportinnstillinger
	 *
	 * @access public
	 * @param shortname (bruk i opt())
	 * @param name
	 * @return void
	 */
	public function formatGrp($shortname, $name, $type='checkbox') {
		$this->formatGrps[$shortname] = $name;
		$this->formatGrps_type[$shortname] = $type;
		return $shortname;
	}
	
	/**
	 * setFormat function.
	 * 
	 * Lager et valg for rapportformatering
	 *
	 * @access public
	 * @param shortname (bruk i opt())
	 * @param name
	 * @return void
	 */
	 public function format($optGrp, $shortname, $name) {
		$this->format[$optGrp][$shortname] = $name;
		$this->formatKeys[preg_replace('/[^A-Za-z0-9_]/', '',$name)] = $shortname;
	}
	
	/**
	 * get_formats function.
	 * 
	 * Returnerer array med formater
	 *
	 * @access public
	 * @return array options and states
	 */
	 public function get_formats() {
	 	if(is_array($this->formatGrps))
 		foreach($this->formatGrps as $key => $name) {
 			foreach($this->format[$key] as $optKey => $optName) {
				if($this->formatGrps_type[$key]=='radio')
					$state = $this->_log_mostPop($this->format[$key], $optKey);
	 			$options[$name][$optKey] = array('checked'=>$state,
	 											 'name'=>$optName,
	 											 'type'=>$this->formatGrps_type[$key]
	 											 );
			}
		}	
		return $options;
	}
	
	/********************************************************************************/
	/*								HELPERS (public)								*/
	/********************************************************************************/
	
	/**
	 * showHelpers function.
	 * 
	 * Returnerer liste over hjelpefiler
	 *
	 * @access public 
	 * @return array helpers
	 */
	public function get_helpers(){
		return $this->helper_files;
	}
	
	/**
	 * helper function.
	 * 
	 * Registrerer en fil som skal vises som hjelper til rapporten
	 *
	 * @access public 
	 * @return void
	 */
	public function helper($fileUrl, $fileName){
		$this->helper_files[$fileUrl] = $fileName;
	}

	/********************************************************************************/
	/*								DRAW (public)								*/
	/********************************************************************************/
	
	/**
	 * draw function.
	 * 
	 * Returnerer HTML for gitt element
	 *
	 * @access public
	 * @param what (hva som skal tegnes)
	 * @return string html
	 */
	 public function draw($what) {
		if(method_exists($this, '_draw_'.$what))
			return $this->{'_draw_'.$what}();
	}


	public function generate(){
		return 'Beklager, en feil har oppstått, da rapporten mangler en nødvendig funksjon!';
	}
	
	
	
	/**
	 * get_season function.
	 * 
	 * Generer HTML for sesong-valg
	 *
	 * @access public
	 * @return array seasons
	 */
	 public function get_season() {	 		
	 	for($i=2010; $i<get_option('season')+1; $i++)
	 		$seasons[] = $i;
	 	
	 	return $seasons;
	 }
	/********************************************************************************/
	/*									PRIVATE										*/
	/********************************************************************************/
	/** 
	 * _deep_ksort function.
	 * 
	 * Sorterer et multidimensjonalt array etter nøkkel
	 *
	 * @access public
	 * @param array reference
	 * @return void
	 *	REF: http://www.php.net/manual/en/function.ksort.php#105399
	*/
	public function _deep_ksort(&$arr) {
		if(!is_array($arr))
			return;
	    ksort($arr);
	    foreach ($arr as &$a) {
	        if (is_array($a) && !empty($a)) {
	            $this->_deep_ksort($a);
	        }
	    }
	}
	
	
	/**
	 * _sanName function
	 * 
	 * Rensker opp en streng så den kan brukes til filnavn
	 *
	 * @access private
	 * @return string safename
	 */	
	private function _sanName($name=false){
		if(!$name)
			$name = $this->name;
		return preg_replace('/[^a-z]/i', '', strtolower($name));
	}
	
	/**
	 * csv_write function
	 *
	 * Creates a file for the CSV-export and writes to it.
	 * Returns a 
	 * By @asgeirsh, 26.03.2017
	 *
	 */
	public function csv_write($data) {
		$tmp_folder = '/home/ukmno/public_subdomains/download/rapporter/';
		if( 'ukm.dev' == UKM_HOSTNAME ) {
			$tmp_folder = '/tmp/rapporter/';
		} 
		$name = 'UKMRapport_'.$this->pl_id.'_'.$this->_sanName().'_'.date('dmYhis').'.csv';


		if ( !is_dir($tmp_folder) ) {
			mkdir($tmp_folder);
		}
		$file = fopen($tmp_folder.$name, 'x');
		if( false == $file) {
			echo '<h2>Failed to create file for writing.</h2>';
			return false;
		}
		$write_result = fwrite($file, $data);
		if( false == $write_result ) {
			echo '<h2>Failed to write to file.</h2>';
			return false;
		}
		fclose($file);

		$link = '//download.ukm.no/?folder=rapporter&filename='.urlencode($name);
		if( 'ukm.dev' == UKM_HOSTNAME ) {
			echo '<br><h4>File was written to '.$tmp_folder.$name.'</h4>';
		}
		return $link;
	}
	
	/********************************************************************************/
	/*									WORD (public)								*/
	/********************************************************************************/

	public function word_init($orientation='portrait',$name=false) {
		require_once('UKM/inc/word.inc.php');
		global $PHPWord;
		$section = $PHPWord->createSection(array('orientation' => $orientation,
			    'marginLeft' => 1100,
			    'marginRight' => 1100,
			    'marginTop' => 1100,
			    'marginBottom' => 1100));
		if(!$name)
			$name = $this->name;
		$properties = $PHPWord->getProperties();
		$properties->setCreator('UKM Norge'); 
		$properties->setCompany('UKM Norges arrangørsystem');
		$properties->setTitle('UKM-rapport '. ucfirst(str_replace('_',' ',$name)));
		$properties->setDescription('Rapport generert fra UKM Norges arrangørsystem'); 
		$properties->setCategory('UKM-rapporter');
		$properties->setLastModifiedBy('UKM Norge arrangørsystem');
		$properties->setCreated( time() );
		$properties->setModified( time() );

		// Definer noen styles
		$PHPWord->addFontStyle('f_p', array('size'=>10));
		$PHPWord->addParagraphStyle('p_p', array('spaceAfter'=>0, 'spaceBefore'=>0));
		$PHPWord->addParagraphStyle('p_bold', array('spaceAfter'=>0, 'spaceBefore'=>0));
		
		$PHPWord->addParagraphStyle('p_center', array('spaceAfter'=>0, 'spaceBefore'=>0, 'align'=>'center'));
				
		$PHPWord->addParagraphStyle('p_grp', array('align'=>'left', 'spaceAfter'=>300));
		$PHPWord->addParagraphStyle('p_h1', array('align'=>'left', 'spaceAfter'=>100));
		$PHPWord->addParagraphStyle('p_h2', array('align'=>'left', 'spaceAfter'=>100));
		$PHPWord->addParagraphStyle('p_h3', array('align'=>'left', 'spaceAfter'=>0, 'spaceBefore'=>100));
		$PHPWord->addParagraphStyle('p_h4', array('align'=>'left', 'spaceAfter'=>0, 'spaceBefore'=>100));
		$PHPWord->addFontStyle('f_grp', array('size'=>20, 'align'=>'left', 'bold'=>true));
		$PHPWord->addFontStyle('f_h1', array('size'=>16, 'align'=>'left', 'bold'=>true));
		$PHPWord->addFontStyle('f_h2', array('size'=>14, 'align'=>'left', 'bold'=>true));
		$PHPWord->addFontStyle('f_h3', array('size'=>12, 'align'=>'left', 'bold'=>true));
		$PHPWord->addFontStyle('f_h4', array('size'=>10, 'align'=>'left', 'bold'=>true));
		$PHPWord->addFontStyle('f_bold', array('bold'=>true, 'spaceAfter'=>0, 'spaceBefore'=>0));
		
		$PHPWord->addParagraphStyle('p_rapportIkonSpacer', array('spaceBefore'=>3000));
		$PHPWord->addParagraphStyle('p_rapportIkonSpacerLandscape', array('spaceBefore'=>1500));
		$PHPWord->addParagraphStyle('p_rapport', array('align'=>'center', 'spaceBefore'=>400));
		$PHPWord->addParagraphStyle('p_place', array('align'=>'center'));
		$PHPWord->addFontStyle('f_rapport', array('size'=>35, 'bold'=>true, 'color'=>'1e4a45'));
		$PHPWord->addFontStyle('f_place', array('size'=>25, 'bold'=>true, 'color'=>'1e4a45'));
		
		# Offset ganges med 0.05, 360 = 18 i Word-avsnitt-stil.
		$PHPWord->addParagraphStyle('p_diplom_navn', array('spaceAfter'=>0, 'spaceBefore'=>360, 'align'=>'center'));
		$PHPWord->addParagraphStyle('p_diplom_mellom', array('spaceAfter'=>0, 'spaceBefore'=>0, 'align'=>'center'));
		$PHPWord->addParagraphStyle('p_diplom_monstring', array('spaceAfter'=>0, 'spaceBefore'=>0, 'align'=>'center'));
		$PHPWord->addFontStyle('f_diplom_navn', array('size'=>25, 'bold'=>true, 'color'=>'1e4a45'));
		$PHPWord->addFontStyle('f_diplom_mellom', array('size'=>18, 'bold'=>false, 'color'=>'1e4a45'));
		$PHPWord->addFontStyle('f_diplom_monstring', array('size'=>18, 'bold'=>true, 'color'=>'1e4a45'));

		$PHPWord->addParagraphStyle('p_page_divider', array('spaceAfter'=>0, 'spaceBefore'=>0, 'align'=>'center'));
		$PHPWord->addFontStyle('f_page_divider', array('size'=>30, 'bold'=>true, 'color'=>'f3776f'));


		$orientation = $section->getSettings()->getOrientation();
		if($orientation == 'landscape')
			woText($section, '','rapportIkonSpacerLandscape');
		else
			woText($section, '','rapportIkonSpacer');
		$section->addImage('/home/ukmno/public_html/wp-content/plugins/UKMrapporter/UKM_logo.png', array('width'=>300, 'height'=>164, 'align'=>'center'));

		woText($section, ucfirst(str_replace('_',' ',$name)), 'rapport');
		woText($section, $this->m->g('pl_name').' ('.$this->m->g('season').')','place');
		$t = $this->frontInfo();
		if ($t) {
			woText($section, ''); // Adds an empty line before contacts
			woText($section, array_shift($t), 'diplom_mellom'); 
			foreach ($t as $v)
				woText($section, $v, 'diplom_monstring');
		}
		$section->addPageBreak();
		
		if(!isset($this->wordWithoutHeaders)) {
			// Add header
			$header = $section->createHeader();
			$HT = $header->addTable();
			$HT->addRow(720);
			if($orientation == 'landscape')
				$HT->addCell(10000)->addText('UKM-rapporter : '.utf8_decode($name), array('align'=>'left'));
			else
				$HT->addCell(5000)->addText('UKM-rapporter : '.utf8_decode($name), array('align'=>'left'));
			$HT->addCell(5000, array('align'=>'right'))->addText(date('d.m.Y H:i:s'), array('align'=>'right'), array('align'=>'right'));
			// Add footer
			$footer = $section->createFooter();
			$footer->addPreserveText('Side {PAGE} av {NUMPAGES}.', array('align'=>'right'));
		}	
		return $section;
	}

	/**
	 * frontInfo function
	 *
	 * Funksjon som kan overskrives hvis man vil ha mer info på forsiden,
	 * som f.eks kontaktinfo til lokalkontakten på tekniske prøver
	 * Skal returnere null/false (ingen tekst) eller et array med strings.
	 * Det første elementet i arrayet blir overskriften.
	 */
	public function frontInfo() {
		return false;
	}

	/**
	 * woWrite function
	 * 
	 * Generer excel-dokument av rapporten
	 *
	 * @access private
	 * @return string safename
	 */	
	public function woWrite(){
		global $objPHPWord;
		return woWrite('UKMRapport_'.$this->_sanName().'_'.date('dmYhis'));
	}
	
	public function excel_init($navn=false){
		global $objPHPExcel;
		require_once('UKM/inc/excel.inc.php');
		$objPHPExcel = new PHPExcel();

		// Endret parameter 22.11.12, navn fungerer nå som direction mens navn hentes fra klassen
		if($navn == 'portrait' || $navn == 'landscape') {
			$orientation = $navn;
			$navn = $this->name;
		} else {
			$orientation = 'portrait';
		}


		exorientation($orientation);

		$objPHPExcel->getProperties()->setCreator('UKM Norges arrangørsystem');
		$objPHPExcel->getProperties()->setLastModifiedBy('UKM Norges arrangørsystem');
		$objPHPExcel->getProperties()->setTitle('UKM-rapport '.ucfirst(str_replace('_',' ',$this->name)));
		$objPHPExcel->getProperties()->setKeywords('UKM-rapporter');
	
		## Sett standard-stil
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
	
		####################################################################################
		## OPPRETTER ARK
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->setActiveSheetIndex(0)->getTabColor()->setRGB('A0CF67');
	}
	/**
	 * exWrite function
	 * 
	 * Generer excel-dokument av rapporten
	 *
	 * @access private
	 * @return string safename
	 */	
	public function exWrite(){
		global $objPHPExcel;
		return exWrite($objPHPExcel,'UKMRapport_'.$this->_sanName().'_'.date('dmYhis'));
	}

	
	public function html_init($navn=false) {
		if(!$navn) {
			if( !empty( $this->navn ) ) {
				$navn = $this->navn;
			} else {
				$navn = ucfirst(str_replace('_',' ',$this->name));
			}
		}

		$t = $this->frontInfo();
		if ($t) {
			$heading = array_shift($t);
			$text = '';
			foreach ($t as $v)
				$text .= '<div class="kp">'.$v.'</div>';
		}

		return '<div class="rapportinfo">
				<img src="//ico.ukm.no/grafikk/UKM_logo.png" width="100" />
				<div class="navn">'.$navn.'</div>
				<div class="sted">'.$this->m->g('pl_name').' ('.$this->m->g('season').')</div>
				' .($t ? '<h3 class="heading">'.$heading.'</h3>'.$text : '').'
				</div>';
	}
}

?>
