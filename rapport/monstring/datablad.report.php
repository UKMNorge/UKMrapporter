<?php
require_once('UKM/monstringer.class.php');
	
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
		
		$this->_postConstruct();	
	}
	
	
	public function generateExcel() {
		global $objPHPExcel;
		//$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		exSheetName('LOKALKONTAKTER');

		$row = 1;
	
		exCell('A'.$row, 'Navn','bold');
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
	public function generateWord() {
		global $PHPWord;		
		$section = $this->word_init('landscape');

		$lokalmonstringer = stat_monstringer_v2::utenGjester( 
			stat_monstringer_v2::getAllByFylke(
				$this->getMonstring()->getFylke(),
				$this->getMonstring()->getSesong()
			)
		);
		
		foreach( $lokalmonstringer as $lokalmonstring ) {
			woText($section, $lokalmonstring->getNavn(), 'h1');
			
			if( $this->getMonstring()->harSkjema() ) {
				$skjema = $lokalmonstring->getSkjema()->getQuestionsWithAnswers();

				foreach( $skjema as $element ) {
					if( $element->type == 'overskrift' ) {
						woText($section, $element->title, 'h3');
					} else {
						woText($section, $element->title .':', 'bold');
						switch( $element->type ) {
							case 'janei':
								woText($section, $element->value == 'true' ? 'JA' : 'NEI' );
								break;
							case 'kontakt':
								woText($section, $element->value->navn . ' - '. $element->value->mobil .' - '. $element->value->epost );
								break;
							default:
								woText($section, $element->value);
								break;
						}
					}
				}
			}
			
			woText($section, 'Videresendte', 'h2');
			
			$tab = $section->addTable(array('align'=>'center'));
			$tab->addRow();
			
			$bredde_innslag = 3200;
			$bredde_type = 2000;
			$bredde_kontakt = 3600;
			$bredde_deltakere = 3600;
			$bredde_titler = 3600;
			$bredde_varighet = 1000;
			
			woText( $tab->addCell( $bredde_innslag ), 'Innslag', 'bold');
			woText( $tab->addCell( $bredde_type ), 'Type', 'bold');
			woText( $tab->addCell( $bredde_kontakt ), 'Kontaktperson', 'bold');
			woText( $tab->addCell( $bredde_deltakere ), 'Deltakere', 'bold');
			woText( $tab->addCell( $bredde_titler ), 'Titler', 'bold');
			woText( $tab->addCell( $bredde_varighet ), 'Varighet', 'bold');

			foreach( $lokalmonstring->getInnslag()->getAll() as $innslag ) {
				if( $this->getMonstring()->getInnslag()->har( $innslag->getId() ) ) {

					woText( $tab->addCell( $bredde_innslag ), $innslag->getNavn() );
					woText( $tab->addCell( $bredde_type ), $innslag->getType() );
					woText( $tab->addCell( $bredde_kontakt ), 
						$innslag->getKontaktperson()->getNavn() .' - '.
						$innslag->getKontaktperson()->getMobil() .' - '.
						$innslag->getKontaktperson()->getEpost()
					);

					if( $innslag->getType()->harTitler() ) {
						// PERSONER
						$personer = $this->getMonstring()->getInnslag()->get( $innslag->getId() )->getPersoner()->getAllVideresendt();
						if( sizeof( $personer ) == 0 ) {
							woText( $tab->addCell( $bredde_deltakere ), 'INGEN DELTAKERE VIDERESENDT', 'bold');
						}
						foreach( $personer as $person ) {
							$celle = $tab->addCell( $bredde_deltakere );
							woText( $celle, $person->getNavn() );
							woText( $celle, $person->getMobil() .' - '.	$person->getEpost() );
						}

						// TITLER
						$titler = $this->getMonstring()->getInnslag()->get( $innslag->getId() )->getTitler()->getAll();
						if( sizeof( $titler ) == 0 ) {
							woText( $tab->addCell( $bredde_titler ), 'INGEN TITLER/VERK VIDERESENDT', 'bold');
						}
						foreach( $titler as $tittel ) {
							$celle = $tab->addCell( $bredde_titler );
							woText( $celle, $tittel->getTittel() );
							woText( $celle, $tittel->getParentes() );
						}

						woText( $tab->addCell( $bredde_varighet ), $innslag->getVarighet() );
					} else {
						woText( $tab->addCell( $bredde_deltakere ), ' - ' );
						woText( $tab->addCell( $bredde_titler ), $innslag->getPersoner()->getSingle()->getRolle() );
						woText( $tab->addCell( $bredde_varighet ), ' - ' );
					}
				}
			}
			$section->addPageBreak();
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
		echo $this->html_init('Oppsummert videresending');
		
		$lokalmonstringer = stat_monstringer_v2::utenGjester( 
			stat_monstringer_v2::getAllByFylke(
				$this->getMonstring()->getFylke(),
				$this->getMonstring()->getSesong()
			)
		);
		
		foreach( $lokalmonstringer as $lokalmonstring ) {
			echo '<h1>'. $lokalmonstring->getNavn().'</h1>';
			
			if( $this->getMonstring()->harSkjema() ) {
				$skjema = $lokalmonstring->getSkjema()->getQuestionsWithAnswers();

				foreach( $skjema as $element ) {
					if( $element->type == 'overskrift' ) {
						echo '<h3>'. $element->title .'</h3>';
					} else {
						echo '<strong>'. $element->title .': </strong>';
						switch( $element->type ) {
							case 'janei':
								echo $element->value == 'true' ? 'JA' : 'NEI';
								break;
							case 'kontakt':
								echo $element->value->navn . ' - '.
									'<span class="UKMSMS">'. str_replace(' ', '', $element->value->mobil) .'</span> - ' .
									'<a href="mailto:'.$element->value->epost .'" class="UKMMAIL">'. $element->value->epost .'</a>';
								 break;
							default:
								echo $element->value;
								break;
						}
						echo '<br />';
					}
				}
			}
			echo '<h3>Videresendte</h3>'.
				'<table class="table">'.
					'<thead>'.
						'<tr>'.
							'<th>Innslag</th>'.
							'<th>Type</th>'.
							'<th>Kontaktperson</th>'.
							'<th>Deltakere</th>'.
							'<th>Titler</th>'.
							'<th>Varighet</th>'.
						'</tr>'.
					'</thead>'.
					'<tbody>';
			foreach( $lokalmonstring->getInnslag()->getAll() as $innslag ) {
				if( $this->getMonstring()->getInnslag()->har( $innslag->getId() ) ) {
					echo
						'<tr>'.
							'<td>'. $innslag->getNavn() .'</td>'.
							'<td>'. $innslag->getType() .'</td>'.
							'<td>'. 
								$innslag->getKontaktperson()->getNavn() .
								'<br />'.
								'<span class="UKMSMS small">'. str_replace(' ', '', $innslag->getKontaktperson()->getMobil() ) .'</span>'.
							'</td>';

					if( $innslag->getType()->harTitler() ) {
						// PERSONER
						echo '<td>';
						$personer = $this->getMonstring()->getInnslag()->get( $innslag->getId() )->getPersoner()->getAllVideresendt();
						if( sizeof( $personer ) == 0 ) {
							echo '<span class="alert-error alert-danger">Ingen personer videresendt</span>';
						}
						foreach( $personer as $person ) {
							echo $person->getNavn() .'<br />'.
								'<span class="UKMSMS small">'. str_replace(' ', '', $person->getMobil() ) .'</span>'.
								'<br />';
						}
						echo '</td>';

						// TITLER
						echo '<td>';
						$titler = $this->getMonstring()->getInnslag()->get( $innslag->getId() )->getTitler()->getAll();
						if( sizeof( $titler ) == 0 ) {
							echo '<span class="alert-error alert-danger">Ingen titler/verk videresendt</span>';
						}
						foreach( $titler as $tittel ) {
							echo $tittel->getTittel() .'<br />'.
								'<small>'. $tittel->getParentes() .'</small>'.
								'<br />';
						}
						echo '</td>';
						
						echo '<td>'. $innslag->getVarighet() .'</td>';
					} else {
						echo '<td> - </td>';
						echo '<td class="small">'. $innslag->getPersoner()->getSingle()->getRolle() .'</td>';
						echo '<td> - </td>';
					}
						
						
					echo '</tr>';
				}
			}
			echo '</tbody>'.
				'</table>';

			echo '<div style="page-break-after: always;"></div>';
		}
		
	}
}
?>