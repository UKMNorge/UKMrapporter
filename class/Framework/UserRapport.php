<?php

namespace UKMNorge\Rapporter\Framework;

/**
 * Brukes for rapporter i bruker-senteret
 * 
 * Dette fordi brukersenteret mangler noen muligheter som følge av at
 * det ikke er tilknyttet et arrangement
 */
class UserRapport extends Rapport {
    public $har_sms = false;
}