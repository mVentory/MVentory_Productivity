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
 * Helper
 *
 * @package MVentory/Productivity
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_Productivity_Helper_Data
  extends Mage_Core_Helper_Abstract {

  const ATTRIBUTE_CODE = 'media_gallery';
  const REVIEWER_GROUP_CODE = 'REVIEWER';

  private $_mediaBackend = array();

  public function rotate ($file, $angle) {
    $image = Mage::getModel('catalog/product_image');

    $image
      ->setBaseFile($file)
      ->setQuality(100)
      ->setKeepFrame(false)
      ->rotate($angle)
      ->saveFile();

    return $image->getNewFile();
  }

  public function remove ($file, $product) {
    $backend = $this->_getMediaBackend($product);

    if (!$backend->getImage($product, $file))
      throw new MVentory_Productivity_Exception('No image in the product');

    $backend->removeImage($product, $file);
    $product->save();

    return true;
  }

  public function setMainImage ($file, $product) {
    $backend = $this->_getMediaBackend($product);

    if (!$backend->getImage($product, $file))
      throw new MVentory_Productivity_Exception('No image in the product');

    $currentImage = $product->getImage();

    if ($currentImage) {
      $data = $backend->getImage($product, $currentImage);
      $disabled = $data['disabled'];

      unset($data);

      $backend->updateImage($product, $currentImage, array('exclude' => false));
    }

    if (isset($disabled) && $disabled)
      $backend->updateImage($product, $file, array('exclude' => true));

    $backend->setMediaAttribute(
      $product,
      array('image', 'small_image', 'thumbnail'),
      $file
    );

    $product->save();

    return true;
  }

  public function updateImageInGallery ($oldFile, $newFile, $product,
                                        $mediaAttributes = null, $move = true,
                                        $exclude = false) {

    $backend = $this->_getMediaBackend($product);

    $backend->removeImage($product, $oldFile);

    $file = $backend->addImage(
      $product,
      $newFile,
      $mediaAttributes,
      $move,
      $exclude
    );

    $product->save();

    return $file;
  }

  public function add ($product, $data) {
    $backend = $this->_getMediaBackend($product);

    $gallery = $product->getData(self::ATTRIBUTE_CODE);

    $mediaAttributes = null;

    if (!(isset($gallery['images']) && $gallery['images']))
      $mediaAttributes = array_keys($product->getMediaAttributes());

    $backend->addImage(
      $product,
      $data['path'] . $data['file'],
      $mediaAttributes,
      true,
      false
    );

    $product->save();

    $gallery = $product->getData(self::ATTRIBUTE_CODE);

    if (!(isset($gallery['images']) && $gallery['images']))
      throw new MVentory_Productivity_Exception('Image gallery is not loaded');

    $image = end($gallery['images']);

    if (!(isset($image['new_file'], $image['file'])
          && $image['new_file']
          && $image['file']))
      throw new MVentory_Productivity_Exception(
        'No newly added image in the product'
      );

    return $image['file'];
  }

  protected function _getMediaBackend ($product) {
    if (!$id = $product->getId())
      throw new MVentory_Productivity_Exception('Product is not loaded');

    if (isset($this->_mediaBackend[$id]))
      return $this->_mediaBackend[$id];

    $attributes = $product
      ->getTypeInstance(true)
      ->getSetAttributes($product);

    if (!isset($attributes[self::ATTRIBUTE_CODE]))
      throw new MVentory_Productivity_Exception(
        'Attribute ' . self::ATTRIBUTE_CODE . 'doesn\'t exist in product\'s '
        . 'attribute set'
      );

    $gallery = $attributes[self::ATTRIBUTE_CODE];
    $backend = $gallery->getBackend();

    $this->_mediaBackend[$id] = $backend;

    return $backend;
  }

  public function isAdminLogged () {
    return Mage::registry('is_admin_logged') === true;
  }

  public function isReviewerLogged () {
    if ($this->isAdminLogged())
      return true;
    
    $session = Mage::getSingleton('customer/session');

    $groupId = $session->getCustomerGroupId();

    if ($groupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
      return false;

    if ($session->getCustomer()->getWebsiteId()
          != Mage::app()->getWebsite()->getId())
      return false;

    $group = Mage::getModel('customer/group')->load($groupId);

    if (!$group->getId())
      return false;

    return strcasecmp($group->getCode(), self::REVIEWER_GROUP_CODE) == 0;
  }

  /**
   * Sends email to stores's general contant address
   *
   * @param string $subject
   * @param string $message
   * @param int|string|Mage_Core_Model_Store $store Store, its ID or code
   */
  public function sendEmail ($subject,
                             $message,
                             $store = Mage_Core_Model_App::ADMIN_STORE_ID) {

    $store = Mage::app()->getStore($store);

    $email = $store->getConfig('trans_email/ident_general/email');
    $name = $store->getConfig('trans_email/ident_general/name');

    Mage::getModel('core/email')
      ->setFromEmail($email)
      ->setFromName($name)
      ->setToName($name)
      ->setToEmail($email)
      ->setBody($message)
      ->setSubject($subject)
      ->send();
  }

  public function isMVentoryModuleEnabled () {
    return parent::isModuleEnabled('MVentory_API');
  }

  /**
   * Get Images html
   *
   * @param int $productId
   * @return string $html
   */
  public function getProductImagesToHtml($productId = null){
    $html = '';
    if ($productId != null) {     
      $imageCollection = $this->_getProductMediaGallery($productId);   

      foreach ($imageCollection as $key => $image) {        
        $u_id = uniqid();        
        $html .='<li id="'.$u_id.'" class="product-media-image-gallery"  
              image="'.$key.'" type="'.$image["type"].'" 
              style="background-image: url('.$image["url"].')"></li>';
      }

    }
    return $html;
  }

  /**
   * Get Media Gellery array
   *
   * @param Mage_Catalog_Model_Product $_product
   * @return array $imageList
   */
  protected function _getProductMediaGallery($_product){    
    $imageList = array();         

    if (!$_product instanceof Mage_Catalog_Model_Product) {
      return  $imageList;
    }

    /* Checks if product has default image */
    if ($_product->getImage()!='no_selection'){

        $imageList[$_product->getImage()] = array('url'=>
                    $this->_getResizedImageUrl($_product, $_product->getImage())
                    ,'type'=>'image');        
    }
      
    foreach ($_product->getMediaGalleryImages() as $image){
      if(!array_key_exists($image->getFile(), $imageList)){
        $resizedUrl = $this->_getResizedImageUrl($_product, $image->getFile());
        $imageList[$image->getFile()] = array('url'=>$resizedUrl, 'type'=>'thumbnail');    
      }        
    }
    return $imageList;
  }

  /**
   * Return resized image url 
   *
   * @param Mage_Catalog_Product $_product
   * @param string $_file
   * @return string
   */
  protected function _getResizedImageUrl($_product, $_file){
      return (string)Mage::helper('catalog/image')
        ->init($_product, 'image', $_file)        
        ->resize(100);
  }

}
