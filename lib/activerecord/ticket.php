<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\ActiveRecord;
use ICanBoogie\DateTime;

/**
 * Representation of a password request.
 *
 * @property DateTime|mixed $expire_at Date at which the ticket expires.
 */
class Ticket extends ActiveRecord
{
	/**
	 * Ticket identifier.
	 *
	 * @var string
	 */
	public $token;

	/**
	 * User identifier.
	 *
	 * @var int
	 */
	public $uid;

	/**
	 * Initial IP for the nonce login request.
	 *
	 * @var string
	 */
	public $ip;

	/**
	 * Date at which the ticket expires.
	 *
	 * @var DateTime|mixed
	 */
	private $expire_at;

	/**
	 * Returns the expire date.
	 *
	 * @return \ICanBoogie\DateTime
	 */
	protected function volatile_get_expire_at()
	{
		$datetime = $this->expire_at;

		if ($datetime instanceof DateTime)
		{
			return $datetime;
		}

		return $this->expire_at = ($datetime === null) ? DateTime::none() : new DateTime($datetime, 'utc');
	}

	/**
	 * Sets the expire date.
	 *
	 * @param mixed $value
	 */
	protected function volatile_set_expire_at($datetime)
	{
		$this->expire_at = $datetime;
	}

	/**
	 * @param string $model Defaults to `users.noncelogin`.
	 */
	public function __construct($model='users.noncelogin')
	{
		parent::__construct($model);
	}
}