<?php

$lang  = $modx->getOption('manager_language');

$fLabel = $lang == 'en' ? 'Make this payment method default' : 'Сделать Payselection платежной системой по умолчанию';
$fDesc = $lang == 'en' ? 'When this option is selected, the Payselection payment method becomes the default payment' : 'При выборе данной опции метод оплаты Payselection становится оплатой по умолчанию.';
$output = '<label><input type="checkbox" name="make_first" value="1" checked>' . $firstLabel . '</label>' . $firstDesc . '<br><br>';

return $output;
