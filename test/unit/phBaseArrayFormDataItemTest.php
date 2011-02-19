<?php
/*
 * Minacl Project: An HTML forms library for PHP
 *          https://github.com/mellowplace/PHP-HTML-Driven-Forms/
 * Copyright (c) 2010, 2011 Rob Graham
 *
 * This file is part of Minacl.
 *
 * Minacl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Minacl is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with Minacl.  If not, see
 * <http://www.gnu.org/licenses/>.
 */

require_once 'phTestCase.php';
require_once dirname(__FILE__) . '/../../lib/form/phLoader.php';
phLoader::registerAutoloader();

/**
 * Some common tests for array data types
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 * @subpackage test
 */
abstract class phBaseArrayFormDataItemTest extends phTestCase
{
	/**
	 * @expectedException phFormException
	 */
	public function testScalarValueBindError()
	{
		$this->createArrayDataItem('test')->bind('test');
	}
	
	
	/**
	 * @expectedException phFormException
	 */
	public function testCannotSetDataDirectly()
	{
		$this->createArrayDataItem('test')->offsetSet(0, 'test');
	}
	
	/**
	 * @expectedException phFormException
	 */
	public function testCannotUnsetDataDirectly()
	{
		$this->createArrayDataItem('test')->offsetUnset(0);
	}
	
	/**
	 * @return phArrayFormDataItem
	 */
	protected abstract function createArrayDataItem($name);
}