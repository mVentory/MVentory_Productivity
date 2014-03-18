<?php
/**
 * Block for cms page Frontshop Preview button
 *
 * @category   ZetaPrints_Productivity_Block_Adminhtml_Cms_Page_Edit_Button
 * @package    ZetaPrints_Productivity
 * @author     ZetaPrints <anemets1@gmail.com>
 */
class ZetaPrints_Productivity_Block_Adminhtml_Cms_Page_Edit_Button extends Mage_Core_Block_Template
{
  /**
   * Get cms page frontend url
   */
  public function getPageUrl()
  {
    return Mage::getBaseUrl() . Mage::registry('cms_page')->getIdentifier();
  }
}
