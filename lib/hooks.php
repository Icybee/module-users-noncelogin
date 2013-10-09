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
use ICanBoogie\Mailer;

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
		$url = $core->site->url . $route->format($ticket);
		$until = $ticket->expire_at->local->format('H:i');

		$t = new Proxi(array('scope' => \ICanBoogie\normalize($user->constructor, '_') . '.nonce_login_request.operation'));

		$core->mailer(array
		(
			Mailer::T_DESTINATION => $user->email,
			Mailer::T_FROM => $core->site->title . ' <no-reply@icybee.org>', // TODO-20110709: customize to site domain
			Mailer::T_SUBJECT => $t('message.subject'),
			Mailer::T_MESSAGE => $t
			(
				'message.template', array
				(
					':url' => $url,
					':until' => $until,
					':ip' => $event->request->ip
				)
			)
		));
	}
}