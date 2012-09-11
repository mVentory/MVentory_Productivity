<?php

/**
 * Block to retrieve data if admin is logged in
 *
 * @package    Inheritx_Sampleapp
 * @author     Peexk <anemets1@gmail.com>
 */

class Inheritx_Sampleapp_Block_Panel extends Mage_Core_Block_Template {
   
    public function isAdmin() {   
		  return Mage::registry('is_admin_logged');
    }

}