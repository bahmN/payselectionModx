<?php

/**
 *  Константы пути MODX, необходимые для установки ядра
 *
 * @package    mmspayselection
 * 
 */
define('MODX_BASE_PATH', dirname(dirname(__DIR__)) . '/');
define('MODX_CORE_PATH', MODX_BASE_PATH . 'core/');
define('MODX_MANAGER_PATH', MODX_BASE_PATH . 'manager/');
define('MODX_CONNECTORS_PATH', MODX_BASE_PATH . 'connectors/');
define('MODX_ASSETS_PATH', MODX_BASE_PATH . 'assets/');

define('MODX_BASE_URL', '/');
define('MODX_CORE_URL', MODX_BASE_URL . 'core/');
define('MODX_MANAGER_URL', MODX_BASE_URL . 'manager/');
define('MODX_CONNECTORS_URL', MODX_BASE_URL . 'connectors/');
define('MODX_ASSETS_URL', MODX_BASE_URL . 'assets/');

define('BUILD_SETTING_UPDATE', false);
define('BUILD_CHUNK_UPDATE', true);
define('BUILD_SNIPPET_UPDATE', true);
define('BUILD_PLUGIN_UPDATE', true);
define('BUILD_EVENT_UPDATE', true);

define('BUILD_CHUNK_STATIC', false);
define('BUILD_SNIPPET_STATIC', false);
define('BUILD_PLUGIN_STATIC', false);
