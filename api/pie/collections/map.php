<?php
/**
 * PIE API map collection class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package pie
 * @subpackage collections
 * @since 1.0
 */

/**
 * Make a mapped (key => value) collection easy
 *
 * This is a fork of the Yii CMap implementation
 *
 * Originally authored by Qiang Xue for the {@link http://www.yiiframework.com/ Yii Framework},
 * Copyright 2008-2010 {@link http://www.yiiframework.com/ Yii Software LLC},
 * and released under the {@link http://www.yiiframework.com/license/ Yii License}
 *
 * @package pie
 * @subpackage collections
 */
class Pie_Easy_Map implements IteratorAggregate,ArrayAccess,Countable
{
	/**
	 * @var array Internal data storage
	 */
	private $data = array();

	/**
	 * @var boolean Whether this list is read-only
	 */
	private $read_only = false;

	/**
	 * Initializes the list with an array or an iterable object.
	 *
	 * @param array $data The intial data. Default is null, meaning no initialization.
	 * @param boolean $read_only Whether the list is read-only
	 * @throws Exception If data is not null and neither an array nor an iterator.
	 */
	public function __construct( $data = null, $read_only = false )
	{
		if ( $data !== null ) {
			$this->copy_from( $data );
		}

		$this->set_read_only( $read_only );
	}

	/**
	 * Check whether this map is read-only or not. Defaults to false.
	 * 
	 * @return boolean 
	 */
	public function get_read_only()
	{
		return $this->read_only;
	}

	/**
	 * Set read only status
	 *
	 * @param boolean $value
	 */
	protected function set_read_only( $value )
	{
		$this->read_only = $value;
	}

	/**
	 * Returns the number of items in the map.
	 * 
	 * @return integer
	 */
	public function count()
	{
		return count( $this->data );
	}

	/**
	 * Return all map keys as an array
	 *
	 * @return array
	 */
	public function get_keys()
	{
		return array_keys( $this->data );
	}

	/**
	 * Returns the item with the specified key.
	 *
	 * @param mixed $key The key to look up
	 * @return mixed The element at the offset, null if no element is found at the offset
	 */
	public function item_at( $key )
	{
		if( isset( $this->data[$key] ) ) {
			return $this->data[$key];
		} else {
			return null;
		}
	}

	/**
	 * Adds an item into the map.
	 *
	 * If the specified key already exists, the old value will be overwritten.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param boolean $prepend
	 * @throws Exception if the map is read-only
	 */
	public function add( $key, $value, $prepend = false )
	{
		if( !$this->read_only ) {
			if ( $prepend ) {
				if ( !is_null($key) ) {
					$this->data = array_merge( array( $key => $value ), $this->data );
				} else {
					throw new Exception( 'Prepend requires a non-null key' );
				}
			} else {
				if ( $key === null ) {
					$this->data[] = $value;
				} else {
					$this->data[$key] = $value;
				}
			}
		} else {
			throw new Exception( 'The map is read only.' );
		}
	}

	/**
	 * Removes an item from the map by its key.
	 * 
	 * @param mixed $key The key of the item to be removed
	 * @return mixed The removed value, null if no such key exists.
	 * @throws Exception if the map is read-only
	 */
	public function remove( $key )
	{
		if( !$this->read_only ) {
			if( isset( $this->data[$key] ) ) {
				$value = $this->data[$key];
				unset( $this->data[$key] );
				return $value;
			} else {
				// it is possible the value is null, which is not detected by isset
				unset( $this->data[$key] );
				return null;
			}
		} else {
			throw new Exception( 'The map is read only.' );
		}
	}

	/**
	 * Removes all items in the map.
	 */
	public function clear()
	{
		foreach( array_keys( $this->data ) as $key ) {
			$this->remove( $key );
		}
	}

	/**
	 * Check whether the map contains an item with the specified key
	 * 
	 * @param mixed $key
	 * @return boolean 
	 */
	public function contains( $key )
	{
		return isset( $this->data[$key] ) || array_key_exists( $key, $this->data );
	}

