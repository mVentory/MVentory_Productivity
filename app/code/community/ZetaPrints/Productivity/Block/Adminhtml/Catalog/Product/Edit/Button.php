<?php
/**
 * Block for product Frontshop Preview button
 *
 * @category   ZetaPrints_Productivity_Block_Adminhtml_Catalog_Product_Edit_Button
 * @package    ZetaPrints_Productivity
 * @author     ZetaPrints <anemets1@gmail.com>
 */
class ZetaPrints_Productivity_Block_Adminhtml_Catalog_Product_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get product frontend url
   */
  public function getProductUrl()
  {
    return Mage::registry('product')->getProductUrl();
  }
}
