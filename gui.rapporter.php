<?php 
$raport_icon_size = 50; 
$category_icon_size = 32;

?>
<h1>Rapporter</h1>

<span><?= UKMN_ico('hus', $category_icon_size)?><h2 class="rapport_kategori">Mønstring</h2></span>
<ul class="rapportcontainer" data-kat="monstring">
	<?php
	if($_SERVER['REMOTE_ADDR']=='81.0.146.162' || $_SERVER['REMOTE_ADDR']=='127.0.0.1') { ?>
	<li class="clickable rapport_direct" data-target="?page=UKMrapport_admin&stat=home">
		<?= UKMN_icoAlt('graph', "Statistikk", $raport_icon_size ) ?>
		<div class="title">Statistikksenter</div>
		<span>Alt om din mønstring - i tall</span>
	</li>
	<?php } ?>
	
	<li class="clickable rapport" data-file="statistikk">
		<?= UKMN_icoAlt('graph', "Statistikk", $raport_icon_size ) ?>
		<div class="title">Statistikk</div>
		<span>Statistikk nasjonalt, regionalt og lokalt. Alt du trenger!</span>
	</li>
<?php	if(get_option('site_type') == 'fylke') {?>
	<li class="clickable rapport" data-file="videresendingsskjema">
		<?= UKMN_icoAlt('buss', "Oppsummering digitalt infoskjema", $raport_icon_size ) ?>
		<div class="title">Videresendingsskjema</div>
		<span>Viser hva kommunene har skrevet i videresendingsskjemaet</span>
	</li>
<?php } ?>
<?php	if(get_option('site_type') == 'fylke') {?>
	<li class="clickable rapport" data-file="lokalkontakter">
		<?= UKMN_icoAlt('contact', "Lokalkontakter", $raport_icon_size ) ?>
		<div class="title">Lokalkontakter</div>
		<span>Lister ut alle lokalkontakter i fylket</span>
	</li>
<?php } ?>

<div class="clear"></div>
</ul>


<span><?= UKMN_ico('people', $category_icon_size)?><h2 class="rapport_kategori">Personer</h2></span>
<ul class="rapportcontainer" data-kat="personer">
	<li class="clickable rapport" data-file="alle_innslag">
		<?= UKMN_icoAlt('group-config', 'Alle innslag', $raport_icon_size ) ?>
		<div class="title">Alle innslag</div>
		<span>Alfabetisk oversikt over alle innslag på mønstringen, tekniske krav og konferansiertekster</span>
	</li>
	<li class="clickable rapport" data-file="kontaktlister">
		<?= UKMN_icoAlt('contact', "Kontaktlister", $raport_icon_size ) ?>
		<div class="title">Kontaktlister</div>
		<span>Oversikt over alle personer på mønstringen</span>
	</li>
	<li class="clickable rapport" data-file="duplikate_mobilnummer">
		<?= UKMN_icoAlt('banana-twins', "Duplikate mobilnummer", $raport_icon_size ) ?>
		<div class="title">Duplikate mobilnummer</div>
		<span>Liste over mobilnummer på din mønstring som brukes av to eller flere personer</span>
	</li>
	<li class="clickable rapport" data-file="inn_og_utlevering">
		<?= UKMN_icoAlt('tasklist', "Inn- og utlevering", $raport_icon_size ) ?>
		<div class="title">Inn- og utlevering</div>
		<span>Hold enkelt oversikt over inn- og utlevering av kunst og/eller film på din mønstring</span>
	</li>
<?php	if(get_option('site_type') == 'fylke') {?>
	<li class="clickable rapport" data-file="turneliste">
		<?= /**/ UKMN_icoAlt('adium', "Duplikate deltakere i fylket", $raport_icon_size ) ?>
		<div class="title">Duplikate deltakere i fylket</div>
		<span>Viser innslag som deltar på flere av dine lokalmønstringer</span>
	</li>
<?php } ?>
	<li class="clickable rapport" data-file="diplomer">
		<?= UKMN_icoAlt('diplom', "Diplomer", $raport_icon_size ) ?>
		<div class="title">Diplomer</div>
		<span>Autogenerer worddokument med alle deltakere som skrives ut direkte på de trykte diplomene</span>
	</li>


<div class="clear"></div>
</ul>

<span><?= UKMN_ico('chart', $category_icon_size)?><h2 class="rapport_kategori">Program</h2></span>
<ul class="rapportcontainer" data-kat="program">
	<li class="clickable rapport" data-file="program">
		<?= UKMN_icoAlt('list', "Sannsynligvis OK!", $raport_icon_size ) ?>
		<div class="title">Program</div>
		<span>Sammen med papermill-omslaget er dette alt du trenger!</span>
	</li>
	<li class="clickable rapport" data-file="kunstkatalog">
		<?= UKMN_icoAlt('art-supplies', "Kunstkatalog", $raport_icon_size ) ?>
		<div class="title">Kunstkatalog</div>
		<span>All info du trenger for å lage en kunstkatalog, unntatt bilder.<br />Last ned excel-rapporten for å flette utstillingsetiketter i word</span>
	</li>
	<li class="clickable rapport" data-file="tekniske_prover">
		<?= UKMN_icoAlt('settings', "Tekniske prøver", $raport_icon_size ) ?>
		<div class="title">Tekniske prøver</div>
		<span>Skreddersy en oversikt til dine teknikere for en best mulig mønstring</span>
	</li>
	<li class="clickable rapport" data-file="analogt_juryskjema">
		<?= UKMN_icoAlt('gavel', "Juryskjema for utskrift", $raport_icon_size ) ?>
		<div class="title">Juryskjema</div>
		<span>Juryskjema for utskrift
		</span>
	</li>
<div class="clear"></div>
</ul>


