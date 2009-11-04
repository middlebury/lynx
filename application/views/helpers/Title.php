<?php

class Zend_View_Helper_Title
	extends Zend_View_Helper_HeadTitle
{
	public function title() {
		return $this->toString();
	}
	
	/**
     * Turn helper into string
     *
     * @param  string|null $indent
     * @param  string|null $locale
     * @return string
     */
    public function toString($indent = null, $locale = null)
    {
    	return preg_replace('#\s*<title>(.*)</title>#', '\1', parent::toString(null, $locale));
    }
}
	
	