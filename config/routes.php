<?php

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\HTTP\Request;

return [

	'api:nonce-login-request' => [

		'pattern' => '/api/nonce-login-request',
		'controller' => Operation\NonceLoginRequestOperation::class,
		'via' => Request::METHOD_POST

	],

	'api:inline-nonce-login-request' => [

		'pattern' => '/api/nonce-login-request/:email',
		'controller' => Operation\NonceLoginRequestOperation::class,
		'via' => Request::METHOD_POST

	],

	'api:nonce-login' => [

		'pattern' => '/api/nonce-login',
		'controller' => Operation\NonceLoginOperation::class

	],

	'nonce-login-request' => [

		'pattern' => '/nonce-login-request',
		'controller' => NonceLoginRequestController::class

	],

	'nonce-login' => [

		'pattern' => '/nonce-login/<token:[0-9a-z]{40}>',
		'controller' => NonceLoginController::class

	]

];
