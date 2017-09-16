<?php
/**
 * Joomla! Editor Button Plugin - Bing Translate
 *
 * @author Yireo <info@yireo.com>
 * @copyright Copyright 2017
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bing Translate Editor Button Plugin
 */
class PlgButtonBingTranslate extends JPlugin
{
	/**
	 * Method to display the button
	 *
	 * @param string $name
	 *
	 * @return object
	 */
	public function onDisplay($name)
	{
		// Add the proper JavaScript to this document
		JHtml::_('jquery.framework');
		$document = JFactory::getDocument();
		$document->addScript(JUri::base() . '../media/com_bingtranslate/js/editor-xtd.js');
		$document->addStyleSheet(JUri::base() . '../media/com_bingtranslate/css/editor-xtd.css');

		// Detect the language
		$lang = null;

		// Construct the button
		$button = new JObject;
		$button->set('modal', false);
		$button->set('onclick', 'javascript:doBingTranslate(\'' . $name . '\', \'' . $lang . '\', this);return false;');
		$button->set('class', 'btn');
		$button->set('text', JText::_('Bing Translate'));
		$button->set('name', 'copy');
		$button->set('link', '#');

		return $button;
	}
}
