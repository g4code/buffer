<?php
/**
 * Storage Abstract Class
 *
 */
namespace G4\Buffer\Adapter;

abstract class Base
{
    /**
     * Default buffer size
     *
     * @var int
     */
    const DEFAULT_SIZE = 1000;

    /**
     * Current buffer size
     *
     * @var int
     */
    protected $_size = self::DEFAULT_SIZE;

    /**
     * Class construcotr with default signature
     *
     * @param mixed $parameters
     * @param mixed $options
     */
    public function __construct($parameters = null, $options = null)
    {
        throw new \Exception('Must implement this in child class');
    }

    /**
     * Add record to the buffer
     *
     * @abstract
     * @param string $key
     * @param mixed  $value
     */
    abstract public function add($key, $value);

    /**
     * Overflow action
     *
     * @abstract
     * @param string $key
     * @param int    $start
     * @param int    $stop
     */
    abstract public function overflow($key, $start = null, $stop = null);

    /**
     * Buffer usage
     *
     * @abstract
     * @param string $key
     */
    abstract public function used($key);

    /**
     * Read all data
     *
     * @abstract
     * @param string $key
     */
    abstract public function read($key);

    /**
     * Delete all data
     *
     * @abstract
     * @param string $key
     */
    abstract public function del($key);

    /**
     * Set buffer size
     *
     * @param int $size
     */
    public function setSize($size = null) {
        $this->_size = is_int($size)? $size : self::DEFAULT_SIZE;
    }

    /**
     * Getbuffer size
     *
     * @param  void
     * @return interger
     */
    public function getSize() {
        return $this->_size;
    }

} // end of class
