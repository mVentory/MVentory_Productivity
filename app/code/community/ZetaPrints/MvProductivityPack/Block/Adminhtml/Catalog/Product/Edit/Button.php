<?php
/**
 * Block for product Frontshop Preview button
 *
 * @category   ZetaPrints_MvProductivityPack_Block_Adminhtml_Catalog_Product_Edit_Button
 * @package    ZetaPrints_MvProductivityPack
 * @author     ZetaPrints <anemets1@gmail.com>
 */
class ZetaPrints_MvProductivityPack_Block_Adminhtml_Catalog_Product_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get product frontend url
   */
  public function getProductUrl()
  {         
    return Mage::registry('product')->getProductUrl(); 
  }
}
