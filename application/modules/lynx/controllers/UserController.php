<?php

/** Zend_Controller_Action */
class Lynx_UserController extends Zend_Controller_Action
{
	public function init() {
		$this->manager = new Lynx_Model_Manager_Authenticated();
	}
	
    public function indexAction()
    {
    	$this->_forward('list');
    }
    
    /**
     * List the users links
     */
    public function listAction () {
    	$this->view->paginator = Zend_Paginator::factory($this->manager->getAllMarks());
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    }
    
    public function tagsAction () {
    	$tags = $this->manager->getTags();
    	foreach ($tags as $tag) {
    		$tag->setParam('url', $this->_helper->url('viewtag', 'user', 'lynx', array('tag' => $tag->getTitle())));
    	}
    	$this->view->cloud = new Zend_Tag_Cloud(array('itemList' => $tags));
    	$this->render('tags', null, true);
    }
    
    public function viewtagAction () {
    	$this->view->paginator = Zend_Paginator::factory($this->manager->getMarksForTag($this->_getParam('tag')));
    	$this->view->paginator->setCurrentPageNumber($this->_getParam('page'));
    	$this->render('list');
    }
}
