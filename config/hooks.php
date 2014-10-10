<?php

namespace Icybee\Modules\Users\NonceLogin;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		__NAMESPACE__ . '\NonceLoginRequestOperation::process' => $hooks . 'on_nonce_login_request',
		__NAMESPACE__ . '\NonceLoginOperation::get_form' => $hooks . 'on_nonce_login_get_form'

	]
];