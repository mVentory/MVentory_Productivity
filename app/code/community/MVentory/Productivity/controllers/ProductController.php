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

require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

/**
 * Product controller
 *
 * @package MVentory/Productivity
 * @author <daniel@clockworkgeek.com>
 */
class MVentory_Productivity_ProductController extends Mage_Core_Controller_Front_Action
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
    $helper = Mage::helper('productivity/attribute');

    if ($helper->isReviewerLogged())
    {
      //Check if save scope is set and we need to load product
      //with dara from current store
      $saveScope = (int) Mage::getStoreConfig(
        MVentory_Productivity_Model_Config::_PRODUCT_SAVE_SCOPE
      );

      if ($saveScope
            != MVentory_Productivity_Model_Config::PRODUCT_SCOPE_CURRENT)
        $storeId = false;

      unset($saveScope);

      //Product loads and saves must be made from admin store
      //- Magento doesn't fill origData array when product is loaded from
      //  non admin scope, so some core comparing function (for comparing
      //  original and current product data) won't work as expected
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

      $product = Mage::getModel('catalog/product');

      //Set current store ID before loading product if save scope is set,
      //e.g. user selected Current scope optin in admin interface
      if ($storeId !== false)
        $product->setStoreId($storeId);

      $isConfigurable = $product
        ->load($productId)
        ->isConfigurable();

      // Only allow certain attributes to be set, if missing product will not be changed.
      $data = array_intersect_key(
        $request->getPost(),
        $helper->getEditables($product)
      );

      if (isset($data['qty'])) {
        if (!$isConfigurable)
          $qty = $data['qty'];

        unset($data['qty']);
      }

      //Find all attributes which value was changed in the editor by comparing
      //data from request with product's data
      //
      //In admin interface it uses special Use default flag for every field
      //which user removes before setting different value in selected scope.
      //Productivity interface doesn't provide such flags, so it simply
      //compares current product values with values entered in the form to
      //find changed fields.
      $changeAttrs = array();
      foreach ($data as $code => $value)
        if ($product->getData($code) != $value)
          $changeAttrs[$code] = true;

      if (isset($qty)) {
        $qty = (float) $qty;

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

      if ($changeAttrs || isset($qty)) {
        $product->addData($data);

        if ($storeId !== false)
          $this->_unsetValues($product, $changeAttrs);

        $product->save();

        if ($isConfigurable)
          $this->_copyDataToSimpleProducts($product, $changeAttrs, $storeId);

        //Reset store to be neat
        Mage::app()->setCurrentStore($storeId);
      }
    }

    //Always show same page, even if nothing has changed
    $this->_redirectUrl($product->setData('url', null)->getProductUrl());
  }

  /**
   * Set value of not changed attributes to false to prevents from copying
   * attribute's values to current scope if user selected Current store
   * option of Save edits to setting in admin interface
   *
   * Filter out following attributes:
   * - Gallery attr (is a complex attr with it's own editor)
   * - Global (setting value of global attr to false removes it globally)
   * - Non-visible (non-visible attributes can't be edited directly)
   * - Without input field (this attributes can'be edited directly)
   *
   * Filtering rules are taken from:
   * - Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes::_prepareForm()
   * - Mage_Adminhtml_Block_Widget_Form::_setFieldset()
   *
   * @param Mage_Catalog_Model_Product $product Product
   * @param array $changed Key-based list of updated attributes
   */
  protected function _unsetValues ($product, $changed) {
    foreach ($product->getAttributes() as $code => $attr) {
      $allow = $code != 'gallery'
               && $attr->getIsGlobal()
                    != Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
               && $attr->getIsVisible()
               && $attr->getFrontend()->getInputType();

      if (!$allow || isset($changed[$code]))
        continue;

      $product
        ->setData($code, false)
        ->setOrigData($code, false);
    }
  }

  /**
   * Copy data from configurable product to it associated
   * simple products
   *
   * @param Mage_Catalog_Model_Product $configurable Configurable product
   * @param array $changed Key-based list of updated attributes
   * @param int|bool $storeId Current store ID
   */
  protected function _copyDataToSimpleProducts ($configurable,
                                                $changed,
                                                $storeId = false)
  {
    if (!$data = $this->_getDataForCopying($configurable, $changed))
      return;

    $childrenIds = Mage::getModel('catalog/product_type_configurable')
      ->getUsedProductIds($configurable);

    if (!$childrenIds)
      return;

    //Merge data from parent product to children and save them
    foreach ($childrenIds as $childId) {
      $child = Mage::getModel('catalog/product')
        ->setStoreId($storeId)
        ->load($childId)
        ->addData($data);

      if ($storeId !== false)
        $this->_unsetValues($child, $data);

      $child->save();
    }
  }

  /**
   * Get data from configurable product for updated attributes and only selected
   * attributes on the extension settings
   *
   * @param $configurable Configurable product
   * @return array|null Data for copying into associated simple products
   */
  protected function _getDataForCopying ($configurable, $changed) {
    return array_intersect_key(
      $configurable->getData(),
      $changed,
      Mage::helper('productivity/attribute')->getReplicables($configurable)
    );
  }
}

