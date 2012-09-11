<?php
/**
 * Rewrite the IndexController to check the admin login on store front
 *
 * @category   Mage_Cms_IndexController
 * @package    Inheritx_Sampleapp
 * @author     Peexl <anemets1@gmail.com>
 */
require_once("Mage/Cms/controllers/IndexController.php");
class Inheritx_Sampleapp_Cms_IndexController extends Mage_Cms_IndexController
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
