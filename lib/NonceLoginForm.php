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

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Group;
use ICanBoogie\Operation;

class NonceLoginForm extends Form
{
	const TICKET = '#nonce-ticket';

	public function __construct(array $attributes=[])
	{
		parent::__construct($attributes + [

			Form::ACTIONS => [

				new Button("Send", [ 'class' => 'btn-primary', 'type' => 'submit' ])

			],

			Form::HIDDENS => [

				Operation::NAME => 'nonce-login',
				Operation::DESTINATION => 'users.noncelogin',

				'token' => null

			],

			Form::RENDERER => 'Group',

			Element::CHILDREN => [

				'email' => new Text([

					Group::LABEL => "Email",
					Element::REQUIRED => true,

					'type' => 'email'

				]),

				'password' => new Text([

					Group::LABEL => "Password",
					Element::REQUIRED => true,

					'type' => 'password'

				]),

				'password-verify' => new Text([

					Group::LABEL => "Password verify",
					Element::REQUIRED => true,

					'type' => 'password'

				])
			],

			'name' => 'users.nonce.login'
		]);
	}

	public function __sleep()
	{
		$rc = parent::__sleep();

		$this->required['token'] = "Token";

		return $rc;
	}

	public function render()
	{
		$ticket = $this[self::TICKET];

		$this->hiddens['token'] = $ticket ? $ticket->token : null;

		return parent::render();
	}

}
