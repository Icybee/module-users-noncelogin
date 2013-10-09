<?php

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'users',
	Module::T_DESCRIPTION => 'Provides nonce-based password reset process.',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::ACTIVERECORD_CLASS => __NAMESPACE__ . '\Ticket',
			Model::BELONGS_TO => 'users',
			Model::CLASSNAME => __NAMESPACE__ . '\TicketModel',
			Model::SCHEMA => array
			(
				'fields' => array
				(
					'token' => array('char', 40, 'primary' => true),
					'uid' => array('foreign', 'unique' => true),
					'ip' => array('varchar', 40),
					'expire_at' => 'timestamp'
				)
			)
		)

		// TODO-20131007: history ?
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'Password Reset'
);