<?php 

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Config;

use ArrayAccess;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Contracts\Config\Configure as ConfigureContract;

/**
 * Class Configure
 * 
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Configure implements ArrayAccess, ConfigureContract
{
	/**
	 * Currently registered routes.
	 * 
	 * @var array $vars
	 */
	protected $vars = [];

	/**
	 * @inheritdoc
	 */
	public function has(string $key): bool
	{
		return Arr::has($this->vars, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, $default = null)
	{
		$keys = explode('.', $key);

		if ( ! array_key_exists($file = head($keys), $this->vars)) {
			foreach ([configPath().DIRECTORY_SEPARATOR] as $paths) {
				if (is_readable($path = $paths.$file.'.php')) {
					$this->vars[$file] = require $path;
				}				
			}
		} 
		
		return Arr::get($this->vars, $key, $default);
	}

	/**
	 * @inheritdoc
	 */
	public function set($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];
		
		foreach ($keys as $key => $value) {
			Arr::set($this->vars, $key, $value);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function erase(string $key)
	{
		if (isset($this->$vars[$key])) {
			unset($this->$vars[$key]);
		}
		
		Arr::erase($this->$vars, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		return $this->vars;
	}
	
	/*
	|-----------------------------------------------------------------
	| ArrayAccess Methods
	|-----------------------------------------------------------------
	*/ 
	
	/**
	 * Determine if the given configuration option exists.
	 * 
	 * @param  string  $key
	 * 
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->has($key);
	}
	
	/**
	 * Get a configuration option.
	 * 
	 * @param  string  $key
	 * 
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}
	
	/**
	 * Set a configuration option.
	 * 
	 * @param  string  $key
	 * @param  mixed  $value
	 * 
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}
	
	/**
	 * Unset a configuration option.
	 * 
	 * @param  string  $key
	 * 
	 * @return void
	 */
	public function offsetUnset($key)
	{
		$this->set($key, null);
	}
}