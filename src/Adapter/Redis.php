<?php
/**
 * Buffer Adapter Redis
 */

namespace G4\Buffer\Adapter;

class Redis extends Base
{
    /**
     * Redis client instance placeholder
     *
     * @var \Predis\Client
     */
    protected $_client = null;

    /**
     * Class constructor
     *
     * @param mixed $parameters
     * @param mixed $options
     */
    public function __construct($parameters = null, $options = null)
    {
        if(!($this->_client instanceof \Predis\Client)) {
            $this->_client = new \Predis\Client($parameters, $options);
        }
        $this->_parameters = $parameters;
    }

    public function isClientConnected()
    {
//    	return $this->_client->isConnected();
        $uri = "tcp://{$this->_parameters['host']}:{$this->_parameters['port']}/";
        $flags = STREAM_CLIENT_CONNECT;
         $resource = @stream_socket_client($uri, $errno, $errstr, 5, $flags);
         if($resource) {
            return true;
         }
         else {
             return false;
         }

    }

    /**
     * Returns Client instance
     *
     * @param  void
     * @return \Predis\Client
     */
    protected function _getClient()
    {
        return $this->_client;
    }

    /**
     * Add record to the buffer storage
     *
     * @param  string $key
     * @param  string $value
     * @return bool
     */
    public function add($key, $value)
    {
        return $this->_getClient()->rPush($key, $value);
    }

    /**
     * Overflow handler
     *
     * @param  string  $key
     * @param  integer $start
     * @param  integer $stop
     * @return bool
     */
    public function overflow($key, $start = null, $stop = null)
    {
        return $this->_getClient()->lTrim($key, $start, $stop);
    }

    /**
     * Return used buffer storage size
     *
     * @param  string $key
     * @return integer
     */
    public function used($key)
    {
        return $this->_getClient()->lLen($key);
    }

    /**
     * Read buffer content
     *
     * @param  string $key
     * @return array
     */
    public function read($key)
    {
        return $this->_getClient()->lRange($key, 0, -1);
    }

    /**
     * Delete buffer storage content
     *
     * @param  string $key
     * @return bool
     */
    public function del($key)
    {
        return $this->_getClient()->del($key);
    }

} // end of class
