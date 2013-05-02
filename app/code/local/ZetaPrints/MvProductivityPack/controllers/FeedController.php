<?php

class ZetaPrints_MvProductivityPack_FeedController
  extends Mage_Core_Controller_Front_Action {

  public function updateAction () {
    $request = $this->getRequest();

    if (!$url = $request->getParam('url'))
      return;

    Mage::getSingleton('core/cache')->clean($url);
  }
}
