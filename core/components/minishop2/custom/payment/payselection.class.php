<?php

if (!class_exists('msPaymentInterface')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/model/minishop2/mspaymenthandler.class.php';
}

class Payselection extends msPaymentHandler implements msPaymentInterface {
    public $modx;

    function __construct(xPDOObject $object, $config = array()) {
        $this->modx = &$object->xpdo;
    }

    public function send(msOrder $order) {
        $link = $this->getPaymentLink($order);
        return $this->success('', array('redirect' => $link));
    }

    public function getPaymentLink(msOrder $order) {
        $amount = number_format($order->get('cost', 2, '.', ''));
        $orderId = $order->get('id');
        //TODO: get params: X-SITE-ID, publicKey, products, customer, vat,kkt
        //TODO: create X-REQUEST-SIGNATURE https://api.payselection.com/#tag/Nachalo-raboty-s-API/Request-Signature-or-Podpis-zaprosa

        $url = 'https://webform.payselection.com/webpayments/create';
        $params = array(
            'PaymentRequest' => [
                'OrderId' => strval($orderId),
                "Amount" => $amount,
                'Currency' => 'RUB',
                'Description' =>  'Оплата'
            ]
        );

        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_HTTPHEADER, [
            'X-SITE-ID: 20071',
            'X-REQUEST-ID: ' . $orderId . '_' . time(),
            'X-REQUEST-SIGNATURE: 3b236da94afa12ce644a7e90fac3fd9f06728ed6a9740255459787d8a363ffd3'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);

        // $this->log(json_decode($res, true)); //TODO: удалить, когда закончим

        return json_decode($res, true);
    }

    public function log($data) {
        $errorHandlerFileName = "C:\installedapps\OSPanel\domains\modx.local\core\components\minishop2\custom\payment\devLog.log";
        $logTimestamp = date_format(date_timestamp_set(new \DateTime(), time())->setTimezone(new \DateTimeZone('Europe/Moscow')), 'c');
        $errorHandlerMessage = "[" . $logTimestamp . "] info: \nPOST: \n" . print_r($data, true) . "\n\n";

        $fp = fopen($errorHandlerFileName, "a");
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, $errorHandlerMessage);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
