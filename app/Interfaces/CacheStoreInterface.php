<?php

namespace App\Interfaces;

interface CacheStoreInterface {

	/**
	 * Get items cache thing
	 *
	 * @param string|null $order_no
	 * @return array
	 */
	public function getItems($order_no = null);

    /**
	 * Backwards compability Cart ID
	 *
	 * @author Dian Afrial
	 */
    public function getKeyTransactionId($id = null);

    /**
	 * Backwards compability and check existing On DB
	 * If no exists, create one
	 * If exists loop until no exists
	 *
	 * @return string
	 */
    public function generateCacheNo();
    
    /**
	 * Encode array to json string
	 *
	 * @author Dian Afrial
	 * @return void
	 */
    public function encode($data);
    
    /**
	 * Decode json to Array
	 *
	 * @author Dian Afrial
	 * @return void
	 */
    public function decode($data);

    /**
	 * Backwards compability GET method
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	public function get();
    
    /**
	 * Backwards compability ADD method
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
    public function add($value);
    
    /**
	 * Backwards compability PUT method
	 *
	 * @author Dian Afrial
	 * @return object
	 */
    public function put($key, $newValue);
    
    /**
	 * Flush all cache from the merchant ID
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	public function flush($order_no = null);

}