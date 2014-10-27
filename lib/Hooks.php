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

use ICanBoogie\I18n\Translator\Proxi;
use ICanBoogie\Operation\ProcessEvent;
use ICanBoogie\Operation\GetFormEvent;

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
		$user = $target->user;
		$ticket = $target->ticket;
		$app = \ICanBoogie\app();
		$url = $app->routes['nonce-login']->format($ticket)->absolute_url;
		$local_expire_at = $ticket->expire_at->local;
		$until = $local_expire_at->format('H:i');
		$utc_relative = $local_expire_at->format('P');

		$t = new Proxi([

			'scope' => \ICanBoogie\normalize($user->constructor, '_') . '.nonce_login_request.operation'

		]);

		$app->mail([

			'to' => $user->email,
			'from' => 'no-reply@' . $_SERVER['HTTP_HOST'],
			'subject' => $t('message.subject'),
			'body' => $t('message.template', [

				'url' => $url,
				'until' => $until,
				'utc_relative' => $utc_relative,
				'ip' => $event->request->ip

			])

		], [ 'sender' => $target ]);
	}

	/**
	 * Provide the form for the `nonce-login` operation.
	 *
	 * The form is an instance of {@link NonceLoginForm}.
	 *
	 * @param GetFormEvent $event
	 */
	static public function on_nonce_login_get_form(GetFormEvent $event)
	{
		$event->form = new NonceLoginForm;
	}
}
