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
 * @property-read TicketModel $model
 * @property DateTime $expire_at Date at which the ticket expires.
 */
class Ticket extends ActiveRecord
{
	const MODEL_ID = 'users.noncelogin';
	const FRESH_PERIOD = 3600;

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
	 * @return DateTime
	 */
	protected function get_expire_at()
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
	 * @param mixed $datetime
	 */
	protected function set_expire_at($datetime)
	{
		$this->expire_at = $datetime;
	}

	/**
	 * A token is obtained from the model if {@link $token} is empty. {@link $expire_at} is set
	 * to `time() + FRESH_PERIOD` if it is empty.
	 */
	public function save()
	{
		if (!$this->token)
		{
			$this->token = $this->model->generate_token();
		}

		if ($this->get_expire_at()->is_empty)
		{
			$this->set_expire_at('@' . (time() + self::FRESH_PERIOD));
		}

		return parent::save();
	}
}
