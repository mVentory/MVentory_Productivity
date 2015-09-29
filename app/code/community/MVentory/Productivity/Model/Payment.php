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
 * Payment model
 *
 * @package MVentory/Productivity
 * @author
 */
class MVentory_Productivity_Model_Payment extends  Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'productivity';
    protected $_canUseCheckout = false;
}