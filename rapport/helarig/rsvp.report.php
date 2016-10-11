<?php

require_once('UKM/monstring.class.php');
require_once(ABSPATH.'wp-content/plugins/UKMrsvp_admin/class/SecretFinder.php');
require_once(ABSPATH.'wp-content/plugins/UKMrsvp_admin/class/EventManager.php');
require_once('UKM/inc/twig-admin.inc.php');

class valgt_rapport extends rapport {

	var $events = false;
	var $eventmanager = false;
	
	private function _owner() {
		$site_type = get_option('site_type');
		if ($site_type == 'kommune' || $site_type == 'fylke') {
			return get_option('pl_id');
		} else {
			return 'UKMNorge';
		}
	}
	
	private function _eventManager() {
		if( false == $this->eventmanager ) {
			$api_key = 'ukmno_rsvp';
			$secretFinder = new SecretFinder();
			$this->eventmanager = new EventManager($api_key, $secretFinder->getSecret($api_key));
		}
		return $this->eventmanager;
	}
	
	private function _events() {
		if( false == $this->events ) {
			$events = $this->_eventManager()->fetchEvents($this->_owner());
			$this->events = $events->data;
		}
		
		return $this->events;
	}
	
	private function _find( $status, $eventId ) {
		switch( $status ) {
			case 's_kommer':
				$data = $this->_eventManager()->findAttending($eventId, $this->_owner());
				if( false == $data->success ) {
					throw new Exception('API-feil. Kunne ikke hente deltakere');
				}
			break;
			case 's_kanskje':
				throw new Exception('Feil i rapporten - støtter ikke kanskje');
				$data = $this->_eventManager()->findMaybe($eventId, $this->_owner());
				if( false == $data->success ) {
					throw new Exception('API-feil. Kunne ikke hente deltakere');
				}
			break;
			case 's_kommerikke':
				$data = $this->_eventManager()->findNotComing($eventId, $this->_owner());
				if( false == $data->success ) {
					throw new Exception('API-feil. Kunne ikke hente deltakere');
				}
			break;

			default:
				throw new Exception('Feil i rapporten. Kontakt UKM Norge');
		}
		return $data->data;
	}
	
	private function _status() {
		return array('s_kommer' => 'De som kommer',
					's_kanskje' => 'De som kanskje kommer',
					's_kommerikke' => 'De som ikke kommer',
					's_venter' => 'De som står på venteliste'
					);
	}
	
	public function __construct($rapport, $kategori) {
		parent::__construct($rapport, $kategori);

		$this->monstring = new monstring_v2(get_option('pl_id'));



		$h = $this->optGrp('h','Velg hendelse');
		foreach( $this->_events() as $event ) {
			$this->opt( $h, 'h_'. $event->id, $event->name );
		}

		$s = $this->optGrp('s', 'Hvilken status er du interessert i?');
		foreach( $this->_status() as $status_id => $status_name ) {
			$this->opt( $s, $status_id, $status_name );
		}
		$this->_postConstruct();
	}

	public function generate() {
		if( 0 == sizeof( $this->_events() ) ) {
			die('Du må sette opp minst én hendelse i <a href="admin.php?page=UKMrsvp">helårig UKM</a> før du kan bruke denne rapporten');
		}
		
		$selected_events = [];
		foreach( $this->_events() as $event ) {
			if( $this->show('h_'. $event->id ) ) {
				$selected_events[] = $event;
			}
		}
		if( 0 == sizeof( $selected_events ) ) {
			die('Du må velge minst én hendelse');
		}
		
		if( !$this->show('s_kommer') && !$this->show('s_kanskje') && !$this->show('s_kommerikke') && !$this->show('s_venter') ) {
			die('Du må velge minst én type status!');
		}
		
		foreach( $selected_events as $event ) {
			
			echo '<h3>'. $event->name .'</h3>';
			
			foreach( $this->_status() as $status_id => $status_name ) {
				try {
					if( $this->show($status_id) ) {
						$attending = $this->_find( $status_id, $event->id );
						echo '<h4>'. $status_name .' ('. sizeof( $attending ).' personer) </h4>';
						foreach( $attending as $attendee ) {
							echo '<b>'. $attendee->first_name .' '. $attendee->last_name .'</b> '. $attendee->phone .'<br />';
						}
		
					}
				} catch( Exception $e ) {
					die('<b>Beklager, en ukjent feil oppsto. API sier: </b>'. $e->getMessage() );
				}
			}
		}
	}
}