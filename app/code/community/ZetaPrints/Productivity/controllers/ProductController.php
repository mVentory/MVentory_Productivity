<?php

/**
 * Productivity
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  ZetaPrints
 * @package   ZetaPrints_Productivity
 * @copyright Copyright (c) 2014 ZetaPrints Ltd. (http://www.zetaprints.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Product controller
 *
 * @category ZetaPrints
 * @package  ZetaPrints_Productivity
 * @author   daniel@clockworkgeek.com
 */

require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

class ZetaPrints_Productivity_ProductController extends Mage_Core_Controller_Front_Action
{

  /**
   * Save new product details then redirect to product page.
   * Reviewer will see the page refresh.
   */
  public function saveAction() {
    $request = $this->getRequest();

    // URL parameter
    $productId = $request->getParam('id');
    // Useful stuff
    $storeId = Mage::app()->getStore()->getId();
    $helper = Mage::helper('productivity');

    if ($helper->isReviewerLogged())
    {
      // Product saves must be made from admin store
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

      //Don't set store ID to product because it has to be saved
      //in a global scope
      $product = Mage::getModel('catalog/product')->load($productId);

      // Only allow certain attributes to be set, if missing product will not be changed.
      $data = array_intersect_key(
        $request->getPost(),
        $helper->getVisibleAttributes($product)
      );
      if (isset($data['description'])) {
        $data['short_description'] = $data['description'];
      }

      if ($product->getTypeId() != 'configurable') {
        $qty = (float) $request->getParam('qty');

        $data['stock_data'] = array(
          'qty'
            => $qty < Mage_Adminhtml_Catalog_ProductController::MAX_QTY_VALUE
                 ? $qty
                   : Mage_Adminhtml_Catalog_ProductController::MAX_QTY_VALUE,
          'is_in_stock'
            => $qty > 0
                 ? Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK
                   : Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK
            );
      }

      $product->addData($data)
          ->save();

      // Reset store to be neat
      Mage::app()->setCurrentStore($storeId);
    }

    // Always show same page, even if nothing has changed
    $this->_redirectUrl($product->setData('url', null)->getProductUrl());
  }

}

