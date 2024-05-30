<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx = &$transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modelPath = $modx->getOption(
                'minishop2.core_path',
                null,
                $modx->getOption('core_path') . 'components/minishop2/'
            ) . 'model/';
            $modx->addPackage('minishop2', $modelPath);
            $lang = $modx->getOption('manager_language') == 'en' ? 1 : 0;

            $statuses = array(
                'Authorized' => array(
                    'name'            => !$lang ? 'Авторизован' : 'Authorized',
                    'color'           => '99CC00',
                    'email_user'      => 0,
                    'email_manager'   => 0,
                    'subject_user'    => '',
                    'subject_manager' => '',
                    'body_user'       => '',
                    'body_manager'    => '',
                    'final'           => 0,
                ),
                'Confirmed'  => array(
                    'name'            => !$lang ? 'Подтвержден' : 'Confirmed',
                    'color'           => 'FF9900',
                    'email_user'      => 0,
                    'email_manager'   => 0,
                    'subject_user'    => '',
                    'subject_manager' => '',
                    'body_user'       => '',
                    'body_manager'    => '',
                    'final'           => 0,
                ),
            );

            $statusSettings = array(
                'Authorized' => 'ms2_payment_payselection_order_status_auth_id',
                'Confirmed'  => 'ms2_payment_payselection_status_for_confirm_id',
            );

            if ($setting = $modx->getObject(
                'modSystemSetting',
                array('key' => 'ms2_payment_payselection_two_steps')
            )) {
                $setting->set('value', !empty($options['two_steps'] ? '1' : '0'));
                $setting->save();
            }
            if (!empty($options['two_steps']) && !empty($options['add_statuses'])) {
                $real_add_statuses = array();
                foreach ($options['add_statuses'] as $v) {
                    if (!isset($statuses[$v])) {
                        continue;
                    };
                    $properties = $statuses[$v];
                    if ($modx->getCount('msOrderStatus', array('name' => $properties['name']))) {
                        continue;
                    }

                    $real_add_statuses[$v] = $properties;
                }

                if (count($real_add_statuses)) {
                    $sql = sprintf(
                        'UPDATE %s SET rank = rank + %d WHERE id > 1',
                        $modx->getTableName('msOrderStatus'),
                        count($real_add_statuses)
                    );
                    $modx->exec($sql);
                }

                $rank = 1;
                foreach ($real_add_statuses as $k => $properties) {
                    /** @var msOrderStatus $status */
                    $status = $modx->newObject('msOrderStatus', array_merge(array(
                        'editable' => 0,
                        'active'   => 1,
                        'rank'     => $rank,
                        'fixed'    => 1,
                    ), $properties));
                    $status->save();
                    if (isset($statusSettings[$k])) {
                        if ($setting = $modx->getObject(
                            'modSystemSetting',
                            array('key' => $statusSettings[$k])
                        )) {
                            $setting->set('value', $status->get('id'));
                            $setting->save();
                        }
                    }
                    $rank++;
                }
            }

            break;
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
return true;
