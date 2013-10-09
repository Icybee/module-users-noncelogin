<?php

namespace Icybee\Modules\Users\NonceLogin;

return array
(
	'api:nonce-login-request' => array
	(
		'pattern' => '/api/nonce-login-request',
		'controller' => __NAMESPACE__ . '\NonceLoginRequestOperation'
	),

	'api:inline-nonce-login-request' => array
	(
		'pattern' => '/api/nonce-login-request/:email',
		'controller' => __NAMESPACE__ . '\NonceLoginRequestOperation'
	),

	'api:nonce-login' => array
	(
		'pattern' => '/api/nonce-login/<token:[0-9a-z]{40}>',
		'controller' => __NAMESPACE__ . '\NonceLoginOperation'
	)
);