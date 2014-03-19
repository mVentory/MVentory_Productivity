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
 * Catalog category helper
 *
 * @category ZetaPrints
 * @package  ZetaPrints_Productivity
 * @author   Anatoly A. Kazantsev <anatoly@zetaprints.com>
 */

class ZetaPrints_Productivity_Helper_Mage_Catalog_Category
  extends Mage_Catalog_Helper_Category {

  /**
   * Check if a category can be shown
   *
   * The method is redefined to allow access to website's root category.
   * We need it for showing or filtering over all products in the store.
   *
   * @param Mage_Catalog_Model_Category|int $category
   * @return boolean
   */
  public function canShow ($category) {
    if (is_int($category))
      $category = Mage::getModel('catalog/category')->load($category);

    if (!$categoryId = $category->getId())
      return false;

    if (!$category->getIsActive())
      return false;

    $isRootCategory
      = Mage::app()->getStore()->getRootCategoryId() == $categoryId;

    if (!($isRootCategory || $category->isInRootCategoryList()))
      return false;

    return true;
  }
}