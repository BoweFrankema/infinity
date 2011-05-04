<?php
/**
 * PIE API: stack collection iterator class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage collections
 * @since 1.0
 */

/**
 * Implements an iterator for a stack.
 *
 * This is a fork of the Yii CStackIterator implementation
 *
 * Originally authored by Qiang Xue for the {@link http://www.yiiframework.com/ Yii Framework},
 * Copyright 2008-2010 {@link http://www.yiiframework.com/ Yii Software LLC},
 * and released under the {@link http://www.yiiframework.com/license/ Yii License}
 *
 * @package PIE
 * @subpackage collections
 */
class Pie_Easy_Stack_Iterator implements Iterator
{
	/**
	 * @var array The data to be iterated through
	 */
	private $data;
	
	/**
	 * @var integer Index of the current item
	 */
	private $index;
	
	/**
	 * @var integer Count of the data items
	 */
	private $count;

	/**
	 * @param array $data The data to be iterated through
	 */
	public function __construct( &$data )
	{
		$this->data = &$data;
		$this->index = 0;
		$this->count = count( $this->data );
	}

	/**
	 * Rewinds internal array pointer.
	 */
	public function rewind()
	{
		$this->index = 0;
	}

	/**
	 * Returns the key of the current array item.
	 *
	 * @return integer
	 */
	public function key()
	{
		return $this->index;
	}

	/**
	 * Returns the current array item.
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->data[$this->index];
	}

	/**
	 * Moves the internal pointer to the next array item.
	 */
	public function next()
	{
		$this->index++;
	}

	/**
	 * Returns whether there is an item at current position.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return $this->index < $this->count;
	}
}

?>
