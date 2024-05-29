<?php

/**
 *  Скрипт сборки платежного модуля Payselection
 * 
 * @package mmspayselection
 */

$mTime = microtime();
$mTime  = explode(' ', $mTime);
$mTime  = $mTime[1] + $mTime[0];
$timeStart = $mTime;

set_time_limit(0);

define('PACKAGE_NAME', 'mmsPaysection');
define('PACKAGE_NAME_LOWER', strtolower(PACKAGE_NAME));
define('PACKAGE_VERSION', '1.0.0');
define('PACKAGE_RELEASE', 'pl');
define('PACKAGE_NAME_LOWER_MINISHOP', 'minishop2');

$root = dirname(dirname(__FILE__)) . '/';
$path = array(
    'root'                          => $root,
    'docs'                          => $root . 'docs/',
    'build'                         => $root . '_build/',
    'data'                          => $root . '_build/data/',
    'resolvers'                     => $root . '_build/resolvers/',
    'chunks'                        => $root . 'core/components/' . PACKAGE_NAME_LOWER . '/elements/chunks/',
    'snippets'                      => $root . 'core/components/' . PACKAGE_NAME_LOWER . '/elements/snippets/',
    'plugins'                       => $root . 'core/components/' . PACKAGE_NAME_LOWER . '/elements/plugins/',
    'source_assets'                 => $root . 'assets/components/' . PACKAGE_NAME_LOWER,
    'source_core'                   => $root . 'core/components/' . PACKAGE_NAME_LOWER,
    'payment_source_assets_files'   => [
        'components/' . PACKAGE_NAME_LOWER_MINISHOP . '/payment/payselection.php'
    ],
    'payment_source_core_files'     => [
        'components/' . PACKAGE_NAME_LOWER_MINISHOP . '/custom/payment/payselection.class.php',
        'components/' . PACKAGE_NAME_LOWER_MINISHOP . '/lexicon/en/msp.payselection.inc.php',
        'components/' . PACKAGE_NAME_LOWER_MINISHOP . '/lexicon/ru/msp.payselection.inc.php'
    ]
);

require_once $path['build'] . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $path['build'] . '/includes/functions.php';

$modx = new modX();
$modx->initialize('mgr');

echo '<pre>';

$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PACKAGE_NAME_LOWER, PACKAGE_VERSION, PACKAGE_RELEASE);
$modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package.');


// Загрузка системных настроек
$settings = include $source['data'] . 'transport.settings.php';
if (!is_array($settings)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in settings.');
} else {
    $attributes = array(
        xPDOTransport::UNIQUE_KEY    => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => BUILD_SETTING_UPDATE,
    );
    foreach ($settings as $setting) {
        $vehicle = $builder->createVehicle($setting, $attributes);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings.');
}

// Создадим категорию
$modx->log(xPDO::LOG_LEVEL_INFO, 'Created category.');
/** @var modCategory $category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PACKAGE_NAME);

// Добавим снипетты
$snippets = include $sources['data'] . 'transport.snippets.php';
if (!is_array($snippets)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in snippets.');
} else {
    $category->addMany($snippets);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippets) . ' snippets.');
}

// Добавим чанки
$chunks = include $sources['data'] . 'transport.chunks.php';
if (!is_array($chunks)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in chunks.');
} else {
    $category->addMany($chunks);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($chunks) . ' chunks.');
}

// Добавим плагины
$plugins = include $sources['data'] . 'transport.plugins.php';
if (!is_array($plugins)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in plugins.');
} else {
    $category->addMany($plugins);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($plugins) . ' plugins.');
}

// Загрузим статусы
$statuses = include $sources['data'] . 'transport.statuses.php';

$attr = array(
    xPDOTransport::UNIQUE_KEY                => 'category',
    xPDOTransport::PRESERVE_KEYS             => false,
    xPDOTransport::UPDATE_OBJECT             => true,
    xPDOTransport::RELATED_OBJECTS           => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Snippets' => array(
            xPDOTransport::PRESERVE_KEYS     => false,
            xPDOTransport::UPDATE_OBJECT     => BUILD_SNIPPET_UPDATE,
            xPDOTransport::UNIQUE_KEY        => 'name',
        ),
        'Chunks' => array(
            xPDOTransport::PRESERVE_KEYS     => false,
            xPDOTransport::UPDATE_OBJECT     => BUILD_CHUNK_UPDATE,
            xPDOTransport::UNIQUE_KEY        => 'name',
        ),
        'Plugins' => array(
            xPDOTransport::PRESERVE_KEYS     => false,
            xPDOTransport::UPDATE_OBJECT     => BUILD_PLUGIN_UPDATE,
            xPDOTransport::UNIQUE_KEY        => 'name',
        ),
        'PluginEvents' => array(
            xPDOTransport::PRESERVE_KEYS     => true,
            xPDOTransport::UPDATE_OBJECT     => BUILD_EVENT_UPDATE,
            xPDOTransport::UNIQUE_KEY        => array('pluginid', 'event'),
        ),
    ),
);
$vehicle = $builder->createVehicle($category, $attr);
$builder->putVehicle($vehicle);

// Создадим платежный метод
$payment = $modx->newObject('msPayment');
$payment->set('id', 1);
$payment->set('name', 'Payselection');
$payment->set('active', 1);
$payment->set('class', 'Payselection');
$payment->set('rank', 100);

$attributes = array(
    xPDOTransport::UNIQUE_KEY    => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => false
);
$vehicle = $builder->createVehicle($payment, $attributes);
$modx->log(modX::LOG_LEVEL_INFO, 'Добавление средств распознавания файлов к платежу...');

$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));

foreach ($sources['payment_source_assets_files'] as $file) {
    $dir = dirname($file) . '/';
    $vehicle->resolve('file', array(
        'source' => $root . 'assets/' . $file,
        'target' => "return MODX_ASSETS_PATH . '{$dir}';",
    ));
}
foreach ($sources['payment_source_core_files'] as $file) {
    $dir = dirname($file) . '/';
    $vehicle->resolve('file', array(
        'source' => $root . 'core/' . $file,
        'target' => "return MODX_CORE_PATH . '{$dir}';"
    ));
}

$modx->log(modX::LOG_LEVEL_INFO, 'Добавление в PHP-преобразователи...');

$vehicle->resolve('php', array(
    'source' => $sources['resolvers'] . 'resolve.make_first.php',
));

$vehicle->resolve('php', array(
    'source' => $sources['resolvers'] . 'resolve.uninstall.php',
));

// Загрузим лицензии и README
$builder->setPackageAttributes(array(
    'changelog'     => file_get_contents($sources['docs'] . 'changelog.txt'),
    'license'       => file_get_contents($sources['docs'] . 'license.txt'),
    'readme'        => file_get_contents($sources['docs'] . 'readme.txt'),
    'statuses'      => $BUILD_STATUSES,
    'setup-options' => array(
        'source' => $sources['build'] . 'setup.options.php',
    ),
));

$modx->log(modX::LOG_LEVEL_INFO, 'Добавлены атрибуты пакета и параметры настройки.');

/* Архивация */
$modx->log(modX::LOG_LEVEL_INFO, 'Упаковка в транспортный архив...');
$builder->pack();

$mTime     = microtime();
$mTime     = explode(" ", $mTime);
$mTime     = $mTime[1] + $mTime[0];
$timeEnd      = $mTime;
$totalTime = ($timeEnd - $timeStart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit();
