<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\UKMFestival;
use UKMrapporter;



class Overnatting extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-admin-multisite';
    public $navn = 'Overnatting';
    public $beskrivelse = 'Informasjon om hotel, rom osv.';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;
    
    private $table_name = 'ukm_festival_overnatting_gruppe';
	private $table_idcol = 'id';
	private $object_type = 'gruppe';
	private $filter = '';


    /**
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData()
    {
        try {
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

        UKMrapporter::addViewData('sesong', (int) $this->getConfig()->get('vis_sesong')->getValue());
        return 'Overnatting/rapport.html.twig';
    }
}
