<?php
/**
 * Joomla! component BingTranslate
 *
 * @author Yireo (info@yireo.com)
 * @package BingTranslate
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/**
 * HTML View class 
 *
 * @static
 * @package BingTranslate
 */
class BingTranslateViewHome extends YireoViewHome
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        $icons = array();
        $this->assignRef( 'icons', $icons );

        $urls = array();
        $urls['twitter'] ='http://twitter.com/yireo';
        $urls['facebook'] ='http://www.facebook.com/yireo';
        $urls['tutorials'] = 'http://www.yireo.com/tutorials/joomla/joomla-extension-tutorials';
        $urls['jed'] = 'http://extensions.joomla.org/extensions/social-web/social-share/social-auto-publish/16753';
        $this->assignRef( 'urls', $urls );

        parent::display($tpl);
    }
}
