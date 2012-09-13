<?php
/**
 * Block for cms page Frontshop Preview button
 *
 * @category   Zetaprints_Productivity_Block_Adminhtml_Cms_Page_Edit_Button
 * @package    Zetaprints_Productivity
 * @author     Zetaprints <anemets1@gmail.com>
 */
class Zetaprints_Productivity_Block_Adminhtml_Cms_Page_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get product frontend url
   */
  public function getPageUrl()
  {         
    return '/'.Mage::registry('cms_page')->getIdentifier(); 
  }
}
