<?php
/**
 * Joomla! component Bing Translate
 *
 * @author    Yireo
 * @copyright Copyright 2017
 * @license   GNU Public License
 * @link      https://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the loader
require_once JPATH_COMPONENT . '/helpers/loader.php';

/**
 * @package com_bingtranslate
 */
class BingTranslateController extends YireoController
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->default_view = 'home';

		parent::__construct();
	}

	/**
	 * Translate task
	 */
	public function translate()
	{
		// Get options from request
		$input    = JFactory::getApplication()->input;
		$text     = $input->get('text', null, 'raw');
		$toLang   = $this->getToLang();
		$fromLang = $input->getCmd('from');

		// Get options from parameters
		$params  = JComponentHelper::getParams('com_bingtranslate');
		$useBork = (bool) $params->get('bork', 0);

		// Parse the language
		$toLang   = preg_replace('/-([a-zA-Z0-9]+)$/', '', $toLang);
		$fromLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $fromLang);

		// Get the API-key
		$params    = JComponentHelper::getParams('com_bingtranslate');
		$clientKey = $params->get('client_key');

		// Sanity checks
		if (empty($clientKey))
		{
			$this->response(JText::_('Windows Azure client key is not configured'), false);
		}

		if (empty($text))
		{
			$this->response(JText::_('No text to translate'), false);
		}

		if (empty($toLang))
		{
			$this->response(JText::_('Failed to detect destination-language'), false);
		}

		/** @var BingTranslateModelTranslator $translator */
		$translator = $this->getModel('translator');
		$translator->setClientKey($clientKey);
		$translator->setUseBork($useBork);

		try {
			$translation = $translator->translate($text, $toLang, $fromLang);
		} catch(Exception $e) {
			return $this->response($e->getMessage(), false);
		}


		if (empty($translation))
		{
			return $this->response(JText::_('No response from Bing'), false);
		}

		return $this->response($translation);
	}

	/**
	 *
	 * @return string
	 */
	protected function getToLang()
	{
		$input  = JFactory::getApplication()->input;
		$toLang = $input->getCmd('to');

		if (!preg_match('/joomfish([0-9\-]+)/', $toLang))
		{
			return $toLang;
		}

		$languageId = preg_replace('/([^0-9]+)/', '', $toLang);

		$languages = $this->getLanguagesFromSystem();

		if (empty($languages))
		{
			return $toLang;
		}

		foreach ($languages as $language)
		{
			if (isset($language->id) && $language->id == $languageId)
			{
				$matchLanguage = $language;
				break;
			}

			if (isset($language->lang_id) && $language->lang_id == $languageId)
			{
				$matchLanguage = $language;
				break;
			}
		}

		if (empty($matchLanguage))
		{
			return $toLang;
		}

		if (!empty($matchLanguage->lang_code))
		{
			return $matchLanguage->lang_code;
		}

		if (!empty($matchLanguage->shortcode))
		{
			return $matchLanguage->shortcode;
		}

		if (!empty($matchLanguage->sef))
		{
			return $matchLanguage->sef;
		}
	}

	/**
	 *
	 * @return array
	 */
	protected function getLanguagesFromSystem()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from($db->quoteName('#__languages'));
		$query->select('*');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/*
	 * Helper method to send a response
	 *
	 * @param string $text
	 * @param bool $success
	 */
	protected function response($text, $success = true)
	{
		$response = array('text' => $text, 'code' => (int) $success);
		print json_encode($response);
		$application = JFactory::getApplication();
		$application->close();
	}
}