	/**
	 * Return the entire list of items in the map
	 *
	 * @param boolean $reverse
	 * @return array
	 */
	public function to_array( $reverse = false )
	{
		if ( $reverse ) {
			return array_reverse( $this->data, true );
		} else {
			return $this->data;
		}
	}

	/**
	 * Copies iterable data into the map.
	 *
	 * Note, existing data in the map will be cleared first.
	 * 
	 * @param mixed the data to be copied from, must be an array or object implementing Traversable
	 * @throws Exception If data is neither an array nor an iterator.
	 */
	public function copy_from($data)
	{
		if( is_array($data) || $data instanceof Traversable ) {

			if( $this->count() > 0 ) {
				$this->clear();
			}

			if ( $data instanceof Pie_Easy_Map ) {
				$data = $data->to_array();
			}

			foreach( $data as $key => $value ) {
				$this->add( $key, $value );
			}

		} elseif ( $data !== null ) {
			throw new Exception( 'Map data must be an array or an object implementing Traversable.' );
		}
	}

	/**
	 * Merges iterable data into the map.
	 *
	 * Existing elements in the map will be overwritten if their keys are the same as those in the source.
	 * If the merge is recursive, the following algorithm is performed:
	 *
	 * - The map data is saved as $a, and the source data is saved as $b
	 * - If $a and $b both have an array indxed at the same string key, the arrays will be merged using this algorithm
	 * -- Any integer-indexed elements in $b will be appended to $a and reindxed accordingly
	 * -- Any string-indexed elements in $b will overwrite elements in $a with the same index
	 *
	 * @param mixed $data The data to be merged with, must be an array or object implementing Traversable
	 * @param boolean $recursive Whether the merging should be recursive.
	 * @throws Exception If data is neither an array nor an iterator.
	 */
	public function merge_with( $data, $recursive = true )
	{
		if( is_array( $data) || $data instanceof Traversable ) {
			if( $data instanceof Pie_Easy_Map ) {
				$data = $data->to_array();
			} if ( $recursive ) {
				if ( $data instanceof Traversable ) {
					$d = array();
					foreach( $data as $key => $value ) {
						$d[$key] = $value;
					}
					$this->data = self::merge_array( $this->data, $d );
				} else {
					$this->data = self::merge_array( $this->data, $data);
				}
			} else {
				foreach( $data as $key => $value ) {
					$this->add( $key, $value );
				}
			}
		} elseif ( $data !== null ) {
			throw new Exception( 'Map data must be an array or an object implementing Traversable.' );
		}
	}

	/**
	 * Merges two arrays into one recursively.
	 * 
	 * @see merge_with
	 * @param array $a Array to be merged to
	 * @param array $b Array to be merged from
	 * @return array The merged array (the original arrays are not changed.)
	 */
	public static function merge_array($a,$b)
	{
		foreach( $b as $k => $v ) {
			if( is_integer( $k ) ) {
				$a[] = $v;
			} elseif ( is_array( $v ) && isset( $a[$k] ) && is_array( $a[$k] ) ) {
				$a[$k] = self::merge_array( $a[$k], $v );
			} else {
				$a[$k] = $v;
			}
		}
		
		return $a;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 *
	 * @param mixed $offset The offset to check on
	 * @return boolean
	 */
	public function offsetExists( $offset )
	{
		return $this->contains( $offset );
	}

	/**
	 * Returns the element at the specified offset.
	 *
	 * @param integer $offset The offset to retrieve element.
	 * @return mixed The element at the offset, null if no element is found at the offset
	 */
	public function offsetGet( $offset )
	{
		return $this->item_at( $offset );
	}

	/**
	 * Sets the element at the specified offset.
	 *
	 * @param integer $offset The offset to set element
	 * @param mixed $item The element value
	 */
	public function offsetSet( $offset, $item )
	{
		$this->add( $offset, $item );
	}

	/**
	 * Unsets the element at the specified offset.
	 *
	 * @param mixed $offset The offset to unset element
	 */
	public function offsetUnset( $offset )
	{
		$this->remove( $offset );
	}

	/**
	 * Returns an iterator for traversing the items in the list.
	 *
	 * @return Pie_Easy_Map_Iterator An iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		return new Pie_Easy_Map_Iterator( $this->data );
	}
}

?>
