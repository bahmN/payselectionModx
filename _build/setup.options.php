<?php

$output = '';
$lang  = $modx->getOption('manager_language');

$firstLabel = $lang == 'en' ? 'Make this payment method default' : 'Сделать Payselection платежной системой по умолчанию';
$firstDesc = $lang == 'en' ? 'When this option is selected, the Payselection payment method becomes the default payment' : 'При выборе данной опции метод оплаты Payselection становится оплатой по умолчанию.';
$output = '<label><input type="checkbox" name="make_first" value="1" checked>' . $firstLabel . '</label>' . $firstDesc . '<br><br>';

/** @var array $options */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        if (!empty($options['attributes']['statuses'])) {
            $statuses = '<ul id="formCheckboxes" style="height:200px;overflow:auto;">';
            foreach ($options['attributes']['statuses'] as $k => $v) {
                $label = $lang == 'ru' ? $v : $k;
                $desc  = isset($statusesDesc[$k][$lang]) ? '<p>' . $statusesDesc[$k][$lang] . '</p>' : '';
                $statuses .= '
				<li>
					<label>
						<input type="checkbox" name="add_statuses[]" value="' . $k . '"> ' . $label . '
					</label>
					' . $desc . '
				</li>';
            }
            $statuses .= '</ul>';
        }
        break;

    case xPDOTransport::ACTION_UPGRADE:
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return $output;
