<?php

/** @var xPDOSimpleObject $object */
if ($object->xpdo) {
    /* @var modX $modx */
    $modx = &$transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {

        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $payment = $modx->getObject(msPayment::class, ['class' => 'Payselection']);

            if (!$payment) {
                $q = $modx->newObject(msPayment::class);
                $q->fromArray([
                    'name' => 'Payselection',
                    'active' => 1,
                    'class' => 'Payselection'
                ]);
                $save = $q->save();
            }

            /* @var miniShop2 $miniShop2 */
            $miniShop2 = $modx->getService('minishop2');

            if ($miniShop2) {
                $miniShop2->addService(
                    'payment',
                    'Payselection',
                    '{core_path}components/minishop2/custom/payment/payselection.class.php'
                );
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $miniShop2 = $modx->getService('minishop2');
            $miniShop2->removeService(
                'payment',
                'Payselection'
            );
            $payment = $modx->getObject(msPayment::class, ['class' => 'Payselection']);
            if ($payment) {
                $payment->remove();
            }
            $modx->removeCollection(modSystemSetting::class, ['key:LIKE' => 'ms2\_payment\_rb\_%']);
            break;
    }
}
return true;
