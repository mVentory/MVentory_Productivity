<?php

class ZetaPrints_MvProductivityPack_ImageController
  extends Mage_Core_Controller_Front_Action {
  
  const ATTRIBUTE_CODE = 'media_gallery';

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

    if (!($request->has('file') && $request->has('rotate')
          && $request->has('productId')))
      return;

    $file = $request->get('file');
    $rotate = $request->get('rotate');    
    $productId = $request->get('productId');

    $angels = array('left' => 90, 'right' => -90);

    if (!($file && $rotate && array_key_exists($rotate, $angels)))
      return;

    // rotate image and get new file
    $newFileAbsolute = Mage::helper('MvProductivityPack')
                         ->rotate($file, $angels[$rotate]);
    
    $type = $request->get('thumb') == 'true'?'thumbnail':'image';
    
    $imageProp1 = $request->get('imageWidth')>$request->get('imageHeight')?
                  $request->get('imageWidth'):
                  $request->get('imageHeight');   
    $imageProp2 = null;
    $imageFile = null;
    
    if($type == 'image') {  
      // update main product image and get new base filename      
      $file =
        Mage::helper('MvProductivityPack')
          ->updateImageInGallery($file, 
                                 $newFileAbsolute, 
                                 $productId, 
                                 array('image', 'small_image', 'thumbnail'));
      
    } else {
      $file = 
        Mage::helper('MvProductivityPack')
          ->updateImageInGallery($file, 
                                 $newFileAbsolute, 
                                 $productId);
      $imageProp2 = $imageProp1;
      $imageFile = $file;
    }        
    
    $_product = Mage::getModel('catalog/product')->load($productId);
		
    // get resized version of image
    $image = Mage::helper('catalog/image')->init($_product, $type, $imageFile)
               ->resize($imageProp1, $imageProp2)->__toString();
               
    $base = $file;
    
    echo Zend_Json::encode(compact('image', 'base'));

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
