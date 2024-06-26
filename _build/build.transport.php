<?php

/**
 * mmsPayselection build script
 *
 * @package payselection
 * @subpackage build
 */

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

require_once 'build.config.php';

/* define sources */
$root = dirname(__FILE__, 2) . '/';
$sources = array(
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'resolvers' => $root . '_build/resolvers/',
    'source_assets' => array(
        'components/minishop2/payment/payselection.php'
    ),
    'source_core' => array(
        'components/minishop2/custom/payment/payselection.class.php',
        'components/minishop2/lexicon/en/msp.payselection.inc.php',
        'components/minishop2/lexicon/ru/msp.payselection.inc.php'
    ),
    'docs' => $root . 'docs/'
);
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

$modx = new modX();
$modx->initialize('mgr');
echo '<pre>'; /* used for nice formatting of log messages */
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$modx->log(modX::LOG_LEVEL_INFO, 'Созданный транспортный пакет.');

/* load system settings */
if (defined('BUILD_SETTING_UPDATE')) {
    $settings = include $sources['data'] . 'transport.settings.php';
    if (!is_array($settings)) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Не удалось выполнить упаковку в настройках.');
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => BUILD_SETTING_UPDATE,
        );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $modx->log(modX::LOG_LEVEL_INFO, 'Упаковано ' . count($settings) . ' Системных настроек.');
    }
    unset($settings, $setting, $attributes);
}

/* @var msPayment $payment */
$payment = $modx->newObject(msPayment::class);
$payment->fromArray(array(
    'name' => 'Payselection',
    'active' => 1,
    'class' => 'Payselection'
));

/* create payment vehicle */
$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => false
);
$vehicle = $builder->createVehicle($payment, $attributes);

$modx->log(modX::LOG_LEVEL_INFO, 'Добавление средств распознавания файлов к платежу...');
foreach ($sources['source_assets'] as $file) {
    $dir = dirname($file) . '/';
    $vehicle->resolve('file', array(
        'source' => $root . 'assets/' . $file,
        'target' => "return MODX_ASSETS_PATH . '{$dir}';",
    ));
}
foreach ($sources['source_core'] as $file) {
    $dir = dirname($file) . '/';
    $vehicle->resolve('file', array(
        'source' => $root . 'core/' . $file,
        'target' => "return MODX_CORE_PATH . '{$dir}';"
    ));
}
unset($file, $attributes);

$resolvers = array('settings');
foreach ($resolvers as $resolver) {
    if ($vehicle->resolve('php', array('source' => $sources['resolvers'] . 'resolve.' . $resolver . '.php'))) {
        $modx->log(modX::LOG_LEVEL_INFO, 'Добавлены распознователи"' . $resolver . '" в категорию.');
    } else {
        $modx->log(modX::LOG_LEVEL_INFO, 'Не удалось добавить распознаватель "' . $resolver . '" в категорию.');
    }
}

flush();
$builder->putVehicle($vehicle);

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'changelog' => file_get_contents($sources['docs'] . 'change.log'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt')
));
$modx->log(modX::LOG_LEVEL_INFO, 'Добавлены атрибуты пакета и параметры настройки.');

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO, 'Упаковка в zip-архив...');
$builder->pack();
$modx->log(modX::LOG_LEVEL_INFO, "\n<br>Zip-архив собран.</br>");

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$signature = $builder->getSignature();
if (defined('PKG_AUTO_INSTALL') && PKG_AUTO_INSTALL) {
    $sig = explode('-', $signature);
    $versionSignature = explode('.', $sig[1]);

    /* @var modTransportPackage $package */
    if (!$package = $modx->getObject('transport.modTransportPackage', array('signature' => $signature))) {
        $package = $modx->newObject('transport.modTransportPackage');
        $package->set('signature', $signature);
        $package->fromArray(array(
            'created' => date('Y-m-d h:i:s'),
            'updated' => null,
            'state' => 1,
            'workspace' => 1,
            'provider' => 0,
            'source' => $signature . '.transport.zip',
            'package_name' => $sig[0],
            'version_major' => $versionSignature[0],
            'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
            'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
        ));
        if (!empty($sig[2])) {
            $r = preg_split('/([0-9]+)/', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
            if (is_array($r) && !empty($r)) {
                $package->set('release', $r[0]);
                $package->set('release_index', (isset($r[1]) ? $r[1] : '0'));
            } else {
                $package->set('release', $sig[2]);
            }
        }
        $package->save();
    }
    $package->install();
}
if (!empty($_GET['download'])) {
    echo '<script>document.location.href = "/core/packages/' . $signature . '.transport.zip' . '";</script>';
}

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Execution time: {$totalTime}\n");
echo '</pre>';
