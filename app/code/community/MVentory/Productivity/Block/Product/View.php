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
 * Product view block
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */

class MVentory_Productivity_Block_Product_View
  extends Mage_Catalog_Block_Product_View {

  public function getMinimalQty ($product) {
    $minimalQty = parent::getMinimalQty($product);

    if (!$minimalQty)
      return $minimalQty;

    $stockItem = $product->getStockItem();

    return $stockItem->getIsQtyDecimal() ? $minimalQty : ceil($minimalQty);
  }
}
