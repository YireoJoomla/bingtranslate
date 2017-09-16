<?php
/**
 * @package     Yireo Translation Library
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\Translate\Session;

use Yireo\Translate\Session;

/**
 * @package     Session
 */
class Joomla extends Session
{
	/**
	 * @param string $name
	 * @param string $value
	 */
	public function save($name, $value)
	{
		$session = \JFactory::getSession();
		$session->set($name, $value);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name)
	{
		$session = \JFactory::getSession();
		return $session->get($name);
	}
}