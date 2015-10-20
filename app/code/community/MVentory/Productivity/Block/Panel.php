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
 * Block for productivity panel
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Block_Panel
  extends Mage_Core_Block_Template {

  protected function _getType () {
    return $this->getRequest()->getControllerName();
  }

  protected function _getCurrentUrl () {
    return $this->getUrl(
      null,
      array('_use_rewrite' => true, '_current' => true)
    );
  }

  protected function _getProductLink () {
    $params['id'] = Mage::registry('product')->getId();

    return Mage::helper('adminhtml')
             ->getUrl('adminhtml/catalog_product/edit/', $params);
  }

  protected function _getCmsPageLink () {
    $page = $this->getHelper('cms/page')->getPage();

    if (!$pageId = $page->getPageId())
      return false;

    $params['page_id'] = $pageId;

    return Mage::helper('adminhtml')
             ->getUrl('productivity_admin/adminhtml_index/index/', $params);
  }

  protected function _getCategoryLink () {
    if (!$category = Mage::registry('current_category'))
      return false;

    $params['id'] = $category->getId();

    return Mage::helper('adminhtml')
             ->getUrl('adminhtml/catalog_category/edit/', $params);
  }

  protected function _getWithoutImagesLink () {
    if ($isCategoryPage = $this->_getType() == 'category') {
      $params['_current'] = true;
      $params['_use_rewrite'] = true;
    }

    $params['_query'] = array('without_images_only' => true);

    return Mage::getUrl(
      $isCategoryPage ? '*/*/*' : 'catalog/category/all',
      $params
    );
  }

  public function _getAnalyticsUrl () {
    return Mage::getStoreConfig(
      MVentory_Productivity_Model_Config::_ANALYTICS_URL
    );
  }

  public function _getHelpUrl () {
    return 'http://mventory.com/help/toolbar/mventory-toolbar/';
  }

  /**
   * Get Express Order url
   * @return string
   */
  public function getExpressOrderUrl () {
    return Mage::getUrl('productivity/sales/expressOrder');
  }

  /**
   * Build a form object populated with product data.
   *
   * @return Varien_Data_Form
   */
  public function getEditForm() {
    $helper = Mage::helper('productivity/attribute');

    /* @var $product Mage_Catalog_Model_Product */
    $product = Mage::registry('product');

    $dateFormat = Mage::app()->getLocale()->getDateFormat();
    $isConfigurable
      = $product->getTypeId()
          == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE;

    $allowedInputs = array(
      'label' => true,
      'text' => true,
      'textarea' => true,
      'date' => true,
      'select' => true,
      'multiselect' => true
    );

    $notallowedInputs = array(
        'date' => true,
    );

    $product['qty'] = $isConfigurable
                        ? $this->__('Edit individual sub-products')
                          : (int) $product->getStockItem()->getQty();

    $form = new Varien_Data_Form();

    $form
      ->setUseContainer(true)
      ->setMethod('post')
      /* @see MVentory_Productivity_ProductController::saveAction() */
      ->setAction(
          Mage::getUrl('catalog/product/save', array('id' => $product->getId()))
        );

    /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
    foreach ($helper->getEditables($product) as $code => $attribute) {
      $input = $attribute->getFrontendInput();
      if (isset($notallowedInputs[$input]))
        continue;

      $form->addField(
        $code,
        isset($allowedInputs[$input]) ? $input : 'text',
        array(
          'name' => $code,
          'label'  => trim($attribute->getStoreLabel()),
          'values' => $values = $attribute->usesSource()
                        ? $attribute->getSource()->getAllOptions()
                          : null,

          //In case it's a textarea, make it taller
          'rows' => 5
        ),
        ($after = $attribute['_insert_after']) ? $after : false
      );
    }

    $form->setValues($product->getData());

    //Add field after values so "Submit" value is not overwritten
    $form->addField('submit', 'submit', array(
      'value'   => 'Save',
      'no_span' => true,
    ));

    $form->addField('cancel', 'button', array(
      'value'   => 'Cancel',
      'no_span' => true,
    ));

    return $form;
  }

}
