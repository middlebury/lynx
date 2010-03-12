<?php

require_once(dirname(__FILE__).'/UserController.php');

class Lynx_UserapiController
	extends Lynx_UserController
{
	public function init() {
		parent::init();
		$this->_helper->layout->disableLayout();
		$this->getResponse()->setHeader('Content-Type', 'text/xml');
		
		print '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
		
		// Verify that API calls are coming from a valid proxy application 
		// and not directly from a user. This will limit the API's exposure
		// to Cross-Site Request Forgery attacks.
		// 
		// Since our CAS is configured to only allow proxying from trusted applications,
		// we can just check that there is a proxy. This could be updated in the future
		// to allow a configurable list of proxies.
		if (!count(phpCAS::getProxies())) {
			print "\n".'<result code="API method may only be called via a CAS proxy."/>';
			exit;
		}
	}

	/**
	 * List the users links
	 */
	public function allpostsAction () {
		if ($this->_getParam('tag'))
			$this->view->marks = $this->manager->getMarksForTag($this->_getParam('tag'));
		else if ($this->_getParam('query'))
			$this->view->marks = $this->manager->getMarksBySearch($this->_getParam('query'));
		else
			$this->view->marks = $this->manager->getAllMarks();
		
		$this->view->matches = count($this->view->marks);
		
		$start = intval($this->_getParam('start'));
		$results = intval($this->_getParam('results'));
		if (!$results)
			$results = null;
		if ($start || $results)
			$this->view->marks = array_slice($this->view->marks, $start, $results);
		
		$this->render('posts');
	}
	
	/**
	 * Answers particular links
	 */
	public function getpostsAction () {
		if ($this->_getParam('url')) {
			$this->view->marks = array($this->manager->getMarkByUrl($this->_getParam('url')));
		} else if ($this->_getParam('hashes')) {
			$this->view->marks = array();
			foreach (array_unique(explode(' ', $this->_getParam('hashes'))) as $hash) {
				$this->view->marks[] = $this->manager->getMarkByUrlHash($hash);
			}
		} else {
			$this->view->marks = array();
		}
		
		$this->view->matches = count($this->view->marks);
				
		$this->render('posts');
	}
	
	/**
	 * Delete a link
	 */
	public function deletepostAction () {
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (!$this->_getParam('url')) {
			print '<result code="url must be specified."/>';
			return;
		}
		
		try {
			$mark = $this->manager->getMarkByUrl($this->_getParam('url'));
			$this->manager->deleteMark($mark->id);
			print '<result code="done"/>';
			return;
		} catch (Exception $e) {
			print '<result code="'.htmlentities($e->getMessage()).'"/>';
		}
	}
	
	/**
	 * List the users links
	 */
	public function gettagsAction () {
		$this->view->tags = $this->manager->getTags();
		$this->render('apitags', null, true);
	}
	
	/**
	 * Add/update a post
	 * 
	 * @access public
	 * @since 12/4/09
	 */
	public function addpostAction () {
		$this->_helper->viewRenderer->setNoRender();
		
		// Run our values through the Mark-form for cleaning/validation.
		$form    = new Lynx_Form_Mark();
		
		// We are assuming that the client is checking for Cross-Site Request Forgeries.
		$form->removeElement('csrf'); 
		
		$params = array(
				'url' => $this->_getParam('url'),
				'description' => $this->_getParam('description'),
				'description' => $this->_getParam('description'),
				'notes' => $this->_getParam('extended'),
				'tags' => $this->_getParam('tags'),
			);
		if ($form->isValid($params)) {
			$values = $form->getValues();
			try {
				try {
					$mark = $this->manager->createMark($values['url'], $values['description'], $values['notes'], $form->getTags());
					print "\n<result code='done'/>";
				} catch (Exception $e) {
					// If the error was that the mark already exists, update it.
					if ($e->getCode() == 23000) {
						$mark = $this->manager->getMarkByUrl($values['url']);
						$mark->description = $values['description'];
						$mark->notes = $values['notes'];
						$mark->tags = $form->getTags();
						$this->manager->saveMark($mark);
						print "\n<result code='done'/>";
					} else {
						print "\n<result code='Unable to save: ".$e->getMessage()."'/>";
						throw $e;
					}
				}
			} catch (Exception $e) {
				print "\n<result code='Unable to save: ".$e->getMessage()."'/>";
				throw $e;
			}
		} else {
			print "\n<result code=\"Unable to save. ";
			foreach ($form->getMessages() as $name => $message) {
				foreach ($message as $code => $message) {
					print htmlentities("\n\t$name: $message.");
				}
			}
			print "\"/>";
		}
		
	}
}