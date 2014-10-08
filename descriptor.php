<?php

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return [

	Module::T_ID => 'users.noncelogin',
	Module::T_CATEGORY => 'users',
	Module::T_DESCRIPTION => 'Provides nonce-based password reset process.',
	Module::T_MODELS => [

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

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'Password Reset'
];