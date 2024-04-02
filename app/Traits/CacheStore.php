<?php

namespace App\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;

trait CacheStore
{
	public $configCreateExpiry;
	public $configPutExpiry;
	public $messageEmptyGet;

	/**
	 * Cache Driver
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	public $cache;


	/**
	 * Procedure Get items to Cache by Order ID
	 *
	 * @author Dian Afrial
	 * @param string $order_no
	 * @return mixed
	 */
	public function getItems($order_no = null)
	{
		$stored = $this->get();

		// return index 0
		if ($order_no && !isset($stored[$order_no])) {
			throw new HttpException(422, 'Invalid! Not found order no or has bee expired!');
		} else if ($order_no && isset($stored[$order_no])) {
			return $stored[$order_no];
		}

		return $stored;
	}

	/**
	 * Backwards compability ADD method
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	public function add($value)
	{
		$expiresAt = now()->addMinutes($this->configCreateExpiry);

		return $this->cache->add($this->getKeyTransactionId(), $value, $expiresAt);
	}

	/**
	 * Backwards compability PUT method
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	public function put($key, $newValue)
	{
		$expiresAt = now()->addMinutes($this->configPutExpiry);

		if (!$this->cache->has($this->getKeyTransactionId())) {
			$data = [];
		} else {
			$data = $this->get();
		}

		$data[$key] = $newValue;

		return $this->cache->put(
			$this->getKeyTransactionId(),
			$this->encode($data),
			$expiresAt
		);
	}

	/**
	 * Backwards compability GET method
	 *
	 * @author Dian Afrial
	 * @return object
	 */
	public function get()
	{
		if (!$this->cache->has($this->getKeyTransactionId())) {
			throw new HttpException(403, $this->messageEmptyGet);
		}

		$data = $this->cache->get($this->getKeyTransactionId());

		return $this->decode($data);
	}

	/**
	 * Flush all cache from the merchant ID
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	public function flush($order_no = null)
	{
		$this->cache->forget($this->getKeyTransactionId());

		return true;
	}

	/**
	 * Encode array to json string
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function encode($data)
	{
		return json_encode($data);
	}

	/**
	 * Decode json to Array
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function decode($data)
	{
		return json_decode($data, true);
	}
}
