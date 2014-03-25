<?php

/**
 * Productivity
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE-OSL.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  ZetaPrints
 * @package   ZetaPrints_Productivity
 * @copyright Copyright (c) 2014 ZetaPrints Ltd. (http://www.zetaprints.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Category model
 *
 * @category ZetaPrints
 * @package  ZetaPrints_Productivity
 * @author   Anatoly A. Kazantsev <anatoly@zetaprints.com>
 */

class ZetaPrints_Productivity_Model_Mage_Catalog_Category
  extends Mage_Catalog_Model_Category {

  const DISPLAY_DESCENDING_PRODUCTS
    = 'catalog/frontend/display_descending_products';

  const HIDE_EMPTY_CATEGORIES = 'catalog/navigation/hide_empty_categories';

  /**
   * Get category products collection
   *
   * The method is redefined to load all descending products when it's allowed
   * and category is not anchor (anchor categories load descending products
   * by default)
   *
   * @return Varien_Data_Collection_Db
   */
  public function getProductCollection () {
    if ($this->getIsAnchor()
        || !Mage::getStoreConfig(self::DISPLAY_DESCENDING_PRODUCTS))
      return parent::getProductCollection();

    return Mage::getResourceModel('catalog/product_collection')
             ->joinField('category_id',
                         'catalog/category_product',
                         'category_id',
                         'product_id = entity_id',
                         null,
                         'left')
             ->addAttributeToFilter('category_id',
                                    array('in' => $this->getAllChildren(true)))
             ->setStoreId($this->getStoreId());
  }

  /**
   * Retrieve categories by parent
   *
   * The method is redefined to show categories with more
   * then 1 subcategory only
   *
   * @param int $parent
   * @param int $recursionLevel
   * @param bool $sorted
   * @param bool $asCollection
   * @param bool $toLoad
   * @return mixed
   */
  public function getCategories($parent,
                                $recursionLevel = 0,
                                $sorted = false,
                                $asCollection = false,
                                $toLoad = true) {

    $categories = parent::getCategories(
      $parent,
      $recursionLevel,
      $sorted,
      $asCollection,
      $toLoad
    );

    return Mage::getStoreConfig(self::HIDE_EMPTY_CATEGORIES)
             ? $this->_removeParentCategories($categories)
               : $categories;
  }

  /**
   * Walk down categories tree and return first category with more
   * than one sub-category
   *
   * @param int $parent
   * @param int $recursionLevel
   * @param bool $sorted
   * @param bool $asCollection
   * @param bool $toLoad
   * @return mixed
   */
  private function _removeParentCategories ($categories) {
    if (count($nodes = $categories->getNodes()) != 1)
      return $categories;

    $children = end($nodes)->getChildren();

    if (!count($children->getNodes()))
      return $categories;

    return $this->_removeParentCategories($children);
  }
}
