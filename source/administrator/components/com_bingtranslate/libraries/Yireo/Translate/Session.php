<?php
/**
 * @package     Yireo Translation Library
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\Translate;

/**
 * @package     Yireo\Translate
 */
abstract class Session
{
	/**
	 * @param string $name
	 * @param string $value
	 */
	abstract public function save($name, $value);

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	abstract public function get($name);
}