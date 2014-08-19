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
    $helper = Mage::helper('productivity');

    if ($helper->isReviewerLogged())
    {
      //Check if save scope is set and we need to load product
      //with dara from current store
      $hasScope = (int) Mage::getStoreConfig(
        MVentory_Productivity_Model_Config::_PRODUCT_SAVE_SCOPE
      );
      $hasScope = MVentory_Productivity_Model_Config::PRODUCT_SCOPE_CURRENT
                    == $hasScope;

      $product = Mage::getModel('catalog/product');

      //Set current store ID before laoding product if save scope is set,
      //e.g. user selected Current scope optin in admin interface
      if ($hasScope)
        $product->setStoreId($storeId);

      $product->load($productId);

      // Only allow certain attributes to be set, if missing product will not be changed.
      $data = array_intersect_key(
        $request->getPost(),
        $helper->getVisibleAttributes($product)
      );
      if (isset($data['description'])) {
        $data['short_description'] = $data['description'];
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

      if ($data) {
        $product->addData($data);

        //Set value of not changed attributes to false to prevents from copying
        //attribute's values to current scope if user selected Current store
        //option of Save edits to setting in admin interface
        //
        //Filter out following attributes:
        //  - Gallery attr (is a complex attr with it's own editor)
        //  - Global (setting value of global attr to false removes it globally)
        //  - Non-visible (non-visible attributes can't be edited directly)
        //  - Without input field (this attributes can'be edited directly)
        //
        //Filtering rules are taken from:
        //  - Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes::_prepareForm()
        //  - Mage_Adminhtml_Block_Widget_Form::_setFieldset()
        if ($hasScope) foreach ($product->getAttributes() as $code => $attr) {
          $allow = $code != 'gallery'
                   && $attr->getIsGlobal()
                        != Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
                   && $attr->getIsVisible()
                   && $attr->getFrontend()->getInputType();

          if (!$allow || isset($changeAttrs[$code]))
            continue;

          $product->setData($code, false);
        }

        //Product saves must be made from admin store
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $product->save();
        $this->_copyDataToSimpleProducts($product);

        //Reset store to be neat
        Mage::app()->setCurrentStore($storeId);
      }
    }

    // Always show same page, even if nothing has changed
    $this->_redirectUrl($product->setData('url', null)->getProductUrl());
  }

  /**
   * Copy data from configurable product to it associated
   * simple products
   * @param $product configurable product
   */
  protected function _copyDataToSimpleProducts($product){
    if($product->getTypeId() != 'configurable') return;
    $data = $this->_getDataForCopy($product);

    $childProducts = Mage::getModel('catalog/product_type_configurable')
        ->getUsedProducts(null, $product);

    //merge data from parent product to children and save them
    foreach($childProducts as $child) {
      $childData = $child->getData();
      $childData = array_merge($childData, $data);

      $child->setData($childData);
      $child->save();
    }
  }

  /**
   * Get data from configurable product with only selected
   * in configuration fields
   *
   * @param $product configurable product
   * @return array Data for copying to child(associated) simple products
   */
  protected function _getDataForCopy($product){
    $data = $product->getData();

    //get field names from config
    $config = Mage::getStoreConfig(
        MVentory_Productivity_Model_Config::_PRODUCT_COPY_FIELDS
    );

    $fieldsForCopy = explode(',',$config);
    $dataForCopy = array();

    //copy field values
    foreach($fieldsForCopy as $field){
      $dataForCopy[$field] = $data[$field];
    }

    return $dataForCopy;
  }

}

