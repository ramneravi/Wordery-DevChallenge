<?php
namespace Challenge\Throttle\Rule;

/**
 * A leaky bucket is used for rate limiting / throttle for a rolling period
 * @author Mohanram 
 */
class LeakyBucket implements RuleInterface
{
    /**
     * The maximim capacity of the bucket
     * @var integer $_bucketSize
     */
    protected $_bucketSize;

    /**
     * The current volume of the of the bucket (starts empty)
     * @var integer $_volume
     */
    protected $_volume = 0;

    /**
     * The rate at which the bucket empties per second
     * @var float $_drainRate
     */
    protected $_drainRate;

    /**
     * The time the last update was applied to the bucket
     * @var DateTime $_lastRequest
     */
    protected $_lastRequest;


	public function __construct($bucketSize, $drainRate)
    {
        $this->_bucketSize = $bucketSize;
    	$this->_drainRate = $drainRate;  
    }

    public function getTtl()
    {
        return $_volume;
    }

    public function throttled()
    {
        if ($this->hasCapacity())
        {
            $this->fill();
            return false;
        } else
        {
            return true;
        }
    }

    public function log($timestamp)
    {
        $this->_lastRequest = $timestamp;
        return $this; 
    }

    public function getKey()
    {
        return 'leakyBucket';
    }

    public function getState()
    {
        return [$this->_lastRequest, $this->_volume]; 
    }

    public function setState(array $state)
    {
        if (empty($state) || !isset($state[0])) {
            return;
        }
        $this->_lastRequest = $state[0];
        $this->fill($state[1]);
    }
    
    /**
     * Fill the bucket with current volume
     * @param integer $volume
     */
    private function fill($volume = 1)
    {
        if ($volume > 0)
        {
            $this->_volume += $volume;
        }
    }

    /**
     * Update the current bucket volume and check we have capacity
     * @return boolean $response
     */
    private function hasCapacity()
    {
        $diff = microtime(true) - $this->_lastRequest;
        
        if ($diff > 0)
        {
            // reduce the bucket volume by seconds * empty_rate
            $this->_volume -= ($diff * $this->_drainRate);
            $this->_volume = max($this->_volume, 0);
        }
        
        return (ceil($this->_volume) < $this->_bucketSize);
    }

}