<?php

if ($transport->xpdo) {
    $modx = &$transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            if (!empty($options['make_first'])) {
                $payment = $modx->getObject('msPayment', array('class' => 'Payselection'));
                if ($payment && $payment->get('rank') > 0) {
                    $payment->set('rank', 0);
                    $payment->save();
                    $sql = sprintf(
                        'UPDATE %s SET rank = rank + 1 WHERE id <> %d',
                        $modx->getTableName('msPayment'),
                        $payment->get('id')
                    );
                    $modx->exec($sql);
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'Make payment first');
                }
            }
            break;
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
return true;
