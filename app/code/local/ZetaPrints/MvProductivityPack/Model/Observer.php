<?php

class ZetaPrints_MvProductivityPack_Model_Observer {
  public function addCategoryFeed ($observer) {
    $helper = Mage::helper('core/url');

    $url = $helper->getCurrentUrl();
    $url = $helper->addRequestParam($url, array('rss' => 1));

    Mage::helper('MvProductivityPack/rss')->addFeedToHeader($url);
  }

  public function addLatestProductsFeed ($observer) {
    $helper = Mage::helper('MvProductivityPack/rss');

    $title = $helper->__('Latest products');
    $url = Mage::getUrl('mvproductivitypackf/rss_product/latest');

    $helper->addFeedToHeader($url, $title);
  }

  public function addTopCategoriesFeed ($observer) {
    $helper = Mage::helper('MvProductivityPack/rss');

    $title = $helper->__('Top Categories');
    $url = Mage::getUrl('catalog/category/top');

    $helper->addFeedToHeader($url, $title);
  }
}
