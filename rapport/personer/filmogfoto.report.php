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
		return false;
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
        return false;
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
       echo '<h1>Innslag og personer som ikke skal tas bilde av eller filmes</h1>';
        $innslag_collection = $this->getMonstring()->getInnslag();

        echo 
            '<table class="table table-striped">'.
                '<thead>'.
                    '<tr>'.
                        '<th colspan="2"></th>'.
                        '<th colspan="3">Deltakeren</th>'.
                        '<th colspan="3">Forelder / foresatt</th>'.
                    '</tr>'.
                    '<tr>'.
                        '<th>Innslag</th>'.
						'<th>Type'.
						( $this->getMonstring()->getType() == 'land' ? ' og fylke' : '' ).
						'</th>'.
                        '<th>Navn</th>'.
                        '<th>Mobil</th>'.
                        '<th>Status</th>'.
                        '<th>Navn</th>'.
                        '<th>Mobil</th>'.
                        '<th>Status</th>'.
                    '</tr>'.
                '<thead>'.
                '<tbody>';
            
        foreach( $innslag_collection->getAll() as $innslag ) {
            if( !$innslag->getSamtykke()->harNei() ) {
                continue;
			}
			
			// Ikke godt nok testet til å implementeres. 19.05.2019
			/*
			$harNei = false;
			foreach( $innslag->getSamtykke()->getAll() as $person ) {
				if( 
					$innslag->getPersoner()->harVideresendtPerson( $person->getPerson() ) 
					&& 
						(
							$person->getStatus()->getId() == 'ikke_godkjent'
							||
							$person->getForesatt()->getStatus()->getId() == 'ikke_godkjent'
						)
				) {
					$harNei = true;
				}
			}
			if( !$harNei ) {
				continue;
			}
			*/

            foreach( $innslag->getSamtykke()->getAll() as $person ) {

                echo 
                    '<tr>'.
                        '<th>'. $innslag->getNavn().'</th>'.
						'<td>'. 
							$innslag->getType()->getNavn() .
							( $this->getMonstring()->getType() == 'land' ? '<br /><small>'.$innslag->getFylke()->getNavn().'</small>' : '' ).
						'</td>'.
                        '<td>'. $person->getNavn() .'</td>'.
                        '<td><span class="UKMSMS">'. $person->getMobil() .'</span></td>'.
                        '<td class="text-'.( $person->getStatus()->getId() != 'ikke_godkjent' ? 'success' : 'danger' ).'">'. 
							$person->getStatus()->getNavn() .
							( !$innslag->getPersoner()->harVideresendtPerson( $person->getPerson() ) ?
								'<br />(men er heller ikke videresendt)'
								: ''
							).
                        '</td>';                
                
                if( $person->getKategori()->getId() != '15o' ) {
                    echo
                        '<td>'. $person->getForesatt()->getNavn() .'</td>'.
                        '<td><span class="UKMSMS">'. $person->getForesatt()->getMobil() .'</span></td>'.
                        '<td class="text-'.( $person->getForesatt()->getStatus()->getId() != 'ikke_godkjent' ? 'success' : 'danger').'">'. 
                            $person->getForesatt()->getStatus()->getNavn() . 
                        '</td>';
                } else {
                    echo '<td></td>'.
                        '<td></td>'.
                        '<td></td>';
                }
                        
                    '</tr>';
            }
        }
        echo
                '</tbody>'.
            '<table>';

        #'<span class="UKMSMS">'. str_replace(' ', '', $element->value->mobil) .'</span> - ' .
        #'<a href="mailto:'.$element->value->epost .'" class="UKMMAIL">'. $element->value->epost .'</a>';
	}
}
?>