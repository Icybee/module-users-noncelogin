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

use ICanBoogie\HTTP\Request;

class NonceLoginRequestController extends \ICanBoogie\Routing\Controller
{
	public function __invoke(Request $request)
	{
		return new NonceLoginRequestForm;
	}
}
