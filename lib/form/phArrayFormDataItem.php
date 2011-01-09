<?php
/**
 * This data item is used to represent any array type data specified in a form
 * e.g. <input type="checkbox" name="ids[]" value="1" />
 * 
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 */
class phArrayFormDataItem extends phFormDataItem implements ArrayAccess, Countable
{	
	protected $_autoKeys = array();
	
	protected $_arrayTemplate = array();
	
	public function bind($value)
	{
		if(!is_array($value))
		{
			throw new phFormException("Trying to bind a value that is not an array to {$this->_name}");
		}
		
		return parent::bind($value);
	}
	
	public function registerArrayKeyString($keyString)
	{
		$keys = $this->extractArrayKeys($keyString);
		
		/*
		 * First check that the user has not tried to mix auto keys (e.g. data[]) with
		 * specified ones (e.g. data[6] or data[name])
		 */
		foreach($keys as $k=>$v)
		{
			if(isset($this->_autoKeys[$k]) && is_numeric($this->_autoKeys[$k]) && $v!=='')
			{
				throw new phFormException("You cannot mix auto keys ([]) with specified keys: at {$this->_name}, level {$k}");
			}
			else if($v==='' && !array_key_exists($k, $this->_autoKeys))
			{
				$this->_autoKeys[$k] = 0;
			}
		}
		
		$builtArray = $this->buildArray($keys);
		
		if(!$this->isArrayKeysUnregistered($builtArray))
		{
			throw new phFormException("The array key {$keyString} has already been registered");
		}
		
		//echo "GBUILT"; print_r($builtArray);
		//echo "STORED"; print_r($this->_arrayTemplate);
		$this->_arrayTemplate = $this->arrayMergeReplaceRecursive($this->_arrayTemplate, $builtArray);
		
	}
	
	protected function isArrayKeysUnregistered($keys, $currentKeys = null, $currentRegistered = null)
	{
		if($currentKeys===null)
		{
			$currentKeys = $keys;
		}
		
		if($currentRegistered===null)
		{
			$currentRegistered = $this->_arrayTemplate;
		}
		
		foreach($currentKeys as $k=>$v)
		{
			if(!array_key_exists($k, $currentRegistered))
			{
				return true;
			}
			
			if(!is_array($v))
			{
				// we are at last element and it exists in the registered array
				return false;
			}
		}
		
		return $this->isArrayKeysUnregistered($keys, $currentKeys[$k], $currentRegistered[$k]);
	}
	
	/**
	 * Recursion alert!
	 * 
	 * This recursive function takes in a key array such as
	 * 
	 * $keys[0] = 'address'
	 * $keys[1] = 'ids'
	 * $keys[2] = 1
	 * 
	 * and will return...
	 * 
	 * $builtData['address']['ids'][1] = 1;
	 * 
	 * @param array $keys single dimensional array of the keys
	 * @param integer $level keeps track of where we are in the $keys array
	 */
	protected function buildArray($keys, $level = 0)
	{
		if(!isset($keys[$level]))
		{
			return 1; // we are at the last key so return 1, if it falls to the code below we will return an array
		}
		
		$key = $keys[$level];
		if($key==='')
		{
			// auto key
			$key = $this->_autoKeys[$level];
			$this->_autoKeys[$level]++;
		}
		
		$builtArray = array();
		$builtArray[$key] = $this->buildArray($keys, $level + 1);
		
		return $builtArray;
	}
	
	protected function extractArrayKeys($keyString)
	{
		$numMatched = preg_match_all('/(\[([a-zA-Z0-9_\x7f-\xff]*?)\])/', $keyString, $matches);
		if(!isset($matches[2]))
		{
			throw new phFormException("Invalid array key string '{$keyString}'");
		}
		
		return $matches[2];
	}
	
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->_arrayTemplate);
	}
	
	public function offsetGet($offset)
	{
		return $this->_arrayTemplate[$offset];
	}
	
	public function offsetSet ($offset, $value)
	{
		throw new phFormException('You cannot set array values on this class, all setting of data should bew done through bind');
	}
	
	public function offsetUnset ($offset)
	{
		throw new phFormException('You cannot unset array values on this class, all setting of data should bew done through bind');
	}
	
	public function count()
	{
		if(!is_array($this->_arrayTemplate))
		{
			return 0;
		}
		
		return sizeof($this->_arrayTemplate);
	}
	
	/**
	 * Rob Graham - pikied from php.net and modified slightly so it preserves numeric keys
	 * 
	 * Merges any number of arrays of any dimensions, the later overwriting
	 * previous keys, unless the key is numeric, in whitch case, duplicated
	 * values will not be added.
	 *
	 * The arrays to be merged are passed as arguments to the function.
	 *
	 * @access private
	 * @return array Resulting array, once all have been merged
	 * @author Drvali <drvali@hotmail.com>
	 * @author Rob Graham <htmlforms@mellowplace.com>
	 */
	private function arrayMergeReplaceRecursive() {
	    // Holds all the arrays passed
	    $params = & func_get_args ();
	   
	    // First array is used as the base, everything else overwrites on it
	    $return = array_shift ( $params );
	   
	    // Merge all arrays on the first array
	    foreach ( $params as $array ) {
	        foreach ( $array as $key => $value ) {
	            // Numeric keyed values are added (unless already there)
	            if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
	                if (is_array ( $value )) {
	                    $return [$key] = $this->arrayMergeReplaceRecursive ( $return [$key], $value );
	                } else {
	                    $return [$key] = $value;
	                }
	               
	            // String keyed values are replaced
	            } else {
	                if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
	                    $return [$key] = $this->arrayMergeReplaceRecursive ( $return [$key], $value );
	                } else {
	                    $return [$key] = $value;
	                }
	            }
	        }
	    }
	   
	    return $return;
	}
}