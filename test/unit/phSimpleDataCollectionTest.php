<?php
/*
 * phForms Project: An HTML forms library for PHP
 *          https://github.com/mellowplace/PHP-HTML-Driven-Forms/
 * Copyright (c) 2010, 2011 Rob Graham
 *
 * This file is part of phForms.
 *
 * phForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * phForms is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with phForms.  If not, see
 * <http://www.gnu.org/licenses/>.
 */

require_once 'phTestCase.php';
require_once realpath(dirname(__FILE__)) . '/../../lib/form/phLoader.php';
phLoader::registerAutoloader();

/**
 * Tests the phSimpleDataCollection class
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 * @subpackage test
 */
class phSimpleDataCollectionTest extends phTestCase
{
	public function testRegisterNormalType()
	{
		$collection = new phSimpleDataCollection();
		
		$element = new phSimpleTestElement();
		$nameInfo = new phNameInfo('test');
		$collection->register($element, $nameInfo, new phCompositeDataCollection());
		
		// test the data was bound to the element
		$dataItem = $element->boundDataItem;
		
		$element2 = new phSimpleTestElement();
		$nameInfo = new phNameInfo('test2');
		$collection->register($element, $nameInfo, new phCompositeDataCollection());
		
		// test the data was bound to the element
		$dataItem2 = $element->boundDataItem;
		
		$this->assertTrue($dataItem instanceof phFormDataItem, 'A data item was bound to the element');
		$this->assertEquals('test', $dataItem->getName(), 'data items name is set properly');
		
		// test the data is findable and was the same item that was bound to 'test'
		$this->assertSame($collection->find('test'), $dataItem, 'Data was registered at \'test\' and is the same instance as the data item bound to the element');
		
		$this->assertTrue($dataItem2 instanceof phFormDataItem, 'A data item was bound to element2');
		
		// test the data is findable and was the same item that was bound to 'test'
		$this->assertSame($collection->find('test2'), $dataItem2, 'Data was registered at \'test2\' and is the same instance as the data item bound to element2');
	}
	
	public function testRegisterArrayType()
	{
		$collection = new phSimpleDataCollection();
		
		$element = new phSimpleTestElement();
		
		$nameInfo = new phNameInfo('test[name]');
		$collection->register($element, $nameInfo, new phCompositeDataCollection());
		
		$dataItem = $element->boundDataItem;
		$this->assertTrue($dataItem instanceof phFormDataItem, 'A data item was bound to the element');
		$this->assertEquals('name', $dataItem->getName(), 'data items name is set properly');
		$this->assertNotSame($dataItem, $collection->find('test'), 'Bound data item is different to array item at \'test\'');
		$this->assertEquals('test', $collection->find('test')->getName(), 'array items name is set properly');
		$this->assertTrue($collection->find('test') instanceof phArrayFormDataItem, 'test is a phArrayFormDataItem');		
	}
	
	public function testRegisterMultiDimArrayType()
	{
		$collection = new phSimpleDataCollection();
		
		$element = new phSimpleTestElement();
		
		$nameInfo = new phNameInfo('test[address][city]');
		$collection->register($element, $nameInfo, new phCompositeDataCollection());
		
		$dataItem = $element->boundDataItem;
		$this->assertTrue($dataItem instanceof phFormDataItem, 'A data item was bound to the element');
		
		$arrayDataItem = $collection->find('test');
		/*
		 * assert the recursion has happened correctly
		 */
		$this->assertTrue($arrayDataItem instanceof phArrayFormDataItem, 'test is an phArrayFormDataItem');
		$this->assertEquals('test', $arrayDataItem->getName(), 'name was set properly for test');
		$this->assertTrue($arrayDataItem['address'] instanceof phArrayFormDataItem, 'test[address] is an phArrayFormDataItem');
		$this->assertEquals('address', $arrayDataItem['address']->getName(), 'name was set properly for address');
		$this->assertSame($dataItem, $arrayDataItem['address']['city'], 'bound data item is the same object that is registered at test[address][city]');
		$this->assertEquals('city', $arrayDataItem['address']['city']->getName(), 'name was set properly for city');
	}
	
	/**
	 * @expectedException phFormException
	 */
	public function testRegisterSameNormalTypeTwice()
	{
		$collection = new phCompositeDataCollection();
		
		$element = new phSimpleTestElement();
		
		$nameInfo = new phNameInfo('test');
		$collection->register($element, $nameInfo);
		
		$element = new phSimpleTestElement();
		$collection->register($element, $nameInfo);
	}
	
	/**
	 * test that you can't register an array type then a normal
	 * type with the same name
	 * 
	 * @expectedException phFormException
	 */
	public function testRegisterArrayTypeThenNormalType()
	{
		$collection = new phCompositeDataCollection();
		$element = new phSimpleTestElement();
		
		$nameInfo = new phNameInfo('test');
		$collection->register($element, $nameInfo);
		
		$nameInfo = new phNameInfo('test[]');
		$collection->register($element, $nameInfo);
	}
	
	/**
	 * test that you can't register an array type then a normal
	 * type with the same name further down the array chain
	 * i.e test[address] then test[address][city]
	 * 
	 * @expectedException phFormException
	 */
	public function testRegisterArrayTypeThenNormalTypeAtDeeperLevel()
	{
		$collection = new phCompositeDataCollection();
		$element = new phSimpleTestElement();
		
		$nameInfo = new phNameInfo('test[address]');
		$collection->register($element, $nameInfo);
		
		$nameInfo = new phNameInfo('test[address][city]');
		$collection->register($element, $nameInfo);
	}
	
	/**
	 * Test when a non array name is registered in another collection an exception is thrown
	 * 
	 * @expectedException phFormException
	 */
	public function testValidate()
	{
		$collection = new phSimpleDataCollection();
		$composite = new phSimpleTestCompositeDataCollection();
		$composite->_collections[] = $collection;
		
		$element = new phSimpleTestElement();
		$nameInfo = new phNameInfo('test');
		$collection->register($element, $nameInfo, $composite);
		
		$collection2 = new phSimpleDataCollection();
		$collection2->validate($element, $nameInfo, $composite);
	}
	
	public function testValidateArrayOk()
	{
		$collection = new phSimpleDataCollection();
		$composite = new phSimpleTestCompositeDataCollection();
		$composite->_collections[] = $collection;
		
		$element = new phSimpleTestElement();
		$nameInfo = new phNameInfo('test[]');
		$collection->register($element, $nameInfo, $composite);
		/*
		 * check name set properly
		 */
		$this->assertEquals('test', $collection->find('test')->getName(), 'name of array data typr set properly');
		$collection2 = new phSimpleDataCollection();
		$collection2->validate($element, $nameInfo, $composite);
		
		$this->assertTrue(true, 'validate passed an array, recognising it is different');
	}
}

class phSimpleTestElement implements phFormViewElement
{
	public $boundDataItem = null;
	
	/**
	 * (non-PHPdoc)
	 * @see lib/form/phFormViewElement::needsUniqueName()
	 */
	public function createDataCollection()
	{
		return new phSimpleDataCollection();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see lib/form/phFormViewElement::bindDataItem()
	 */
	public function bindDataItem(phFormDataItem $item)
	{
		$this->boundDataItem = $item;
	}
}

class phSimpleTestCompositeDataCollection extends phCompositeDataCollection
{
	public $_collections = array();
}