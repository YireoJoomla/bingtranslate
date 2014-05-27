<?php
/**
 * Joomla! component BingTranslate
 *
 * @author Yireo
 * @package BingTranslate
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the base controller
require_once (JPATH_COMPONENT.'/controller.php');
$controller	= new BingTranslateController( );

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

