<?php

namespace Icybee\Modules\Users\NonceLogin;

$hooks = Hooks::class . '::';

return [

	Operation\NonceLoginRequestOperation::class . '::process' => $hooks . 'on_nonce_login_request',
	Operation\NonceLoginOperation::class . '::get_form' => $hooks . 'on_nonce_login_get_form'

];
