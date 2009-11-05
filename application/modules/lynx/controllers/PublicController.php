<?php

/** Zend_Controller_Action */
class Lynx_PublicController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	$this->_forward('list');
    }
    
    /**
     * List the users links
     */
    public function listAction () {
    	$manager = new Lynx_Model_Manager_Public();
    	$this->view->paginator = Zend_Paginator::factory($manager->getAllMarks());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    }
}
