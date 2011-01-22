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
 * This class handles uploaded files from a form 
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 */
class phFileFormDataItem extends phFormDataItem
{
	/**
	 * (non-PHPdoc)
	 * @see lib/form/phFormDataItem::bind()
	 */
	public function bind($file)
	{
		if(!$this->isValidFilePost($file))
		{
			throw new phFileDataException('bound data is not a valid file', phFileDataException::INVALID_FILE_DATA);
		}
		
		return parent::bind($file);
	}
	
	/**
	 * This saves the uploaded file to $location
	 * 
	 * @param string $location
	 */
	public function saveFile($location)
	{
		$this->checkBound();
		
		if(!file_exists($this->_value['tmp_name']))
		{
			throw new phFileDataException("Temp file \"{$this->_value['tmp_name']}\" does not exist", phFileDataException::TEMP_FILE_NOT_FOUND);
		}
	}
	
	/**
	 * Gets the size of the uploaded file in bytes
	 * 
	 * @return int the filesize in bytes
	 */
	public function getFileSize()
	{
		$this->checkBound();
		
		return $this->_value['size'];
	}
	
	/**
	 * If the uploading of the file failed due to an error this will return true
	 * @return boolean true if an error occurred while uploading the file
	 */
	public function hasError()
	{
		$this->checkBound();
		
		return $this->_value['error']!=0;
	}
	
	/**
	 * @return string the name of the file on the uploaders PC
	 */
	public function getOriginalFileName()
	{
		$this->checkBound();
		
		return $this->_value['name'];
	}
	
	/**
	 * @return string the temporary path and filename of the uploaded file
	 */
	public function getTempFileName()
	{
		$this->checkBound();
		
		return $this->_value['tmp_name'];
	}
	
	/**
	 * @return string the mime type of the uploaded file
	 */
	public function getMimeType()
	{
		$this->checkBound();
		
		return $this->_value['type'];
	}
	
	/**
	 * Checks if the passed data is proper file data
	 * @param mixed $data
	 * @return boolean true if the data is valid
	 */
	private function isValidFilePost($data)
	{
		if(
			!is_array($data) ||
			!array_key_exists('name', $data) ||
			!array_key_exists('type', $data) ||
			!array_key_exists('size', $data) ||
			!array_key_exists('tmp_name', $data) ||
			!array_key_exists('error', $data)
		)
		{
			return false;
		}
		
		return true;
	}
	
	private function checkBound()
	{
		if($this->_value===null)
		{
			throw new phFileDataException('No data has been bound', phFileDataException::INVALID_FILE_DATA);
		}
	}
}