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
 * Block for displaying external feeds
 *
 * @category ZetaPrints
 * @package  ZetaPrints_Productivity
 * @author   Anatoly A. Kazantsev <anatoly@zetaprints.com>
 */

class ZetaPrints_Productivity_Block_Rss_Import
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
    $tag = md5($this->getData('post_link'));

    $parentBlock = $this;

    //Find parent block which caches its content and add the tag
    //to prevent effect of nested caching
    while ($parentBlock = $parentBlock->getParentBlock())
      if ($parentBlock instanceof Mage_Core_Block_Template
          && !($parentBlock instanceof Mage_Page_Block_Html)) {

        $tags = (array) $parentBlock->getData('cache_tags');

        $tags[] = $tag;

        $parentBlock->setData('cache_tags', $tags);

        break;
      }

    $tags = parent::getCacheTags();

    $tags[] = $tag;

    return $tags;
  }

  public function getFeed () {
    $feed = $this->getData('feed');

    if ($feed)
      return $feed;

    $uri = $this->getUri();

    if (!$uri)
      return null;

    try {
      $feed = Zend_Feed::import($uri);
    } catch (Exception $e) {
      return null;
    }

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
