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
 * Widget to display latest products
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Block_Widget_Latest
  extends MVentory_Productivity_Block_Slideshow
{

  public function getCacheKeyInfo () {
    return array(
      'CATALOG_PRODUCT_LATEST',
      Mage::app()->getStore()->getId(),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
      'template' => $this->getData('item_template'),
      'image_size' => $this->getData('image_size'),
      $this->getProductsCount()
    );
  }

  public function getProductCollection () {
    $collection = $this->getData('product_collection');

    if ($collection)
      return $collection;

    $visibility = Mage::getSingleton('catalog/product_visibility')
      ->getVisibleInCatalogIds();

    $collection = Mage::getResourceModel('catalog/product_collection')
      ->setVisibility($visibility);

    $collection = $this
      ->_addProductAttributesAndPrices($collection)
      ->addAttributeToFilter(
          'small_image',
          array('nin' => array('no_selection', ''))
        )
      ->addStoreFilter()
      ->addAttributeToSort('entity_id', 'desc')
      ->setPageSize($this->getProductsCount())
      ->setCurPage(1);

    $this->setData('product_collection', $collection);

    return $collection;
  }
}
