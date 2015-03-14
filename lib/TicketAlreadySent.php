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

use ICanBoogie\Accessor\AccessorTrait;

/**
 * Exception thrown in attempt to request a ticket before the end of the cooldown period.
 */
class TicketAlreadySent extends \Exception
{
	use AccessorTrait;

	private $ticket;

	protected function get_ticket()
	{
		return $this->ticket;
	}

	public function __construct(Ticket $ticket, $message, $code=500, \Exception $previous=null)
	{
		$this->ticket = $ticket;

		parent::__construct($message, $code, $previous);
	}
}
