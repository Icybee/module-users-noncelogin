<?php

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\HTTP\Request;

return [

	'api:nonce-login-request' => [

		'pattern' => '/api/nonce-login-request',
		'controller' => __NAMESPACE__ . '\NonceLoginRequestOperation',
		'via' => Request::METHOD_POST

	],

	'api:inline-nonce-login-request' => [

		'pattern' => '/api/nonce-login-request/:email',
		'controller' => __NAMESPACE__ . '\NonceLoginRequestOperation',
		'via' => Request::METHOD_POST

	],

	'api:nonce-login' => [

		'pattern' => '/api/nonce-login/<token:[0-9a-z]{40}>',
		'controller' => __NAMESPACE__ . '\NonceLoginOperation'

	]
];