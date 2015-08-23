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
use ICanBoogie\Routing\Controller;

class NonceLoginRequestController extends Controller
{
	public function action(Request $request)
	{
		return new NonceLoginRequestForm;
	}
}
