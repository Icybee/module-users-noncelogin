<?php

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module\Descriptor;

return [

	Descriptor::ID => 'users.noncelogin',
	Descriptor::CATEGORY => 'users',
	Descriptor::DESCRIPTION => 'Provides nonce-based password reset process.',
	Descriptor::MODELS => [

		'primary' => [

			Model::ACTIVERECORD_CLASS => __NAMESPACE__ . '\Ticket',
			Model::BELONGS_TO => 'users',
			Model::CLASSNAME => __NAMESPACE__ . '\TicketModel',
			Model::SCHEMA => [

				'fields' => [

					'token' => [ 'char', 40, 'charset' => 'ascii/bin', 'primary' => true ],
					'uid' => [ 'foreign', 'charset' => 'ascii/bin', 'unique' => true ],
					'ip' => [ 'varchar', 'charset' => 'ascii/bin', 40 ],
					'expire_at' => 'timestamp'
				]
			]
		]

		// TODO-20131007: history ?
	],

	Descriptor::NS => __NAMESPACE__,
	Descriptor::TITLE => 'Password Reset'
];
