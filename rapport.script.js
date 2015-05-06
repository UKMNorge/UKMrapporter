google.load('visualization', '1.0', {'packages':['corechart']});
function drawChart(container, data, options) {
	var chart = new google.visualization.LineChart(document.getElementById(container));
	chart.draw(data, options);
}
function drawPie(container, data, options) {
	var chart = new google.visualization.PieChart(document.getElementById(container));
	chart.draw(data, options);
}
function drawCombo(container, data, options) {
	var chart = new google.visualization.ColumnChart(document.getElementById(container));
	chart.draw(data, options);
}
var vistrapportvalgfor = false;
jQuery(document).ready(function(){
	
	// Aktiver rapportfunksjonalitet
	jQuery('.rapport').click(function(){
		window.location.href = 'http://'+window.location.hostname +window.location.pathname 
							+  '?page=UKMrapport_admin'
							+  '&rapport='+ jQuery(this).attr('data-file')
							+  '&kat=' + jQuery(this).parents('ul').attr('data-kat');
	});
	jQuery('.rapport_direct').click(function(){
		window.location.href = 'http://'+window.location.hostname +window.location.pathname 
							+ jQuery(this).attr('data-target')
							;
	});
	
	// RE-GENERER EN RAPPORT
	jQuery('#filterReport, #oppdaterrapport').click(function(){
		jQuery('#skjulrapportvalg').hide();
		jQuery('#visrapportvalg').slideDown();
		if(!vistrapportvalgfor) {
			jQuery('#visrapportvalg').effect("highlight", {color: '#f3776f'}, 2500);
			vistrapportvalgfor = true;
		}
		jQuery('#rapportvalgene').slideUp();
		var data = 'action=UKMrapport_ajax'
				 + '&get='+jQuery('#UKMrapport').attr('data-rapport')
				 + '&kat='+jQuery('#UKMrapport').attr('data-kat')
				 + '&'+jQuery('#UKMrapport').serialize();
		jQuery('#report_container').show();
		jQuery('#report_container').html('<span class="loading"><img src="http://ico.ukm.no/loading.gif" width="32" /><div>Vennligst vent, genererer rapport...</div></span>');
		jQuery('#report_container_word').html('');

		jQuery.post(ajaxurl, data, function(response){
			jQuery('#report_container').html(response);
			jQuery('ul.actions').slideDown();
			jQuery('ul.contact_actions').slideDown();
			registerUKMSMS('#report_container');
		});
	});

	jQuery('.actions > li#word').click(function(){
		var data = 'action=UKMrapport_ajax'
				 + '&get='+jQuery('#UKMrapport').attr('data-rapport')
				 + '&kat='+jQuery('#UKMrapport').attr('data-kat')
				 + '&word=true'
				 + '&'+jQuery('#UKMrapport').serialize();

		jQuery('#report_container').hide();
		jQuery('#report_container_word').html('<span class="loading"><img src="http://ico.ukm.no/loading.gif" width="32" /><div>Vennligst vent, gjør klar Word-rapport for nedlasting…</div></span>');
		
		jQuery.post(ajaxurl, data, function(response){
			jQuery('#report_container_word').html(response);
			link = jQuery('#downloadLink').attr('href');
			if(link !== undefined && link !== null)
				window.location.href = link;
		});
	});


	/// EXCEL-RAPPORT
	jQuery('.actions > li#excel').click(function(){
		var data = 'action=UKMrapport_ajax'
				 + '&get='+jQuery('#UKMrapport').attr('data-rapport')
				 + '&kat='+jQuery('#UKMrapport').attr('data-kat')
				 + '&excel=true'
				 + '&'+jQuery('#UKMrapport').serialize();

		jQuery('#report_container').hide();
		jQuery('#report_container_word').html('<span class="loading"><img src="http://ico.ukm.no/loading.gif" width="32" /><div>Vennligst vent, gjør klar Excel-rapport for nedlasting...</div></span>');
		
		jQuery.post(ajaxurl, data, function(response){
			jQuery('#report_container_word').html(response);
			link = jQuery('#downloadLink').attr('href');
			if(link !== undefined && link !== null)
				window.location.href = link;
		});
	});


	jQuery('li.option, .actions > li, .contact_actions > li').addClass('clickable');

	
	jQuery('#visrapportvalg').click(function(){
		jQuery('#rapportvalgene').slideDown();
		jQuery('ul.actions').slideUp();
		jQuery('ul.contact_actions').slideUp();
		jQuery(this).hide();
		jQuery('#skjulrapportvalg').show();
	});
	
	jQuery('#skjulrapportvalg').click(function(){
		jQuery('#rapportvalgene').slideUp();
		jQuery('ul.actions').slideDown();
		jQuery('ul.contact_actions').slideDown();
		jQuery(this).hide();
		jQuery('#visrapportvalg').show();
	});
	
	jQuery('#velgalle').click(function(){
		jQuery('fieldset.options li.option input[type=checkbox]').attr('checked','checked');
		return false;
	});
	
	jQuery('#velgingen').click(function(){
		jQuery('fieldset.options li.option input[type=checkbox]').removeAttr('checked');
		return false;
	});

	jQuery('.actions > li#skrivut').click(function(){
		var data = 'action=UKMrapport_countPrint'
				 + '&get='+jQuery('#UKMrapport').attr('data-rapport')
				 + '&kat='+jQuery('#UKMrapport').attr('data-kat')
				 + '&log=print'
				 + '&'+jQuery('#UKMrapport').serialize();
		
		jQuery.post(ajaxurl, data, function(response){});	
		jQuery('#report_container').printArea({mode: 'popup', popClose: true});
	});

	jQuery('#avbrytvisrapport').live('click',function(){
		jQuery('#report_container').slideDown();
		jQuery('#report_container_contact').slideUp();
	});

	jQuery('.contact_actions > li#mail').click(function(){
		var data = 'action=UKMrapport_countPrint'
				 + '&get='+jQuery('#UKMrapport').attr('data-rapport')
				 + '&kat='+jQuery('#UKMrapport').attr('data-kat')
				 + '&log=mail'
				 + '&'+jQuery('#UKMrapport').serialize();
		
		jQuery.post(ajaxurl, data, function(response){});
		var mailarray = new Array();
		jQuery('.UKMMAIL').each(function(){
			if(jQuery.inArray(jQuery(this).html(), mailarray)==-1 && jQuery(this).html()!=='')
				mailarray.push(jQuery(this).html());
		});
		jQuery('#report_container_contact').html('<h1>Send e-post</h1>'
												+'<strong>Det er '+mailarray.length+' e-postadresser i denne rapporten</strong>'
												+' '
												+'<a href="mailto:'+mailarray.join(';')+'">Send e-post til disse</a>'
												+'<br /><br />'
												+'<strong>Hvis e-postprogrammet ditt ikke gjenkjenner mottakerne </strong> '
												+'(prøver å lese alle som én adresse), '
												+'<a href="mailto:'+mailarray.join(',')+'">kan du prøve å klikke her</a>'
												+'<br /><br />'
												+'<a href="#" id="avbrytvisrapport">avbryt, vis rapport</a>');
		jQuery('#report_container').slideUp();
		jQuery('#report_container_contact').slideDown();
	});

	jQuery('.contact_actions > li#sms').click(function(){
		var data = 'action=UKMrapport_countPrint'
				 + '&get='+jQuery('#UKMrapport').attr('data-rapport')
				 + '&kat='+jQuery('#UKMrapport').attr('data-kat')
				 + '&log=sms'
				 + '&'+jQuery('#UKMrapport').serialize();
		
		jQuery.post(ajaxurl, data, function(response){});
		var smsarray = new Array();
		antallTelefonnummer = 0;
		jQuery('.UKMSMS').each(function(){
			antallTelefonnummer++;
			if(jQuery.inArray(jQuery(this).find('a').html(), smsarray)==-1 && jQuery(this).find('a').html()!=='')
				smsarray.push(jQuery(this).find('a').html());
		});
		jQuery('#report_container_contact').html('<h1>Send SMS</h1>'
												+'<strong>Rapporten inneholder '+ antallTelefonnummer +' telefonnummer, hvorav '
													+ smsarray.length +' unike</strong> '
												+'<form method="post" action="admin.php?page=UKMSMS_gui">'
												+'<input type="hidden" name="UKMSMS_recipients" value="'+smsarray.join(',')+'" />'
												+'<input type="submit" value="Jeg vil skrive en SMS til disse '+ smsarray.length +' "/>'
												+'</form>'
												+(smsarray.length < antallTelefonnummer ? 
													'<br /><br />'
													+'Hvis det er færre unike nummer enn totalt antall telefonnummer, skyldes dette ofte at kontaktperson oppgir'
													+'<br />'
													+' samme mobilnummer på flere personer i innslaget'
													+'<br />'
													+'<a href="?page=UKMrapport_admin&rapport=duplikate_mobilnummer&kat=personer">Vis rapport for duplikate telefonnummer</a>'
													: '')
												+'<br /><br />'
												+'<a href="#" id="avbrytvisrapport">avbryt, vis rapport</a>');
		jQuery('#report_container').slideUp();
		jQuery('#report_container_contact').slideDown();
	});
});


/* VIS UKM-TV I RAPPORTER */
/* Må bruke JS, da direktelasting gjør siden super-treg */
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