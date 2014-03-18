<?php
/**
 * Block for category Frontshop Preview button
 *
 * @category   ZetaPrints_Productivity_Block_Adminhtml_Catalog_Category_Edit_Button
 * @package    ZetaPrints_Productivity
 * @author     ZetaPrints <anemets1@gmail.com>
 */
class ZetaPrints_Productivity_Block_Adminhtml_Catalog_Category_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get Category Id => Category Frontend Url jsoned array
   */
  public function getJson()
  {
    $categories_urls = array();
    $collection = Mage::getModel('catalog/category')->getCollection();
    foreach($collection as $category) {
      $categories_urls[$category->getId()] = $category->getUrl();
    }
    return Zend_Json::encode($categories_urls);
  }
}
