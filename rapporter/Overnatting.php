<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Arrangement\Arrangement;
use UKMrapporter;



class Overnatting extends Rapport
{
    public $kategori_id = 'ukmfestivalen';
    public $ikon = 'dashicons-admin-multisite';
    public $navn = 'Hotellbestillinger';
    public $beskrivelse = 'Informasjon om hotel, rom osv.';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;
    
    private $table_name = 'ukm_festival_overnatting_gruppe';
	private $table_idcol = 'id';
	private $object_type = 'gruppe';
	private $filter = '';

    
    /**
     * Er rapporten synlig
     * Rapporten er synlig bare på UKM Festivalen (land)
     * 
     * @return Array
     */
    public function erSynlig() {
        // Sjekk om arrangementet finnes. Metoden kan kalles fra kommuner eller fylker
        try {
            $arrangement = new Arrangement(get_option('pl_id'));
        }catch(Exception $e) {
            return false;
        }

        if($arrangement->getEierType() == 'land') {
            return true;
        }
        return false;
    }

    /**
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData()
    {
        $arrangement = $this->getArrangement();

       
        try {
            if($arrangement->getEierType() == 'land') {
                $arrangement = new UKMFestival($arrangement->getId());
                return ['skjema' => $this->getArrangement()->getSkjema(), 'overnattingGrupper' => $arrangement->getOvernattingGrupper()];
            }
            
            return ['skjema' => $this->getArrangement()->getSkjema()];
        } catch (Exception $e) {
            if ($e->getCode() != 151002) {
                throw $e;
            }
        }
        return [];
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        $arrangement = $this->getArrangement();

        if($arrangement->getEierType() == 'land') {
            $arrangement = new UKMFestival($arrangement->getId());
            UKMrapporter::addViewData('overnattingGrupper', $arrangement->getOvernattingGrupper());
        }
        
        UKMrapporter::addViewData('gruppe', (int) $this->getConfig()->get('vis_gruppe')->getValue());
        return 'Overnatting/rapport.html.twig';
    }
}
