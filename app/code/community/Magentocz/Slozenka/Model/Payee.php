<?php
/** 
* Magento CZ Module
* 
* NOTICE OF LICENSE 
* 
* This source file is subject to the Open Software License (OSL 3.0) 
* that is bundled with this package in the file LICENSE.txt. 
* It is also available through the world-wide-web at this URL: 
* http://opensource.org/licenses/osl-3.0.php 
* If you did of the license and are unable to 
* obtain it through the world-wide-web, please send an email 
* to magentocz@gmail.com so we can send you a copy immediately. 
* 
* @copyright Copyright (c) 2015 GetReady s.r.o. (https://getready.cz)
* 
*/
/**
 *
 * @category   Magentocz
 * @package    Magentocz_Slozenka
 */
class Magentocz_Slozenka_Model_Payee extends Varien_Object {

	public function __construct() {
		$this->setData( array (
			'bank_account_number_prefix' => "000000",
			'bank_account_number' => "2600012345",
			'bank_code' => "2010",
			'constant_symbol' => "0008",
			'name' => 'Nazev obchodníka',
			'street' => 'Ulice obchodníka',
			'city' => 'Město obchodníka',
			)
		);
	}
}