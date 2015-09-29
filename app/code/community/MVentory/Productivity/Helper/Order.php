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
 * Order helper
 *
 * @package MVentory/Productivity
 * @author
 */
class MVentory_Productivity_Helper_Order extends MVentory_Productivity_Helper_Data
{
    /**
     * Create order from quote
     * @return Mage_Sales_Model_Order
     */
    public function createOrder () {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $billingAddress = $customer->getDefaultBillingAddress();
        $quote = $this->_getQuote();
        $quote->setCustomer($customer);

        $this->_setAddresses($quote, $billingAddress);
        $this->_setShippingMethod($quote);
        $this->_setPaymentMethod($quote);
        $quote->save();
        $order = $this->_createOrder($quote);
        try {
            $shipment = $this->_createShipment($order);
            $invoice = $this->_createInvoice($order);
            $this->_completeOrder($order, $shipment, $invoice);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $order;
    }

    /**
     * Get quote
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Create order from quote
     * @param $quote Mage_Sales_Model_Quote
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function _createOrder ($quote) {
        $customerResource = Mage::getModel('checkout/api_resource_customer');
        $customerResource->prepareCustomerForQuote($quote);
        $quote
            ->setTotalsCollectedFlag(false)
            ->collectTotals();
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent(
                'checkout_type_onepage_save_order_after',
                ['order' => $order, 'quote' => $quote]
            );
            try {
                $order->queueNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $quote)
        );
        $quote->setIsActive(0)
              ->save();
        return $order;
    }

    /**
     *  Set customer address to quote
     * @param $quote Mage_Sales_Model_Quote
     * @param $address Mage_Customer_Model_Address
     */
    protected function _setAddresses ($quote, $address) {
        $quoteAddress = Mage::getModel('sales/quote_address')
            ->importCustomerAddress($address)
            ->implodeStreetAddress();

        $billingAddress = clone $address;
        $billingAddress
            ->unsAddressId()
            ->unsAddressType();
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();
        $shippingAddress
            ->addData($billingAddress->getData())
            ->setSameAsBilling(1)
            ->setShippingMethod($shippingMethod)
            ->setCollectShippingRates(true);
        $quote->setBillingAddress($quoteAddress);
    }

    /**
     * Set shipping method for quote
     * @param $quote Mage_Sales_Model_Quote
     * @throws Exception
     */
    protected function _setShippingMethod ($quote) {
        $address = $quote->getShippingAddress();
        $rate = $address
            ->collectShippingRates()
            ->getShippingRateByCode('productivity_productivity');
        if (!$rate)
            throw new Exception('shipping_method_is_not_available');
        $address->setShippingMethod('productivity_productivity');
    }

    /**
     * Set payment method for quote
     * @param $quote Mage_Sales_Model_Quote
     * @throws Exception
     */
    protected function _setPaymentMethod ($quote) {
        $methodCode =  'productivity';
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($methodCode);
        } else {
            $quote->getShippingAddress()->setPaymentMethod($methodCode);
        }
        $quote->getPayment()->importData(array('method' => $methodCode));
    }

    /**
     * Create shipment entity for order
     * @param $order Mage_Sales_Model_Order
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _createShipment ($order) {
        return $order->prepareShipment()
                     ->register();
    }

    /**
     * Create invoice entity for order
     * @param $order Mage_Sales_Model_Order
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _createInvoice ($order) {
        return $order->prepareInvoice()
                     ->register();
    }

    /**
     * Set complete order's status
     * @param $order Mage_Sales_Model_Order
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @param $invoice Mage_Sales_Model_Order_Invoice
     * @return Mage_Core_Model_Resource_Transaction
     * @throws Exception
     */
    protected function _completeOrder ($order, $shipment, $invoice) {
        return Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($invoice)
            ->addObject($order)
            ->save();
    }

    /*
     * Check products in cart
     */
    public function isProductsInCart() {
        $quote = $this->_getQuote();
        return count($quote->getAllVisibleItems());
    }

}