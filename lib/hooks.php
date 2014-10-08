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

use ICanBoogie\Operation\ProcessEvent;
use ICanBoogie\I18n\Translator\Proxi;

class Hooks
{
	/**
	 * Sends an email to the user requesting a nonce login.
	 *
	 * @param ProcessEvent $event
	 * @param NonceLoginRequestOperation $target
	 */
	static public function on_nonce_login_request(ProcessEvent $event, NonceLoginRequestOperation $target)
	{
		global $core;

		$user = $target->user;
		$ticket = $target->ticket;

		$route = $core->routes['api:nonce-login'];
		$url = $route->format($ticket)->absolute_url;
		$until = $ticket->expire_at->local->format('H:i');

		$t = new Proxi([

			'scope' => \ICanBoogie\normalize($user->constructor, '_') . '.nonce_login_request.operation'

		]);

		$core->mail([

			'to' => $user->email,
			'from' => 'no-reply@' . $_SERVER['HTTP_HOST'],
			'subject' => $t('message.subject'),
			'body' => $t('message.template', [

				':url' => $url,
				':until' => $until,
				':ip' => $event->request->ip

			])

		], [ 'sender' => $target ]);
	}
}