<?php
/**
 * Author: Tatsuki Osawa
 * DateTime: 2015-01-29 10:39
 */

class Underscore implements IteratorAggregate, Countable {

	/**
	 * @param mixed $array_like
	 * @return array
	 */
	static protected function toArray($array_like) {
		if($array_like instanceof self)
			return $array_like->valueOf();
		if($array_like instanceof Traversable)
			return iterator_to_array($array_like);
		return (array) $array_like;
	}

	/**
	 * @param $name
	 * @param $args
	 * @return mixed
	 */
	static public function __callStatic($name, $args) {
		$instance = new self($args[0]);
		return count($args) > 1
			? call_user_func_array(array($instance, $name), array_slice($args, 1))
			: $instance->$name();
	}

	/**
	 * @var array
	 */
	private $_value;

	/**
	 * @param mixed $data
	 */
	public function __construct($data) {
		$this->_value = self::toArray($data);
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function __get($key) {
		return $this->has($key) ? $this->_value[$key] : null;
	}

	/**
	 * @return array
	 */
	public function valueOf() {
		return $this->_value;
	}


	/**
	 * @param callable $callback
	 * @return Underscore
	 */
	public function each(callable $callback) {
		foreach($this->valueOf() as $key => $value) $callback($value, $key);
		return $this;
	}

	/**
	 * @param callable $callback
	 * @return Underscore
	 */
	public function map(callable $callback) {
		$array = array();
		foreach($this->valueOf() as $key => $value) $array[$key] = $callback($value, $key);
		return new self($array);
	}

	/**
	 * @param callable $callback
	 * @param mixed $initial
	 * @return mixed
	 */
	public function reduce(callable $callback, $initial = null) {
		return array_reduce($this->valueOf(), $callback, $initial);
	}

	/**
	 * @param callable $callback
	 * @param mixed $initial
	 * @return mixed
	 */
	public function reduceRight(callable $callback, $initial = null) {
		return array_reduce(array_reverse($this->valueOf()), $callback, $initial);
	}

	/**
	 * @param callable $callback
	 * @return bool
	 */
	public function every(callable $callback) {
		foreach($this->valueOf() as $index => $value) if(!$callback($value)) return false;
		return true;
	}

	/**
	 * @param callable $callback
	 * @return bool
	 */
	public function some(callable $callback) {
		foreach($this->valueOf() as $value) if($callback($value)) return true;
		return false;
	}

	/**
	 * @param callable $callback
	 * @return mixed
	 */
	public function find(callable $callback) {
		foreach($this->valueOf() as $value) if($callback($value)) return $value;
		return null;
	}

	/**
	 * @param callable $callback
	 * @return Underscore
	 */
	public function filter(callable $callback) {
		$result = array();
		foreach($this->valueOf() as $value) if($callback($value)) $result[] = $value;
		return new self($result);
//		PHP5.6以上なら以下のみ
//		return new self(array_filter($this->value(), $callback, ARRAY_FILTER_USE_BOTH));
	}

	/**
	 * @param $properties
	 * @return array
	 */
	public function where($properties) {
		return $this->filter(function($value) use($properties) {
			foreach($properties as $spec_name => $spec_value)
				if(array_key_exists($spec_name, $value) && $spec_value == $value[$spec_name])
					return true;
			return false;
		});
	}

	/**
	 * @param $property_name
	 * @return Underscore
	 */
	public function pluck($property_name) {
		return $this->map(function($value) use($property_name) {
			return isset($value[$property_name]) ? $value[$property_name] : null;
		});
	}

	/**
	 * @return Underscore
	 */
	public function keys() {
		return new self(array_keys($this->valueOf()));
	}

	/**
	 * @return Underscore
	 */
	public function values() {
		return new self(array_values($this->valueOf()));
	}

	/**
	 * @param $defaults
	 * @return Underscore
	 */
	public function defaults($defaults) {
		return new self(array_merge(self::toArray($defaults), $this->valueOf()));
	}

	/**
	 * @param $value
	 * @return bool
	 */
	public function contains($value) {
		return $this->some(function($item) use($value) { return $item === $value; });
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key) {
		return array_key_exists($key, $this->valueOf());
	}

	/**
	 * @param $key
	 * @return Underscore
	 */
	public function pick($key) {
		if(func_num_args() === 1 && is_callable($key)) return $this->filter($key);
		$values = $this->valueOf();
		$keys = new self(func_get_args());
		return new self(
			array_combine($keys->valueOf(), $keys->map(function($key) use($values) {
				return array_key_exists($key, $values) ? $values[$key] : null;
			})->valueOf())
		);
	}

	/**
	 * @param string $joiner
	 * @return string
	 */
	public function join($joiner = ',') {
		return join($joiner, $this->valueOf());
	}

	/**
	 * @return Underscore
	 */
	public function invert() {
		return new self(array_flip($this->valueOf()));
	}

	/**
	 * @return Underscore
	 */
	public function reverse() {
		return new self(array_reverse($this->valueOf()));
	}

	/**
	 * @param $value
	 * @return int|string
	 */
	public function indexOf($value) {
		foreach($this->valueOf() as $index => $v) if($value === $v) return $index;
		return -1;
	}

	/**
	 * @param $value
	 * @return int|string
	 */
	public function lastIndexOf($value) {
		return (new self(array_reverse($this->valueOf(), true)))->indexOf($value);
	}


	/**
	 * @param $value
	 * @return Underscore
	 */
	public function concat($value) {
		return new self(array_merge($this->valueOf(), $value));
	}

	/**
	 * @param $index
	 * @return Underscore
	 */
	public function groupBy($index) {
		$result = [];
		if(is_callable($index)) {
			foreach($this->valueOf() as $key => $value) $result[$index($value, $key)][] = $value;
		} else {
			foreach($this->valueOf() as $value) $result[$value[$index]][] = $value;
		}
		return new self($result);
	}

	/**
	 * @param $sources
	 * @return self
	 */
	public function extend($sources) {
		$destination = $this->valueOf();
		foreach($sources as $key => $value) $destination[$key] = $value;
		return new self($destination);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->join('');
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->valueOf());
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Count elements of an object
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 */
	public function count()
	{
		return count($this->valueOf());
	}

	/**
	 * @param callable $function
	 * @return callable
	 */
	static public function partial(callable $function) {
		$arguments = array_slice(func_get_args(), 1);
		return function() use($function, $arguments) {
			return call_user_func_array($function, array_merge($arguments, func_get_args()));
		};
	}


	/**
	 * @param array|object $object
	 * @return callable
	 */
	static public function property_of($object) {
		if(is_object($object))
			return function($key) use($object){ return $object->$key; };
		if(is_array($object))
			return function($key) use($object){ return isset($object[$key]) ? $object[$key] : null; };
		return function($key){ return null; };
	}

	/**
	 * @param $key
	 * @return callable
	 */
	static public function property($key) {
		return function($object) use($key) {
			if(is_object($object) && property_exists($object, $key))
				return $object->{$key};
			if(is_array($object) && isset($object[$key]))
				return $object[$key];
			return null;
		};
	}

}
