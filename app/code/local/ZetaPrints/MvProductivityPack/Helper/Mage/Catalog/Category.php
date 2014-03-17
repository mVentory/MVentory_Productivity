<?php

/**
 * Catalog category helper
 *
 * @category ZetaPrints
 * @package ZetaPrints_MvProductivityPack
 * @author ZetaPrints Ltd. <support@zetaprints.com>
 */

class ZetaPrints_MvProductivityPack_Helper_Mage_Catalog_Category
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