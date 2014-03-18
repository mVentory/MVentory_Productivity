<?php

class ZetaPrints_MvProductivityPack_Block_Product_View
  extends Mage_Catalog_Block_Product_View {

  public function getMinimalQty ($product) {
    $minimalQty = parent::getMinimalQty($product);

    if (!$minimalQty)
      return $minimalQty;

    $stockItem = $product->getStockItem();

    return $stockItem->getIsQtyDecimal() ? $minimalQty : ceil($minimalQty);
  }
}
