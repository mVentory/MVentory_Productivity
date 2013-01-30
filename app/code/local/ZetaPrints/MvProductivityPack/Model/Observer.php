<?php

class ZetaPrints_MvProductivityPack_Model_Observer {
  public function addCategoryFeed ($observer) {
    $helper = Mage::helper('core/url');

    $url = $helper->getCurrentUrl();
    $url = $helper->addRequestParam($url, array('rss' => 1));

    Mage::helper('MvProductivityPack/rss')->addFeedToHeader($url);
  }
}
