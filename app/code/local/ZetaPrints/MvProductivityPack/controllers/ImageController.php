<?php

class ZetaPrints_MvProductivityPack_ImageController
  extends Mage_Core_Controller_Front_Action {

  public function preDispatch () {
    Mage::getSingleton('core/session', array('name' => 'adminhtml'))
      ->start();

    Mage::register('is_admin_logged',
                   Mage::getSingleton('admin/session')->isLoggedIn());

    parent::preDispatch();

    return $this;
  }

  protected function _isAdmin () {
    return Mage::registry('is_admin_logged') === true;
  }

  public function rotateAction () {
    if (!$this->_isAdmin())
      return;

    $request = $this->getRequest();

    if (!($request->has('file') && $request->has('rotate')))
      return;

    $file = $request->get('file');
    $rotate = $request->get('rotate');

    $angels = array('left' => 90, 'right' => -90);

    if (!($file && $rotate && array_key_exists($rotate, $angels)))
      return;

    Mage::helper('MvProductivityPack')
      ->rotate($file, $angels[$rotate]);
    
    $imageProp1 = $request->get('imageWidth')>$request->get('imageHeight')?
                  $request->get('imageWidth'):
                  $request->get('imageHeight');  
    
    $_product = Mage::getModel('catalog/product')
                  ->load($request->get('productId'));
    $type = $request->get('thumb') == 'true'?'thumbnail':'image';
    
    $imageProp2 = null;
    $imageFile = null;
    if($type == 'thumbnail') {
      $imageProp2 = $imageProp1;
      $imageFile = $file;
    }
		
    $image = Mage::helper('catalog/image')->init($_product, $type, $imageFile)
               ->resize($imageProp1, $imageProp2)->__toString();
    echo Zend_Json::encode(array('image'=>$image));

    return Zend_Json::encode(true);
  }

  public function removeAction () {
    if (!$this->_isAdmin())
      return;

    $request = $this->getRequest();

    if (!($request->has('file') && $request->has('product')))
      return;

    $file = $request->get('file');
    $productId = (int) $request->get('product');

    if (!($file && $productId >= 0))
      return;

    Mage::helper('MvProductivityPack')
      ->remove($file, $productId);
      
    if($request->get('thumb') != 'true') {
      $imageProp = $request->get('imageWidth')>$request->get('imageHeight')?
                   $request->get('imageWidth'):
                   $request->get('imageHeight');
      $_product = Mage::getModel('catalog/product')
                    ->load($request->get('productId'));
      $image = Mage::helper('catalog/image')->init($_product, 'image')
               ->resize($imageProp)->__toString();
      echo Zend_Json::encode(array('image'=>$image));
    }

    return Zend_Json::encode(true);
  }

  public function setmainAction () {
    if (!$this->_isAdmin())
      return;

    $request = $this->getRequest();

    if (!($request->has('thumbImage') 
        && $request->has('mainImage') && $request->has('product')))
      return;

    $file = $request->get('thumbImage');
    $productId = (int) $request->get('product');

    if (!($file && $productId >= 0))
      return;

    Mage::helper('MvProductivityPack')
      ->setMainImage($file, $productId);
      
    $imageThumbProp = $request->get('imageThumbWidth') > $request->get('imageThumbHeight')?
                  $request->get('imageThumbWidth'):
                  $request->get('imageThumbHeight');
                  
    $imageProp = $request->get('imageWidth') > $request->get('imageHeight')?
                  $request->get('imageWidth'):
                  $request->get('imageHeight');  
    
    $_product = Mage::getModel('catalog/product')
                  ->load($request->get('productId'));
    		
    $thumbImage = Mage::helper('catalog/image')
                    ->init($_product, 'thumbnail', $request->get('mainImage'))
                    ->resize($imageThumbProp, $imageThumbProp)
                    ->__toString();
               
    $mainImage = Mage::helper('catalog/image')
                   ->init($_product, 'image', $request->get('thumbImage'))
                   ->resize($imageProp)
                   ->__toString();
      
    echo Zend_Json::encode(array('thumbImage'=>$thumbImage,
                                 'mainImage'=>$mainImage));
                                 
    return Zend_Json::encode(true);
  }
}
