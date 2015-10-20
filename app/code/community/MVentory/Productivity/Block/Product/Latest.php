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
 * Block for displaying latest added products
 *
 * Use cat_id to pass optional category id.
 *  
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Block_Product_Latest
  extends Mage_Catalog_Block_Product_Abstract {

  protected $_productsCount = null;

  const DEFAULT_PRODUCTS_COUNT = 6;

  /**
   * Initialize block's cache
   */
  protected function _construct () {
    parent::_construct();

    $this
      ->addColumnCountLayoutDepend('two_columns_left', 3)
      ->addColumnCountLayoutDepend('two_columns_right', 3);

    $this
      ->addData(array(
        'cache_lifetime' => 86400,
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
      'CATALOG_PRODUCT_LATEST',
      Mage::app()->getStore()->getId(),
      Mage::getDesign()->getPackageName(),
      Mage::getDesign()->getTheme('template'),
      Mage::getSingleton('customer/session')->getCustomerGroupId(),
      ($cat_id = $this->getData('cat_id')) ? $cat_id : null,
      'template' => $this->getTemplate(),
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
    
    $cat_id=$this->getData('cat_id');
    if ($cat_id && $category=Mage::getModel('catalog/category')->load($cat_id))
      $collection->addCategoryFilter($category);

    $imageFilter = array('nin' => array('no_selection', ''));

    $collection = $this
                    ->_addProductAttributesAndPrices($collection)
                    ->addAttributeToFilter('small_image', $imageFilter)
                    ->addStoreFilter()
                    ->addAttributeToSort('entity_id', 'desc')
                    ->setPageSize($this->getProductsCount())
                    ->setCurPage(1);

    $this->setData('product_collection', $collection);

    return $collection;
  }

  /**
   * Set how much product should be displayed at once.
   *
   * @param $count
   * @return Mage_Catalog_Block_Product_New
   */
  public function setProductsCount ($count) {
    $this->_productsCount = $count;

    return $this;
  }

  /**
   * Get how much products should be displayed at once.
   *
   * @return int
   */
  public function getProductsCount () {
    if (null === $this->_productsCount)
      $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;

    return $this->_productsCount;
  }
}
