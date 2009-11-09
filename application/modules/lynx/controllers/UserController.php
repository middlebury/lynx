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
    
    public function createAction () {
        $request = $this->getRequest();
        $form    = new Lynx_Form_Mark();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
            	$values = $form->getValues();
                $mark = $this->manager->createMark($values['url'], $values['description'], $values['notes'], $form->getTags());
                
                return $this->_helper->redirector('list');
            }
        }
        
        $this->view->form = $form;
    }
    
    public function editAction () {
        $request = $this->getRequest();
        $form    = new Lynx_Form_Mark();
        $mark = $this->manager->getMark($this->_getParam('mark'));
        
        $data = array(
        	'url'			=> $mark->url,
        	'description'	=> $mark->description,
        	'notes'			=> $mark->notes,
        );
        $form->populate($data);
        $form->populateTags($mark->tags);

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $mark->url = $form->getValue('url');
                $mark->description = $form->getValue('description');
                $mark->notes = $form->getValue('notes');
                $mark->tags = $form->getTags();                
                $this->manager->saveMark($mark);
                
                return $this->_helper->redirector('list');
            }
        }
        
        $this->view->form = $form;
    }
}
