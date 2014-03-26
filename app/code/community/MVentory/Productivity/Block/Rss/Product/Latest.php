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
 * Block for displaying latest added products in RSS format
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Block_Rss_Product_Latest
  extends Mage_Rss_Block_Abstract {

  const ITEMS_NUMBER = 30;

  protected function _construct () {
    parent::_construct();

    $this
      ->addData(array(
        'cache_lifetime' => 1800,
        'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG),
      ));
  }

  /**
   * Get Key pieces for caching block content
   *
   * @return array
   */
  public function getCacheKeyInfo () {
    return array(
      'PRODUCTIVITY_RSS_PRODUCT_LATEST',
      $this->_getStoreId(),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
    );
  }

  protected function _toHtml () {
    $products = $this
                  ->getLayout()
                  ->createBlock('productivity/product_latest')
                  ->setProductsCount(self::ITEMS_NUMBER)
                  ->getProductCollection();

    $data = array(
      'title' => $this->__('Latest products'),
      'link' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
      'images' => array()
    );

    return $this
             ->helper('productivity/rss')
             ->generateFeedForProducts($products, $data);
  }
}
