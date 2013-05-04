<?php

class ZetaPrints_MvProductivityPack_Block_Rss_Import
  extends Mage_Core_Block_Template {

  public function __construct() {
    parent::__construct();

    //Default template
    $this->setTemplate('productivity/rss/import.phtml');

    //Default period
    $this->setCacheLifetime(86400);
  }

  public function getCacheKeyInfo () {
    $design = Mage::getDesign();

    return array_merge(
             parent::getCacheKeyInfo(),
             array(
               $this->getData('uri'),
               $design->getTheme('layout'),
               $design->getTheme('locale')
             )
           );
  }

  public function getCacheTags () {
    $tags = parent::getCacheTags();

    $tags[] = md5($this->getData('post_link'));

    return $tags;
  }

  public function getFeed () {
    $feed = $this->getData('feed');

    if ($feed)
      return $feed;

    $uri = $this->getUri();

    if (!$uri)
      return null;

    $feed = Zend_Feed::import($uri);

    $this->setFeed($feed);

    return $feed;
  }

  public function getContent () {
    if (!$feed = $this->getFeed())
      return false;

    if (!count($feed))
      return false;

    $post = $feed->current();

    $this->setData('post_link', $post->link());

    return $post->encoded();
  }
}
