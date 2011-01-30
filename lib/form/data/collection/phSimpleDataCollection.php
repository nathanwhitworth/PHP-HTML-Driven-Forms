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

/**
 * Straight forward data collection for elements that need no special
 * rules 
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 * @subpackage data.collection
 */
class phSimpleDataCollection extends phAbstractFindingCollection
{	
	/**
	 * (non-PHPdoc)
	 * @see lib/form/data/collection/phDataCollection::register()
	 */
	public function register(phFormViewElement $element, phNameInfo $name, phCompositeDataCollection $collection)
	{
		if($name->isArray())
		{
			$this->registerArray($element, $name, $collection);
		}
		else
		{
			$this->registerNormal($element, $name, $collection);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see lib/form/data/collection/phDataCollection::validate()
	 */
	public function validate(phFormViewElement $element, phNameInfo $name, phCompositeDataCollection $collection)
	{
		// make sure this data item doesn't exist already
		$item = $collection->find($name->getName());
		if($item===null)
		{
			return; // no problems, the data doesn't exist
		}
		
		if($name->isArray() && !($item instanceof phArrayFormDataItem))
		{
			throw new phFormException("There is already a data item registered at {$name->getName()} and it is not an array data type so I cannot add to it!");
		}
		else if(!$name->isArray())
		{
			throw new phFormException("Cannot register normal data at {$name->getFullName()}, there is already a data item registered there");
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see lib/form/data/collection/phDataCollection::createIterator()
	 */
	public function createIterator()
	{
		return new ArrayIterator($this->_dataItems);
	}
	
	protected function registerArray(phFormViewElement $element, phNameInfo $name, phCompositeDataCollection $collection)
	{
		/*
		 * if the array has already been registered in another collection
		 * then we need to add to the same data instance hence why we use 
		 * find on the composite
		 */
		$currentDataItem = $collection->find($name->getName());
		
		if($currentDataItem===null)
		{
			$currentDataItem = $this->createArrayDataItem($name->getArrayInfo()->getKeyInfo(0), $name->getName());
			$this->_dataItems[$name->getName()] = $currentDataItem;
		}
			
		$finalItem = $this->getOrCreateArrayDataType($name, $name->getArrayInfo()->getKeys(), 0, $element, $currentDataItem);
		$element->bindDataItem($finalItem);
	}
	
	protected function getOrCreateArrayDataType(phNameInfo $name, $keys, $currentKeyIndex, phFormViewElement $element, phArrayFormDataItem $currentDataItem)
	{
		$currentKey = $keys[$currentKeyIndex]->isAutoKey() ? $currentDataItem->getNextAutoKey() : $keys[$currentKeyIndex]->getKey();
		
		if($currentKeyIndex == (sizeof($keys) - 1))
		{
			// last key
			if(isset($currentDataItem[$currentKey]))
			{
				$finalItem = $currentDataItem[$currentKey];
			}
			else
			{
				$finalItem = $this->createNormalDataItem($currentKey);
				$currentDataItem->registerArrayKey($keys[$currentKeyIndex], $finalItem);
			}
			
			return $finalItem;
		}
		else
		{
			if(isset($currentDataItem[$currentKey]))
			{
				$nextDataItem = $currentDataItem[$currentKey];
				if(!($nextDataItem instanceof phArrayFormDataItem))
				{
					$path = $name->getName();
					for($x=0; $x<=$currentKeyIndex; $x++)
					{
						$path .= "[{$keys[$currentKeyIndex]->getKey()}]";
					}
					throw new phFormException("Unable to register array type data at {$path} is not an array data type");
				}
			}
			else
			{
				$nextDataItem = $this->createArrayDataItem($keys[$currentKeyIndex]);
				$currentDataItem->registerArrayKey($keys[$currentKeyIndex], $nextDataItem);
			}
			
			return $this->getOrCreateArrayDataType($name, $keys, ++$currentKeyIndex, $element, $nextDataItem);
		}
	}
	
	protected function registerNormal(phFormViewElement $element, phNameInfo $name, phCompositeDataCollection $collection)
	{
		$data = $this->createNormalDataItem($name->getName());
		$this->_dataItems[$name->getName()] = $data;
		$element->bindDataItem($data);
	}
	
	/**
	 * @return phData
	 */
	protected function createNormalDataItem($name)
	{
		return new phFormDataItem($name);
	}
	
	/**
	 * @return phArrayFormDataItem
	 */
	protected function createArrayDataItem(phArrayKeyInfo $info, $name = null)
	{
		if($name === null)
		{
			$name = $info->getKey();
		}
		return new phArrayFormDataItem($name);
	}
}