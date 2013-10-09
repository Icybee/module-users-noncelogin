<?php

namespace Icybee\Modules\Users\NonceLogin;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		__NAMESPACE__ . '\NonceLoginRequestOperation::process' => $hooks . 'on_nonce_login_request'
	)
);