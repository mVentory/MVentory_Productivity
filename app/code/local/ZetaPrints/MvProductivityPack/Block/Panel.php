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
      $field = array(
        'name'   => $attribute->getAttributeCode(),
        'label'  => $attribute->getFrontendLabel(),
        'values' => $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : null
      );
      $input = in_array($attribute->getFrontendInput(), $allowedInputs)
               ? $attribute->getFrontendInput()
               : 'text';
      $form->addField($attribute->getAttributeCode(), $input, $field)
           ->setRows(5); // in case it's a textarea, make it taller
    }

    $form->setValues($product->getData());
    // add field after values so "Submit" value is not overwritten
    $form->addField('submit', 'submit', array(
      'value'   => 'Save',
      'no_span' => true,
    ));
    return $form;
  }

}
