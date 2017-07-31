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

/**
 * @package com_bingtranslate
 */
class BingTranslateModelTranslator extends YireoAbstractModel
{
	/**
	 * @var string
	 */
	protected $clientKey = '';

	/**
	 * @var bool
	 */
	protected $useBork = false;

	/**
	 * BingTranslateModelTranslator constructor.
	 */
	public function __construct($config = array())
	{
		JLoader::registerNamespace('Yireo', JPATH_COMPONENT . '/libraries');

		return parent::__construct($config);
	}

	/**
	 * Translate task
	 *
	 * @param string $text
	 * @param string $toLang
	 * @param string $fromLang
	 *
	 * @return string
	 */
	public function translate($text, $toLang, $fromLang)
	{
		if (empty($this->clientKey))
		{
			throw new InvalidArgumentException('Client key is not supplied');
		}

		// Setup the parameters
		$params            = [];
		$params['handler'] = '\\Yireo\\Translate\\Handler\\MicrosoftTranslate';
		$params['key']     = $this->clientKey;
		$params['session'] = true;

		// Bork debugging
		if ($this->useBork)
		{
			$params['handler'] = '\\Yireo\\Translate\\Handler\\Bork';
		}

		$translator = new \Yireo\Translate\Translator();
		$translator->setParams($params);
		$translator->setFromLanguage($fromLang);
		$translator->setToLanguage($toLang);
		$translator->setText($text);
		$translation = $translator->translate();

		if (empty($translation))
		{
			throw new RuntimeException(JText::_('No response from Bing'));
		}

		return $translation;
	}

	/**
	 * @param string $clientKey
	 */
	public function setClientKey($clientKey)
	{
		$this->clientKey = (string) $clientKey;
	}

	/**
	 * @param bool $useBork
	 */
	public function setUseBork($useBork)
	{
		$this->useBork = (bool) $useBork;
	}
}
