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

use Icybee\Modules\Views\View;

class Module extends \ICanBoogie\Module
{
	const FRESH_PERIOD = 3600;
	const COOLOFF_DELAY = 900;

	protected function lazy_get_views()
	{
		return array
		(
			'nonce_login_request' => array
			(
				View::TITLE => 'Nonce login request',
				View::RENDERS => View::RENDERS_OTHER
			)
		);
	}
}