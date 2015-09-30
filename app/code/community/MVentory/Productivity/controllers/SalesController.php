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
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Sales controller
 *
 * @package MVentory/Productivity
 * @author
 */
class MVentory_Productivity_SalesController extends Mage_Core_Controller_Front_Action {

    /*
     *  Sold products based customer's quote
     */
    public function soldAction () {
        $helper = Mage::helper('productivity/order');

        //TODO: This IF is a bit too deep. I would check the preconditions and exit with an error message before proceeding. Leave as is, but just a note for future development.
        if (!$helper->isReviewerLogged()) {
            Mage::getSingleton('core/session')->addError($helper->__('Log in as a Customer with Reviewer rights to complete this operation.'));
            $this->_redirectReferer();
        } else {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if ($customer->getDefaultBillingAddress()) {
                $order = $helper->createOrder();
                if ($orderId = $order->getId()) {
                    Mage::getSingleton('core/session')->addSuccess($helper->__('Order N %s was created successfully.', $order->getIncrementId()));
                    $this->_redirect('sales/order/view', array('order_id' => $orderId));
                } else {
                    Mage::getSingleton('core/session')->addError($helper->__('Order doesn\'t created!'));
                    $this->_redirectReferer();
                }
            } else {
                Mage::getSingleton('core/session')->addNotice($helper->__('Please, enter your default address for this type of orders and try to process the sale again. Once the address is set it will be used for all subsequent sales. You can change it any time in your Customer Profile.'));
                $this->_redirect('customer/address');
            }
        }
    }
}
