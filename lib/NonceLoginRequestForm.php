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

use Brickrouge\Group;
use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class NonceLoginRequestForm extends Form
{
	/**
	 * Adds the "widget.css" and "widget.js" assets.
	 *
	 * @param Document $document
	 */
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(DIR . 'public/widget.css');
		$document->js->add(DIR . 'public/widget.js');
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes + [

			Form::ACTIONS => new Button('Send', [ 'type' => 'submit', 'class' => 'btn-primary' ]),

			Form::RENDERER => Form\GroupRenderer::class,

			Form::HIDDENS => [

				Operation::DESTINATION => 'users.noncelogin',
				Operation::NAME => 'nonce-login-request'

			],

			Element::CHILDREN => [

				'email' => new Text([

					Group::LABEL => 'your_email',
					Element::REQUIRED => true,
					Element::VALIDATOR => [ 'Brickrouge\Form::validate_email' ]

				])

			],

			Element::IS => 'user-nonce-request',

			'class' => 'widget-nonce-request',
			'name' => 'users/nonce-request'

		]);
	}
}
