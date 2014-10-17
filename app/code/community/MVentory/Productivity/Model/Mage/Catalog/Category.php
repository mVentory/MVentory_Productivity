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
 * Category model
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Model_Mage_Catalog_Category
  extends Mage_Catalog_Model_Category {

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
    $default = $this->getIsAnchor()
               || !Mage::getStoreConfig(
                     MVentory_Productivity_Model_Config::_DISPLAY_PRODUCTS
                   );

    if ($default)
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

    $flattenType = (int) Mage::getStoreConfig(
      MVentory_Productivity_Model_Config::_CATEGORY_FLATTEN_TREE
    );

    if ($flattenType == MVentory_Productivity_Model_Config::FLATTEN_EXPAND)
      return $this->_expandCategories($this->_getCategories(
        $parent,
        $recursionLevel,
        $sorted,
        $asCollection,
        $toLoad
      ));

    $categories = parent::getCategories(
      $parent,
      $recursionLevel,
      $sorted,
      $asCollection,
      $toLoad
    );

    return $flattenType == MVentory_Productivity_Model_Config::FLATTEN_PATHS
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

  /**
   * Expand recursively hidden (include_in_menu = 0) categories. Preserve
   * order of categories. Hide empty (no subcategories) and hidden categories.
   *
   * @param Varien_Data_Tree_Node_Collection $categories Collection of nodes
   * @return Varien_Data_Tree_Node_Collection
   */
  protected function _expandCategories ($categories) {
    if (!$categories->count())
      return $categories;

    foreach ($categories as $id => $category) {
      $children = $this->_expandCategories($category->getChildren());

      $noChildren = !$children->count();
      $notIncludeInMenu = !$category->getIncludeInMenu();

      //Hide empty and not included in menu categories
      if ($notIncludeInMenu && $noChildren) {
        $categories->delete($category);

        continue;
      }

      //Ignore empty categories
      if ($noChildren)
        continue;

      //Remember hidden category to expand on next step
      if ($notIncludeInMenu)
        $replace[$id] = true;
    }

    if (isset($replace)) {

      //Expand hidden categories and re-add visible categories as is to preserve
      //original order
      foreach ($categories as $id => $category) {
        $categories->delete($category);

        if (isset($replace[$id])) {

          //Copy level and position values of expanded category to it's children
          $level = $category->getLevel();
          $position = $category->getPosition();

          foreach ($category->getChildren() as $child) {
            $child
              ->setLevel($level)
              ->setPosition($position);

            $category->removeChild($child);
            $categories->add($child);
          }
        } else
          $categories->add($category);
      }
    }

    return $categories;
  }

  /**
   * Retrieve categories
   *
   * This method is copy of
   * Mage_Catalog_Model_Resource_Category::getCategories(), but with changed
   * category tree model for expanding hidden categories functionality.
   *
   * !!!TODO: move to some helper class to minimize confusion with
   *          getCategories() method of this class
   *
   * @param integer $parent
   * @param integer $recursionLevel
   * @param boolean|string $sorted
   * @param boolean $asCollection
   * @param boolean $toLoad
   * @return Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Category_Collection
   */
  protected function _getCategories ($parent,
                                     $recursionLevel = 0,
                                     $sorted = false,
                                     $asCollection = false,
                                     $toLoad = true)
  {
    /* @var $tree MVentory_Productivity_Model_Resource_Category_Tree */
    $tree = Mage::getResourceModel('productivity/category_tree');

    $node = $tree
      ->loadNode($parent)
      ->loadChildren($recursionLevel);

    $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);

    return $asCollection ? $tree->getCollection() : $node->getChildren();
  }
}
