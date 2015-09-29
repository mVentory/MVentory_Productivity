<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package MVentory/Productivity
 * @copyright Copyright (c) 2015 mVentory Ltd. (http://mventory.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Shipping model
 *
 * @package MVentory/Productivity
 * @author
 */
class MVentory_Productivity_Model_Shipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'productivity';
    protected $_isFixed = true;

    public function collectRates (Mage_Shipping_Model_Rate_Request $request) {

        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier('productivity');
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod('productivity');
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice('0.00');
        $method->setCost('0.00');
        $result = Mage::getModel('shipping/rate_result');
        $result->append($method);
        return $result;
    }

    public function getAllowedMethods () {
        return array('productivity' => $this->getConfigData('name'));
    }
}