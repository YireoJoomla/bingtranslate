<?php
/**
 * @package     Yireo Translation Library
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\Translate\Session;

use Mage;

/**
 * @package     Yireo\Translate\Session
 */
class Magento extends \Yireo\Translate\Session
{
	/**
	 * @param string $name
	 * @param string $value
	 */
	public function save($name, $value)
	{
		Mage::getModel('core/session')->setData($name, $value);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get($name)
	{
		return Mage::getModel('core/session')->getData($name);
	}
}