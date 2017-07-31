<?php
/*
 * Joomla! component BingTranslate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2017
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * BingTranslate Structure
 */
class HelperAbstract
{
    /**
     * Structural data of this component
     *
     * @return array
     */
    static public function getStructure()
    {
        return array(
            'title' => 'BingTranslate',
            'menu' => array(
                'home' => 'Home',
            ),
            'views' => array(
                'home' => 'Home',
            ),
            'obsolete_files' => array(
            ),
        );
    }
}
