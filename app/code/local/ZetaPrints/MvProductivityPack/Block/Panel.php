<?php
/**
 * Block for frontend top panel
 *
 * @category ZetaPrints_MvProductivityPack_Block_Panel
 * @package  ZetaPrints_MvProductivityPack
 * @author ZetaPrints
 */
class ZetaPrints_MvProductivityPack_Block_Panel
  extends Mage_Core_Block_Template {

  const CONFIG_ANALYTICS_URL = 'google/analytics/productivity_analytics_url';

  protected function _getType () {
    return $this->getRequest()->getControllerName();
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
             ->getUrl('mvproductivitypack/adminhtml_index/index/', $params);
  }

  protected function _getCategoryLink () {
    if (!$category = Mage::registry('current_category'))
      return false;

    $params['id'] = $category->getId();

    return Mage::helper('adminhtml')
             ->getUrl('adminhtml/catalog_category/edit/', $params);
  }

  protected function _getWithoutImagesLink () {
    $params['_current'] = true;
    $params['_use_rewrite'] = true;
    $params['_query'] = array('without_images_only' => true);

    return Mage::getUrl('*/*/*', $params);
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
         // see ZetaPrints_MvProductivityPack_ProductController::saveAction()
         ->setAction(Mage::getUrl('catalog/product/save', array('id' => $product->getId())));
    $attributes = Mage::helper('MvProductivityPack')->getVisibleAttributes($product);
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
            ? $this->__('Edit individual products')
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
