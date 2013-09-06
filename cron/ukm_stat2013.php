<?php
	$SEASON = 2013;
	require_once('UKM/monstring.class.php');
	require_once('UKM/sql.class.php');
	

	$monstringer = new monstringer();
	$monstringer->etter_sesong($SEASON);
	
	var_dump($monstringer);

?>