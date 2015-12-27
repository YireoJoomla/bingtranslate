<?php
/**
 * Joomla! component Bing Translate
 *
 * @author    Yireo
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the loader
require_once JPATH_COMPONENT . '/lib/loader.php';

class BingTranslateController extends YireoController
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_default_view = 'home';

		parent::__construct();
	}

	/**
	 * Translate task
	 */
	public function translate()
	{
		// Get the language from request
		$input = JFactory::getApplication()->input;
		$text = $input->get('text', null, 'raw');
		$toLang = $input->getCmd('to');
		$fromLang = $input->getCmd('from');

		$params = JComponentHelper::getParams('com_bingtranslate');

		if ($params->get('bork', 0) == 1)
		{
			$newText = $this->bork($text);

			if (empty($newText))
			{
				$this->response('Bork failed', false);
			}

			$this->response($newText);
		}

		// Detect JoomFish languages
		if (preg_match('/joomfish([0-9\-]+)/', $toLang))
		{
			$languageId = preg_replace('/([^0-9]+)/', '', $toLang);
			$toLang = null;

			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM #__languages');

			$languages = $db->loadObjectList();

			if (!empty($languages))
			{
				foreach ($languages as $language)
				{
					if (isset($language->id) && $language->id == $languageId)
					{
						$matchLanguage = $language;
						break;
					}
					else
					{
						if (isset($language->lang_id) && $language->lang_id == $languageId)
						{
							$matchLanguage = $language;
							break;
						}
					}
				}

				if (!empty($matchLanguage))
				{
					if (!empty($matchLanguage->lang_code))
					{
						$toLang = $matchLanguage->lang_code;
					}
					else
					{
						if (!empty($matchLanguage->shortcode))
						{
							$toLang = $matchLanguage->shortcode;
						}
						else
						{
							if (!empty($matchLanguage->sef))
							{
								$toLang = $matchLanguage->sef;
							}
						}
					}
				}
			}
		}

		// Parse the language
		$toLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $toLang);
		$fromLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $fromLang);

		// Get the API-key
		$params = JComponentHelper::getParams('com_bingtranslate');
		$client_id = $params->get('client_id');
		$client_secret = $params->get('client_secret');

		// Sanity checks
		if (empty($client_id))
		{
			$this->response(JText::_('Windows Azure client-ID is not configured'), false);
		}

		if (empty($client_secret))
		{
			$this->response(JText::_('Windows Azure client-secret is not configured'), false);
		}

		if (empty($text))
		{
			$this->response(JText::_('No text to translate'), false);
		}

		// Detect the language
		if (empty($fromLang))
		{
			$result = $this->getCurlDetect($text);

			if (empty($result))
			{
				$this->response(JText::_('No response from Bing'), false);
			}

			if (!preg_match('/^</', $result))
			{
				$this->response(JText::_('Not an XML-result'), false);
			}

			// Parse the XML-code
			$xml = new SimpleXMLElement($result);

			if (is_object($xml))
			{
				$string = (string) $xml;

				if (!empty($string))
				{
					$fromLang = $string;
				}
			}
		}

		// Detect the language
		if (empty($fromLang))
		{
			$fromLang = JFactory::getLanguage()
				->getTag();
		}

		// Error-handling
		if (empty($fromLang))
		{
			$this->response(JText::_('Failed to detect source-language'), false);
		}

		if (empty($toLang))
		{
			$this->response(JText::_('Failed to detect destination-language'), false);
		}

		$result = $this->getCurlTranslate($text, $toLang, $fromLang);

		if (empty($result))
		{
			$this->response(JText::_('No response from Bing'), false);
		}

		if (!preg_match('/^</', $result))
		{
			$this->response(JText::_('Not an XML-result'), false);
		}

		$xml = new SimpleXMLElement($result);

		if (is_object($xml))
		{
			$string = (string) $xml;

			if (!empty($string))
			{
				$this->response($string);
			}
		}

		$this->response(JText::_('Unknown BingTranslate error: ', $result), false);
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

	/**
	 * Method to get the access token
	 *
	 * @return string
	 */
	protected function getAccessToken()
	{
		// If client_id and client_secret empty, return nothing
		$params = JComponentHelper::getParams('com_bingtranslate');
		$client_id = $params->get('client_id');
		$client_secret = $params->get('client_secret');

		if (empty($client_id) || empty($client_secret))
		{
			return null;
		}

		// Windows Azure OAuth URL
		$url = 'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/';

		// Bing API fields
		$fields = array(
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'scope' => 'http://api.microsofttranslator.com/',
			'grant_type' => 'client_credentials',);

		// Make the CURL-call
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		$result = curl_exec($ch);

		// Empty response
		if (empty($result))
		{
			$this->response('Empty response', false);
		}

		// Parse the JSON-data
		$data = json_decode($result);

		if (is_object($data) == false)
		{
			$this->response('No JSON-object: ' . $result, false);
		}

		// Check for errors
		if (isset($data->error))
		{
			$error = 'Error while requesting OAuth token: ' . $data->error;

			if ($data->error_description)
			{
				$error .= ' [' . $data->error_description . ']';
			}

			return $this->response($error, false);
		}

		return $data->access_token;
	}

	/*
	 * Helper method to get a CURL-response
	 *
	 * @access protected
	 * @param string $text
	 * @return string
	 */
	protected function getCurlDetect($text)
	{
		$fields = array(
			'text' => $text,);

		return $this->getCurlResponse('Detect', $fields);
	}

	/*
	 * Helper method to get a CURL-response
	 *
	 * @param string $text
	 * @param string $toLang
	 * @param string $fromLang
	 * @return string
	 * @link http://msdn.microsoft.com/en-us/library/ff512406.aspx
	 */
	protected function getCurlTranslate($text, $toLang, $fromLang)
	{
		$fields = array(
			'text' => $text,
			'to' => $toLang,
			'from' => $fromLang,
			'contentType' => 'text/html',);

		return $this->getCurlResponse('Translate', $fields);
	}

	/*
	 * Helper method to get a CURL-response
	 *
	 * @param string $task
	 * @param array $fields
	 * @return string
	 */
	protected function getCurlResponse($task, $fields)
	{
		$url = 'http://api.microsofttranslator.com/v2/Http.svc/' . $task;
		$url .= '?' . http_build_query($fields);

		// Add extra Authorization-header
		$accessToken = $this->getAccessToken();

		if (!empty($accessToken))
		{
			$headers[] = 'Authorization: Bearer ' . $accessToken;
		}

		// Make the CURL-call
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (!empty($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$result = curl_exec($ch);

		return $result;
	}

	/*
	 * Helper method to check for Joomla! 1.5
	 *
	 * @access protected
	 * @param null
	 * @return bool
	 */
	protected function isJoomla15()
	{
		JLoader::import('joomla.version');
		$version = new JVersion();

		if (version_compare($version->RELEASE, '1.5', 'eq'))
		{
			return true;
		}

		return false;
	}


	/**
	 * Method to borkify a given text
	 *
	 * @param $text
	 *
	 * @return mixed|string
	 */
	public function bork($text)
	{
		$textBlocks = preg_split('/(%[^ ]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$newTextBlocks = array();

		foreach ($textBlocks as $text)
		{
			if (strlen($text) && $text[0] == '%')
			{
				$newTextBlocks[] = (string) $text;
				continue;
			}

			$originalText = $text;
			$searchMap = array(
				'/au/',
				'/\Bu/',
				'/\Btion/',
				'/an/',
				'/a\B/',
				'/en\b/',
				'/\Bew/',
				'/\Bf/',
				'/\Bir/',
				'/\Bi/',
				'/\bo/',
				'/ow/',
				'/ph/',
				'/th\b/',
				'/\bU/',
				'/y\b/',
				'/v/',
				'/w/',
				'/oo/',
				'/oe/');
			$replaceMap = array(
				'oo',
				'oo',
				'shun',
				'un',
				'e',
				'ee',
				'oo',
				'ff',
				'ur',
				'ee',
				'oo',
				'oo',
				'f',
				't',
				'Oo',
				'ai',
				'f',
				'v',
				'ø',
				'œ',);

			$text = preg_replace($searchMap, $replaceMap, $text);

			if ($originalText == $text && count($newTextBlocks))
			{
				$text .= '-a';
			}

			if (empty($text))
			{
				$text = $originalText;
			}

			$newTextBlocks[] = (string) $text;
		}

		$text = implode('', $newTextBlocks);
		$text = preg_replace('/([:.?!])(.*)/', '\\2\\1', $text);

		//$text .= '['.$this->getData('toLang').']';

		return $text;
	}
}
