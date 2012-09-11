<?php
/**
 * Rewrite the ProductController to check the admin login on store front
 *
 * @category   Mage_Catalog_ProductController
 * @package    Inheritx_Sampleapp
 * @author     Inheritx Team <gaurav@inheritx.com>
 */
require_once("Mage/Catalog/controllers/ProductController.php");
class Inheritx_Sampleapp_ProductController extends Mage_Catalog_ProductController
{
	/*
	 * Below preDispatch method will check whether admin is logged in or not on admin side and add the value in 
	 * registry 
	 */
	public function preDispatch () {
		Mage::getSingleton('core/session', array('name' => 'adminhtml'))
		  ->start();

		Mage::register('is_admin_logged',
					   Mage::getSingleton('admin/session')->isLoggedIn());

		parent::preDispatch();

		return $this;
	}
}
