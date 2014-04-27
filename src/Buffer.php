<?php
/**
 * Buffer package
 *
 * @author     Dejan Samardzija, samardzija.dejan@gmail.com
 * @copyright  Little Genius Studio www.littlegeniusstudio.com All rights reserved
 * @version    1.0
 */

namespace G4\Buffer;

class Buffer
{
    /**
     * Available adapters
     *
     * @var string
     */
    const ADAPTER_REDIS = 'Redis';

    /**
     * Data storage processor
     *
     * @var \G4\Buffer\Adapter\Base
     */
    protected $_adapter = null;

    /**
     * Available adapters
     *
     * @var array
     */
    protected $_availableAdapters = array(
        self::ADAPTER_REDIS,
    );

    /**
     * Default adapter
     *
     * @var string
     */
    protected $_defaultAdapter = self::ADAPTER_REDIS;

    /**
     * Instance cache key
     *
     * @var string
     */
    protected $_cacheKey;

    /**
     * Overflow callback function
     *
     * @var Closure
     */
    protected $_overflowCallback;

    /**
     * Class constructor
     *
     * @param  string  $namespace
     * @param  mixed   $adapter
     * @param  mixed   $size
     * @param  Closure $callback
     * @param  array   $options
     * @return void
     */
    public function __construct($namespace, $adapter = null, $size = null, \Closure $callback = null, $options = null)
    {
        $adapter = in_array($adapter, $this->_availableAdapters)
            ? $adapter
            : $this->_defaultAdapter;

        $class = 'G4\\Buffer\\Adapter\\' . $adapter;

        // client options
        if(!isset($options['parameters'])) {
            $options['parameters'] = null;
        }

        if(!isset($options['options'])) {
            $options['options'] = null;
        }

        $this->_adapter = new $class($options['parameters'], $options['options']);

        $this->_setCacheKey($namespace);
        $this->setSize($size);

        if(null !== $callback && is_callable($callback)) {
            $this->_overflowCallback = $callback;
        }
    }

    /**
     * Get buffer client worker instance
     *
     * @return \G4\Buffer\Adapter\Base
     */
    protected function _getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Set buffer cache key
     *
     * @param  string $namespace
     * @return void
     */
    protected function _setCacheKey($namespace) {
        $this->_cacheKey = $namespace . '_' . substr(md5($namespace), 0, 10);
    }

    /**
     * Set buffer size
     *
     * @param  integer
     * @return null
     */
    public function setSize($size = null) {
        $this->_getAdapter()->setSize($size);
    }

    /**
     * Buffer overflow handler
     *
     * @param  void
     * @return boolean
     */
    protected function _overflowHandler() {
        return $this->_getAdapter()->overflow(
            $this->_cacheKey,
            $this->getUsage() - $this->getSize() + 1,
            $this->getUsage()
        );
    }

    /**
     * Add data to buffer
     *
     * @param  mixed   $data
     * @return boolean
     */
    public function add($data)
    {

        if(empty($data) || !$this->_getAdapter()->isClientConnected()) {
            return false;
        }

        if(!$this->_getAdapter()->add($this->_cacheKey, json_encode($data))) {
            return false;
        }

        // overflow handling
        if($this->getUsage() >= $this->getSize()) {
            if (!empty ($this->_overflowCallback) && is_callable ($this->_overflowCallback)) {
                call_user_func_array ($this->_overflowCallback, array ($this->readAndFlush()));
            } else {
                $this->_overflowHandler();
            }
        }
    }

    /**
     * Read data from buffer
     *
     * @todo: remove json encode from buffer, move it to adapter
     *
     * @return mixed
     */
    public function read()
    {
        $data = $this->_getAdapter()->read($this->_cacheKey);

        if(!empty($data) && is_array($data)) {
            foreach($data as &$row) { // @todo: check what to do with this
                $row = json_decode($row, 1);
            }
        }

        return $data;
    }

    /**
     * Read data from buffer and flush
     *
     * @todo: flush needs to be better
     *
     * @return mixed
     */
    public function readAndFlush()
    {
        $data = $this->read();
        $this->flush();
        return $data;
    }

    /**
     * Returns buffer size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_getAdapter()->getSize();
    }

    /**
     * Return buffer usage
     *
     * @return int
     */
    public function getUsage()
    {
        return $this->_getAdapter()->used($this->_cacheKey);
    }

    /**
     * Flush buffer
     *
     * @todo: check what this returns?????
     *
     * @return array
     */
    public function flush()
    {
        return $this->_getAdapter()->del($this->_cacheKey);
    }

} // end of class
