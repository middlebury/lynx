<?php

class Default_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    protected $_view;

    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }

    public function getView()
    {
        if (null === $this->_view) {
            $options = $this->getOptions();
            $title   = '';
            if (array_key_exists('title', $options)) {
                $title = $options['title'];
                unset($options['title']);
            }

            $view = new Zend_View($options);
            $view->doctype('XHTML1_STRICT');
            $view->headTitle($title);
            $view->headLink()->appendStylesheet('/css/site.css');
            $view->headScript()->appendFile('/js/analytics.js');

            $viewRenderer =
                Zend_Controller_Action_HelperBroker::getStaticHelper(
                    'ViewRenderer'
                );
            $viewRenderer->setView($view);
            

            $this->_view = $view;
        }
        return $this->_view;
    }
}