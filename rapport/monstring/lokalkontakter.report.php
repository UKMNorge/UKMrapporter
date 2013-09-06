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
		$g = $this->optGrp('i','Hvilke kontaktpersoner');
		$this->opt($g, 'i_kontakter', 'Alle som er registrert på lokalsidene');
		$this->opt($g, 'i_brukere', 'Kontaktpersoner registrert som bruker (de som ligger i passordlisten)');
		
		$this->addresses = array();
		
		$this->_postConstruct();	
	}
	
	
	public function generateExcel() {
		global $all_contacts;
		if($this->show('i_kontakter'))
			$this->_kontakter();
		if($this->show('i_brukere'))
			$brukere = $this->_brukere();

		UKM_loader('excel');
		global $objPHPExcel;
		$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		
		exSheetName('LOKALKONTAKTER');

		$row = 1;
	
		exCell('A'.$row, 'Navn','bold');
		exCell('B'.$row, 'Mønstring','bold');
		exCell('C'.$row, 'E-post','bold');
		exCell('D'.$row, 'Mobil','bold');
		exCell('E'.$row, 'Tittel','bold');
		foreach( $all_contacts as $place => $contacts ) {
			foreach($contacts as $mail => $conarr) {
				$conarr = array_unique($conarr);
				foreach($conarr as $con) {
					$row++;
					exCell('A'.$row, $con['name']);
					exCell('B'.$row, $con['place']);
					exCell('C'.$row, $con['mail']);
					exCell('D'.$row, $con['phone']);
					exCell('E'.$row, $con['title']);
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
	public function generateWord() {
		global $all_contacts;
		if($this->show('i_kontakter'))
			$this->_kontakter();
		if($this->show('i_brukere'))
			$brukere = $this->_brukere();

		UKM_loader('word');
		global $PHPWord;		
		$section = $this->word_init('landscape');

		$tab = $section->addTable(array('align'=>'center'));
		$tab->addRow();

		$c = $tab->addCell(2500);
		woText($c, 'Navn','bold');

		$c = $tab->addCell(2500);
		woText($c, 'Mønstring','bold');

		$c = $tab->addCell(3500);
		woText($c, 'E-post','bold');

		$c = $tab->addCell(1500);
		woText($c, 'Mobil','bold');

		$c = $tab->addCell(1000);
		woText($c, 'Tittel','bold');

		foreach( $all_contacts as $place => $contacts ) {
			foreach($contacts as $mail => $conarr) {
				$conarr = array_unique($conarr);
				foreach($conarr as $con) {
					$tab->addRow();
					
					$c = $tab->addCell(3000);
					woText($c, $con['name']);
			
					$c = $tab->addCell(3000);
					woText($c, $con['place']);
			
					$c = $tab->addCell(5000);
					woText($c, $con['mail']);
			
					$c = $tab->addCell(1500);
					woText($c, $con['phone']);
			
					$c = $tab->addCell(3000);
					woText($c, $con['title']);
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
		echo $this->html_init('Lokalkontakter i fylket');

		global $all_contacts;
		if($this->show('i_kontakter'))
			$this->_kontakter();
		if($this->show('i_brukere'))
			$brukere = $this->_brukere();

		?>
		<table>
			<tr>
				<th>Navn</th>
				<th>Mønstring</th>
				<th>E-post</th>
				<th>Mobil</th>
				<th>Tittel</th>
			</tr>
		<?php
		foreach( $all_contacts as $place => $contacts ) {
			foreach($contacts as $mail => $conarr) {
				$conarr = array_unique($conarr);
				foreach($conarr as $con) {?>
					<tr>
						<td><?= $con['name']?></td>
						<td><?= $con['place']?></td>
						<td><?= contact_mail($con['mail'])?></td>
						<td><?= contact_sms($con['phone'])?></td>
						<td><?= $con['title'] ?></td>
					</tr>
				<?php
				}
			}
		}?>
		</table>
		<?php
	}
	
	
	/**
	 * _kontakter function
	 * 
	 * Henter ut alle kontaktpersoner tilknyttet lokalmønstringene i fylket
	 *
	 * @access private
	 * @return void
	 */	
	private function _kontakter(){
		global $all_contacts;
		$m = new monstring(get_option('pl_id'));
		$monstringer = $m->hent_lokalmonstringer();
		
		foreach($monstringer as $plid) {
			$pl = new monstring($plid);
			$kontakter = $pl->kontakter();
			
			foreach($kontakter as $kontakt) {
				$all_contacts[$pl->g('pl_name')][strtolower($kontakt->get('email'))][]
						  = array('name' => $kontakt->get('name'),
								  'mail' => $kontakt->get('email'),
								  'phone'=> $kontakt->get('tlf'),
								  'title'=> $kontakt->get('title'),
								  'place'=> $pl->g('pl_name'));
			}
		}
		return $return;
	}
	
	/**
	 * _brukere function
	 * 
	 * Henter ut alle brukere fra fylket og returnerer deres info (brukernavn + e-post)
	 *
	 * @access private
	 * @return void
	 */	
	private function _brukere(){
		global $wpdb, $all_contacts;
		$m = new monstring(get_option('pl_id'));
		$fylke = (int)$m->g('fylke_id');

		$qry = $wpdb->prepare("SELECT * FROM `ukm_brukere` WHERE `b_fylke` = '%d' ORDER BY `b_name` ASC", $fylke);
		$res = $wpdb->get_results($qry, ARRAY_A);
		foreach( $res as $array_key => $user ) {
			$place = new kommune_monstring($user['b_kommune'], get_option('season'));
			$pl = $place->monstring_get();
			
			$user['name'] = $user['b_name'];
			$user['mail'] = $user['b_email'];
			$user['place'] = $pl->g('pl_name');
			$user['phone'] = '';
			$user['title'] = 'Hentet fra passordliste';

			$all_contacts[$pl->g('pl_name')][strtolower($user['mail'])][] = $user;

			$this->addresses[] = $user['b_email'];
		}
	}
}
?>