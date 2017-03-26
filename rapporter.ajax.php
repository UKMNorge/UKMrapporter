<?php
function UKMrapport_ajax(){
	require_once('class.rapport.php');
	require_once('rapport/'.$_POST['kat'].'/'.$_POST['get'].'.report.php');
	
	if(class_exists('extended_rapport'))
		$r = new extended_rapport($_POST['get'],$_POST['kat']);
	else
		$r = new valgt_rapport($_POST['get'],$_POST['kat']);

	$season = $r->setSeason($_POST['season'], get_option('season'));
	if($season==false)
		die('<h3>Beklager, finner ikke data for '.$_POST['season'].'-sesongen</h3>');
	$options = $r->setShow($_POST['options']);
	$formats = $r->setShowFormat($_POST['formats']);
	
	if(isset($_POST['word']))
		$log = 'word';
	elseif(isset($_POST['excel']))
		$log = 'excel';
	elseif(isset($_POST['csv']))
		$log = 'csv';
	else
		$log = 'skjerm';
	
	$qry = new SQLins('log_rapporter_format');
	$qry->add('f_type', $log);
	$qry->add('f_rapport', $_POST['get']);
	$qry->add('f_pl_id', get_option('pl_id'));
	$qry->add('f_season', $_POST['season']);
	$qry->run();
	
	if(!$options)
		die('Beklager, ingen info er valgt. Endre rapportvalg og trykk generer rapport på nytt');	
	
	if(isset($_POST['word'])) {
		$link = $r->generateWord();
	
		die('<h2>Rapport klar!</h2>'
			.'Du skal nå få opp spørsmål om å laste ned rapporten. '
			.'Hvis dette ikke skjer i løpet av de neste 5 sekundene kan du høyreklikke og velge &quot;lagre mål som&quot; '
			.'<a href="'.$link.'" id="downloadLink">her</a>');
	}
	
	if(isset($_POST['excel'])) {
		$link = $r->generateExcel();
		
		if(!$link)
			die('<h2>Beklager!</h2>'
				.'Rapporten finnes ikke i excel-format');
	
		die('<h2>Rapport klar!</h2>'
			.'Du skal nå få opp spørsmål om å laste ned rapporten. '
			.'Hvis dette ikke skjer i løpet av de neste 5 sekundene kan du høyreklikke og velge &quot;lagre mål som&quot; '
			.'<a href="'.$link.'" id="downloadLink">her</a>');
	}

	if(isset($_POST['csv'])) {
		$link = $r->generateCSV();

		if( 'ukm.dev' == UKM_HOSTNAME ) {
			die(); // generateCSV() skriver hvor filen er lagret i dev-modus.
		}
		if( !$link ) {
			die('<h2>Beklager!</h2>'
				.'Klarte ikke å generere rapporten til deg.');
		}
		die('<h2>Rapport klar!</h2>'
			.'Du skal nå få opp spørsmål om å laste ned rapporten. '
			.'Hvis dette ikke skjer i løpet av de neste 5 sekundene kan du høyreklikke og velge &quot;lagre mål som&quot; '
			.'<a href="'.$link.'" id="downloadLink">her</a>');
	}

	die($r->generate());
}
?>