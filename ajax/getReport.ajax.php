<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Counter;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once('UKM/Autoloader.php');
require_once('UKM/inc/twig-admin.inc.php');

$rapport = UKMrapporter::getAktivRapport($_POST['rapport']);
$rapport->setConfigFromString($_POST['config']);

if (method_exists(get_class($rapport), 'getCustomizerData')) {
    $data = $rapport->getCustomizerData();
    if (is_array($data)) {
        UKMrapporter::addResponseData($data);
    }
}

UKMrapporter::addResponseData(
    [
        'config' => $rapport->getConfig(),
        'rapport' => $rapport,
        'renderData' => $rapport->getRenderData(),
        'fancyCounter' => new Counter("tittelCounter")
    ]
);

if (get_option('pl_id')) {
    UKMrapporter::addViewData(
        [
            'arrangement' => new Arrangement((int) get_option('pl_id')),
            'kreverHendelse' => $rapport->kreverHendelse(),
        ]
    );
}

// BRUKER SØKER HTML-UTGAVEN
if ($_POST['format'] == 'html') {
    // Bruker allerede angitt respons-data (inkludert renderData fra ovenfor)
    UKMrapporter::addResponseData(
        'html',
        TWIG(
            $rapport->getTemplate(),
            array_merge(UKMrapporter::getResponseData(), UKMrapporter::getViewData()),
            UKMrapporter::getPluginPath()
        )
    );
}

// BRUKER SØKER EXCEL-UTGAVEN
if ($_POST['format'] == 'excel') {
    UKMrapporter::addResponseData(
        'link',
        $rapport->getExcelFile()
    );
}
// BRUKER SØKER WORD-UTGAVEN
elseif ($_POST['format'] == 'word') {
    UKMrapporter::addResponseData(
        'link',
        $rapport->getWordFile()
    );
}
// BRUKER SØKER PDF-UTGAVEN
elseif ($_POST['format'] == 'pdf' && $rapport->getId() == 'Diplom') {
    if (!class_exists(Dompdf::class)) {
        throw new Exception('Dompdf er ikke tilgjengelig. Installer composer-avhengighetene.');
    }

    $config = $rapport->getConfig();
    $themeKey = $config->har('diplom_theme') ? $config->get('diplom_theme')->getValue() : 'purple';
    $themes = [
        'dark' => [
            'background' => '#241211',
            'logoFile' => 'ukmlogolilla.svg',
            'textColor' => '#f5eee4'
        ],
        'purple' => [
            'background' => '#ad83ff',
            'logoFile' => 'ukmlogomorkbrun.svg',
            'textColor' => '#15082a'
        ],
        'orange' => [
            'background' => '#ff520e',
            'logoFile' => 'ukmlogomorkbrun.svg',
            'textColor' => '#15082a'
        ],
        'dark_orange' => [
            'background' => '#241211',
            'logoFile' => 'ukmlogoorange.svg',
            'textColor' => '#f5eee4'
        ]
    ];
    $theme = array_key_exists($themeKey, $themes) ? $themes[$themeKey] : $themes['purple'];
    $pluginPath = UKMrapporter::getPluginPath();

    $logoPath = $pluginPath . 'client/dist/assets/logos/' . $theme['logoFile'];
    $logoPathRel = 'client/dist/assets/logos/' . $theme['logoFile'];
    $logoDataUri = '';
    if (file_exists($logoPath)) {
        $logoExt = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoMime = $logoExt === 'svg' ? 'image/svg+xml' : 'image/' . $logoExt;
        $logoDataUri = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    $fontPath = 'client/dist/assets/fonts/TWKBurns-Ultra.ttf';

    $arrangement = $rapport->getArrangement();
    $placeOverride = $config->har('diplom_place_override') ? $config->get('diplom_place_override')->getValue() : '';
    $seasonOverride = $config->har('diplom_season_override') ? $config->get('diplom_season_override')->getValue() : '';

    $placeLabel = $placeOverride;
    if (!$placeLabel && $arrangement) {
        $navn = $arrangement->getNavn();
        $navnLower = mb_strtolower($navn, 'UTF-8');
        if ($arrangement->getEierType() == 'kommune') {
            $placeLabel = (strpos($navnLower, 'ukm') === 0) ? $navn : 'UKM ' . $navn;
        } elseif ($arrangement->getEierType() == 'fylke') {
            $placeLabel = (strpos($navnLower, 'fylkesfestival') === 0) ? $navn : 'Fylkesfestival ' . $navn;
        } else {
            $placeLabel = $navn;
        }
    }
    if ($placeLabel) {
        $placeLabel = preg_replace('/\b(19|20)\d{2}\b/u', '', $placeLabel);
        $placeLabel = trim(preg_replace('/\s{2,}/', ' ', $placeLabel));
        $placeLabel = preg_replace('/\s*[-\/|,]\s*$/', '', $placeLabel);
    }

    $seasonLabel = $seasonOverride;
    if (!$seasonLabel && $arrangement) {
        $seasonLabel = $arrangement->getSesong();
    }

    $personer = [];
    if (method_exists(get_class($rapport), 'getCustomizerData')) {
        $data = $rapport->getCustomizerData();
        if (is_array($data) && isset($data['diplomPersoner'])) {
            $personer = $data['diplomPersoner'];
        }
    }

    $html = TWIG(
        'Diplom/pdf.html.twig',
        array_merge(UKMrapporter::getResponseData(), UKMrapporter::getViewData(), [
            'personer' => $personer,
            'theme' => $theme,
            'logoDataUri' => $logoDataUri,
            'logoPathRel' => $logoPathRel,
            'fontPath' => $fontPath,
            'placeLabel' => $placeLabel,
            'seasonLabel' => $seasonLabel
        ]),
        UKMrapporter::getPluginPath()
    );

    $upload = wp_upload_dir();
    $dompdfDir = $upload['basedir'] . '/ukmrapporter/dompdf';
    if (!is_dir($dompdfDir)) {
        wp_mkdir_p($dompdfDir);
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isFontSubsettingEnabled', true);
    $options->set('defaultFont', 'TWKBurns Ultra');
    $options->set('chroot', UKMrapporter::getPluginPath());
    $options->set('fontDir', $dompdfDir);
    $options->set('fontCache', $dompdfDir);
    $dompdf = new Dompdf($options);
    $dompdf->setOptions($options);
    $dompdf->getCanvas();
    $fontMetrics = $dompdf->getFontMetrics();
    $fontMetrics->registerFont(
        ['family' => 'TWKBurns Ultra', 'weight' => 'normal', 'style' => 'normal'],
        UKMrapporter::getPluginPath() . 'client/dist/assets/fonts/TWKBurns-Ultra.ttf'
    );
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $upload = wp_upload_dir();
    $dir = $upload['basedir'] . '/ukmrapporter';
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $filename = 'diplom_' . date('Ymd_His') . '.pdf';
    $path = $dir . '/' . $filename;
    file_put_contents($path, $dompdf->output());

    $link = $upload['baseurl'] . '/ukmrapporter/' . $filename;
    UKMrapporter::addResponseData('link', $link);
}
