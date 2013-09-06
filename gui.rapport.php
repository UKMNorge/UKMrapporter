<?php
UKM_loader('contact|private');

if(class_exists('extended_rapport'))
	$r = new extended_rapport($_GET['rapport'],$_GET['kat']);
else
	$r = new valgt_rapport($_GET['rapport'],$_GET['kat']);
?>
<h2 id="rapportvalgheader">Rapportvalg 
<a href="#" id="visrapportvalg" style="display:none;">Vis rapportvalg</a>
<a href="#" id="skjulrapportvalg" style="display:none;">Skjul rapportvalg</a>
</h2>
<!--<a href="#" id="oppdaterrapport">Oppdater</a>-->
<div class="rapportvalg" id="rapportvalgene">
	<form action="#" id="UKMrapport" data-rapport="<?= $r->name ?>" data-kat="<?= $r->kat?>">
	
		<h3 id="hva">Hva</h3>
		<fieldset class="options">
			<div id="velgalleingen">
				Velg: 
				<a href="#" id="velgalle">alle</a> | <a href="#" id="velgingen">ingen</a>
			</div>
			<div class="seasonselector">
				<span id="seasonlabel">Sesong</span>
				<select name="season" id="optSeason">
					<?php foreach($r->get_season() as $s) { ?>
						<option value="<?= $s ?>" <?= ($s==get_option('season')?' selected="selected"':'')?>/><?= $s ?></li>
			 		<?php } ?>
				</select>
			</div>


			<?php
			$options = $r->get_options();
			if(is_array($options))
			foreach($options as $group => $grp_options){
				?>
				<ul class="optGrp" id="<?= $group?>">
					<li class="name"><?= $group?></li>
				<?php
					## MERKELIG TANKEGANG, SELECT ERSTATTER IKKE MANGE CHECKBOXES
					if(true) { #sizeof($grp_options) < 15) {
						foreach($grp_options as $key => $info) {
							if(empty($info['name'])){ ?>
								<br />
							<?php 
							} else { ?>
							<li class="option" id="<?= $info['name']?>">
							<label><input type="<?= $info['type']?>" name="options[]" <?= ($info['checked'] ? 'checked="checked"':'') ?> value="<?= $info['name']?>" /><?= $info['name'] ?></label>
							</li>
						<?php
							}
						}
					} else {
						$grp_options_as_text = '';
						foreach($grp_options as $key => $info) {
							$grp_options_as_text .= '<option value="'.$info['name'].'">'.$info['name'].'</option>';
						} ?>
						<li class="option" id="<?= $info['name']?>">
						<select name="options[]"><?= $grp_options_as_text ?></select>
						</li>
					<?php 
					} ?>
				</ul>
			<?php
			} ?>
		<div class="clear"></div>
		</fieldset>
<?php
	$formats = $r->get_formats();
	if(is_array($formats)){ ?>

		<h3 id="hvordan">Hvordan</h3>
		<fieldset>

			<?php
			foreach($formats as $group => $grp_formats){
				?>
				<ul class="optGrp" id="<?= $group?>">
					<li class="name"><?= $group?></li>
				<?php
				if(sizeof($grp_formats) < 15) {
					foreach($grp_formats as $key => $info) { ?>
						<li class="option" id="<?= $info['name']?>">
						<label><input type="<?= $info['type']?>" name="formats[]" <?= ($info['checked'] ? 'checked="checked"':'') ?>value="<?= $info['name']?>" /><?= $info['name'] ?></label>
						</li>
					<?php
					}
				} else {
					$format_options_as_text = '';
					foreach($grp_formats as $key => $info) {
						$format_options_as_text .= '<option value="'.$info['name'].'">'.$info['name'].'</option>';
					} ?>
					<li class="option" id="<?= $info['name']?>">
					<select name="formats[]"><?= $format_options ?></select>
					</li>
				<?php } ?>
				</ul>
			<?php
			} ?>
		</fieldset>
<?php }
	$helpers = $r->get_helpers();
	if(is_array($helpers) && sizeof($helpers) > 0){ ?>

		<h3 id="hvordan">Hjelpefiler</h3>
		Til denne rapporten finnes det <?= sizeof($helpers)?> hjelpefiler. 
		Du må ikke bruke de, men til enkelte formål kan du trenge de. For eksempel ved diplomfletting
		<br />
		<fieldset>
			<?php foreach( $helpers as $url => $name ) { ?>
				<a href="<?= $url ?>" target="_blank">Last ned <?= $name ?></a><br />
			<?php
			}?>
		</fieldset>
	<?php 
	} ?>
		<div class="clear"></div>
		<input type="button" id="filterReport" value="Generer rapport" />
		<div class="clear"></div>
	</form>
</div>
<ul class="actions" style="display:none;">
	<li id="skrivut"><?= UKMN_ico('print', 40)?><div class="text">Skriv ut</div></li>
	<li id="word"><?= UKMN_ico('word-mac', 40)?><div class="text">Last ned som Word-dokument</div></li>
	<li id="excel"><?= UKMN_ico('excel-mac', 40)?><div class="text">Last ned som Excel-dokument</div></li>
</ul>
<ul class="contact_actions" style="display:none;">
	<li id="mail"><?= UKMN_ico('mail', 40)?><div class="text">Send e-post til alle i rapporten</div></li>
	<li id="sms"><?= UKMN_ico('mobile', 40)?><div class="text">Send SMS til alle i rapporten</div></li>
</ul>

<div class="clear"></div>
<div id="report_container_contact"></div>
<div id="report_container_word"></div>
<div id="report_container">
	<div id="velg_detaljer">Velg hvilke detaljer som skal være med, og trykk "generer rapport"</div>
</div>