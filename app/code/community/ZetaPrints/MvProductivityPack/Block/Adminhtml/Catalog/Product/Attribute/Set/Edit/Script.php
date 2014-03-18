<?php
/**
 * Block Attribute Set edit template script
 *
 * @category   ZetaPrints
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */ 
class ZetaPrints_MvProductivityPack_Block_Adminhtml_Catalog_Product_Attribute_Set_Edit_Script 
  extends Mage_Core_Block_Template {
  
  public function getJson() {
    $a = array();
    $collection = Mage::getResourceModel('catalog/product_attribute_collection');
    foreach($collection as $attribute) {
      $a[$attribute->getData('attribute_code')] = $attribute->getData('attribute_id');;
    }

    return Zend_Json::encode($a);
  }
   
}