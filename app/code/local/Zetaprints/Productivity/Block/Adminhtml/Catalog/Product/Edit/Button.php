<?php
/**
 * Block for product Frontshop Preview button
 *
 * @category   Zetaprints_Productivity_Block_Adminhtml_Catalog_Product_Edit_Button
 * @package    Zetaprints_Productivity
 * @author     Zetaprints <anemets1@gmail.com>
 */
class Zetaprints_Productivity_Block_Adminhtml_Catalog_Product_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get product frontend url
   */
  public function getProductUrl()
  {         
    return Mage::registry('product')->getUrlInStore(); 
  }
}
