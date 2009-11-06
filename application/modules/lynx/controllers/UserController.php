<?php

/** Zend_Controller_Action */
class Lynx_UserController extends Zend_Controller_Action
{
	public function init() {
		parent::init();
		$this->manager = new Lynx_Model_Manager_Authenticated();
		
		$nav = $this->view->navigation()->getContainer()->findOneBy('route', 'user');
		$nav->setActive(true);
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
    		$tag->setParam('url', $this->view->url(array('tag' => $tag->getTitle()), 'user_tag'));
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
