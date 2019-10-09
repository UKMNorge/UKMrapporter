<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Program\Write;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement( get_option('pl_id') );

foreach( $arrangement->getProgram()->getAll() as $hendelse ) {
    Write::reOrder( $hendelse );
}