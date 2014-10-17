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
 * Category tree model
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */

class MVentory_Productivity_Model_Resource_Category_Tree
  extends Mage_Catalog_Model_Resource_Category_Tree
{
  /**
   * Add specified or new collection of categories to category tree. Filter
   * out disabled and non-active categories (if $onlyActive parameter is True)
   * and categories with IDs from $exclude parameter. Load category data and
   * add it to tree nodes if $toLoad parameter is True
   *
   * NOTE: this method is redefined to remove filtering out categories
   *       by 'include_in_menu' field for expanding hidden categories in menu
   *
   * !!!IMPORTANT: this method should stay synchronized with original one.
   *               Changes on original method should be inspected and applied
   *               on every Magento release.
   *
   * @param Mage_Catalog_Model_Resource_Category_Collection $collection
   * @param boolean|string $sorted Sort collection of categories by specified
   *                               field or by 'name' field if is True
   * @param array $exclude List of category IDs to exclude
   * @param boolean $toLoad Load or not category data
   * @param boolean $onlyActive Load only active categories
   * @return Mage_Catalog_Model_Resource_Category_Tree
   */
  public function addCollectionData ($collection = null,
                                     $sorted = false,
                                     $exclude = array(),
                                     $toLoad = true,
                                     $onlyActive = false)
  {
    if (is_null($collection))
      $collection = $this->getCollection($sorted);
    else
      $this->setCollection($collection);

    if (!is_array($exclude))
      $exclude = array($exclude);

    //!!!TODO: This can be combined with filtering $disabledIds.
    //Just merge $exclude with $disabledIds and use it for filtering by
    //'entity_id' field with 'nin' condition.
    $nodeIds = array();

    foreach ($this->getNodes() as $node)
      if (!in_array($node->getId(), $exclude))
        $nodeIds[] = $node->getId();

    $collection->addIdFilter($nodeIds);

    if ($onlyActive) {
      $disabledIds = $this->_getDisabledIds($collection);

      if ($disabledIds)
        $collection->addFieldToFilter(
          'entity_id',
          array('nin' => $disabledIds)
        );

      $collection->addAttributeToFilter('is_active', 1);

      //In original method it filters out categories by 'include_in_menu'
      //field, but we don't need it for expanding hidden ('include_in_menu' = 0)
      //categories. So this filter was removed and following line was added
      //to load values of 'include_in_menu' field in collection's entities
      $collection->addAttributeToSelect('include_in_menu');
    }

    if ($this->_joinUrlRewriteIntoCollection) {
      $collection->joinUrlRewrite();
      $this->_joinUrlRewriteIntoCollection = false;
    }

    if ($toLoad) {
      $collection->load();

      foreach ($collection as $category)
        if ($node = $this->getNodeById($category->getId()))
          $node->addData($category->getData());

      foreach ($this->getNodes() as $node)
        if (!$collection->getItemById($node->getId()) && $node->getParent())
          $this->removeNode($node);
    }

    return $this;
  }
}
