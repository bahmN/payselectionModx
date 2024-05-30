<?php

/**
 * Загрузка системных настроек
 * 
 * @package ms2payselection
 * @subpackage build
 */
$settings = array();

$tmp = array(
    'ms2_payment_payselection_site_id' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection'
    ],
    'ms2_payment_payselection_public_key' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection'
    ],
    'ms2_payment_payselection_currency' => [
        'value' => 'RUB',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection'
    ],
    'ms2_payment_payselection_kassa' => [
        'value' => false,
        'xtype' => 'combo-boolean',
        'area' => 'ms2_payment_payselection'
    ],
    'ms2_payment_payselection_vat' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection',
    ],
    'ms2_payment_payselection_payment_method' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection',
    ],
    'ms2_payment_payselection_payment_object' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection',
    ],
    'ms2_payment_payselection_success_url' => [
        'value' => '',
        'xtype' => 'textfield',
        'area' => 'ms2_payment_payselection',
    ],
);

foreach ($tmp as $k => $v) {
    /* @var modSystemSetting $setting */
    $setting = $modx->newObject(modSystemSetting::class);
    $setting->fromArray(array_merge(
        array(
            'key' => $k,
            'namespace' => 'minishop2',
            'editedon' => date('Y-m-d H:i:s'),
        ),
        $v
    ), '', true, true);

    $settings[] = $setting;
}

return $settings;
