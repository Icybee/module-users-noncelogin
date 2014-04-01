<?php

namespace Icybee\Modules\Users\NonceLogin;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Failure::rescue' => $hooks . 'on_operation_failure_rescue',

		__NAMESPACE__ . '\NonceLoginRequestOperation::process' => $hooks . 'on_nonce_login_request'
	)
);