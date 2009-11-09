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

        if ($request->isPost()) {
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
    
    public function deleteAction () {
    	$request = $this->getRequest();
        $post = $request->getPost();
    	if (!isset($post['no_ajax']) || !$post['no_ajax']) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
		}
        try {
			if (!$request->isPost())
				throw new Exception("Delete only responds to POST requests.");
			
			
			if (!isset($post['mark']))
				throw new Exception("Mark id not posted.");
			$markId = intval($post['mark']);
			if (!$markId)
				throw new Exception("Invalid mark id.");
			
			
			$this->manager->deleteMark($markId);
			
			// If this was a basic form submission, just redirect.
			if (isset($post['no_ajax']) && $post['no_ajax'])
				return $this->_helper->redirector('list');
			
			// If it was an AJAX submission return a basic document.
			$this->getResponse()->setHttpResponseCode(200);
			$this->getResponse()->setHeader('Content-Type', 'text/plain');
			print 'success';
		} catch (Exception $e) {
			$this->getResponse()->setHttpResponseCode(400);
			print 'Error: '.$e->getMessage();
        }	
   }
}
