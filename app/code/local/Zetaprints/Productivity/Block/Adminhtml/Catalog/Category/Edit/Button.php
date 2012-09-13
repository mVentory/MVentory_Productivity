<?php
/**
 * Block for category Frontshop Preview button
 *
 * @category   Zetaprints_Productivity_Block_Adminhtml_Catalog_Category_Edit_Button
 * @package    Zetaprints_Productivity
 * @author     Zetaprints <anemets1@gmail.com>
 */
class Zetaprints_Productivity_Block_Adminhtml_Catalog_Category_Edit_Button extends Mage_Core_Block_Template
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
    return json_encode($categories_urls); 
  }
}
