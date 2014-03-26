<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package MVentory/Productivity
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Breadcrumbs block
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Block_Mage_Page_Html_Breadcrumbs
  extends Mage_Page_Block_Html_Breadcrumbs {

  const PATH_HOME_URL = 'catalog/navigation/home_url';

  /*
   * Replaces link in 'home' crumb with external home URL if it's set
   * and adds 'shop' crumb with link to Magento's home page
   * after the 'home' crumb
   *
   * @return Mage_Core_Block_Abstract
   */
  protected function _beforeToHtml () {
    if (is_array($this->_crumbs)
        && isset($this->_crumbs['home'])
        && ($url = Mage::getStoreConfig(self::PATH_HOME_URL))) {

      $shopUrl = $this->_crumbs['home']['link'];

      $this->_crumbs['home']['link'] = $url;
      $this->_crumbs['home']['first'] = null;
      $this->_crumbs['home']['last'] = null;

      $crumbs = array();

      foreach ($this->_crumbs as $name => $crumb) {
        $crumbs[$name] = $crumb;

        if ($name == 'home')
          $crumbs['shop'] = array(
            'label' => $this->__('Products'),
            'title' => null,
            'link' => $shopUrl,
            'first' => null,
            'last' => null,
            'readonly' => null
          );
      }

      $this->_crumbs = $crumbs;
    }

    return parent::_beforeToHtml();
  }
}
