<?php

/** Zend_Controller_Action */
class Lynx_PublicController extends Zend_Controller_Action
{
    public function indexAction()
    {
//     	$this->_forward('index', 'catalogs');
    }
    
    /**
     * List the users links
     */
    public function listAction () {
    	$manager = new Lynx_Model_Manager_Public();
    	
    	foreach ($manager->getAllMarks() as $mark) {
    		var_dump($mark);
    	}
    	exit;
    }
}