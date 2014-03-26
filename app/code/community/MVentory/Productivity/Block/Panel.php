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

  const CONFIG_ANALYTICS_URL = 'google/analytics/productivity_analytics_url';

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
    return Mage::getStoreConfig(self::CONFIG_ANALYTICS_URL);
  }

  public function _getHelpUrl () {
    return 'http://mventory.com/help/toolbar/mventory-toolbar/';
  }

  /**
   * Build a form object populated with product data.
   *
   * @return Varien_Data_Form
   */
  public function getEditForm() {
    /* @var $product Mage_Catalog_Model_Product */
  	$product = Mage::registry('product');
  	$form = new Varien_Data_Form();
    $form->setUseContainer(true)
         ->setMethod('post')
         // see MVentory_Productivity_ProductController::saveAction()
         ->setAction(Mage::getUrl('catalog/product/save', array('id' => $product->getId())));
    $attributes = Mage::helper('productivity')->getVisibleAttributes($product);
    $allowedInputs = array('text', 'textarea', 'date', 'select', 'multiselect');

    /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
    foreach ($attributes as $attribute) {
      $label = trim($attribute->getStoreLabel());

      //Support for features of MVentory extension
      //Hide attributes which are disabled in the current store
      if ($label == '~')
        continue;

      $values = $attribute->usesSource()
                  ? $attribute->getSource()->getAllOptions()
                    : null;

      //Support for features of MVentory extension
      //Hide attribute's values which are disabled in the current store
      if ($values) {
        foreach ($values as $i => $value)
          if (strpos($value['label'], '~') === 0)
            unset($values[$i]);

        //Also hide attribute if it doesn't have allowed values
        if (count($values) < 2)
          continue;
      }

      $field = array(
        'name'   => $attribute->getAttributeCode(),
        'label'  => $label,
        'values' => $values
      );
      $input = in_array($attribute->getFrontendInput(), $allowedInputs)
               ? $attribute->getFrontendInput()
               : 'text';
      $form->addField($field['name'], $input, $field)
           ->setFormat(Mage::app()->getLocale()->getDateFormat()) // for date, time & datetime fields
           ->setRows(5); // in case it's a textarea, make it taller
    }

    $form->setValues($product->getData());

    $isConfigurable = $product->getTypeId() == 'configurable';

    $form
      ->addField(
          'qty',
          $isConfigurable ? 'label' : 'text',
          array(
            'name' => 'qty',
            'label'  => 'Qty',
          ),
          'price'
        )
      ->setValue(
          $isConfigurable
            ? $this->__('Edit individual sub-products')
              : $product->getStockItem()->getQty() * 1
        );

    // add field after values so "Submit" value is not overwritten
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
