<?php

use Icybee\Modules\Users\User;

return [

	'Icybee\Modules\Users\User::url' => function(User $user, $type) {

		return "/url/for/$type";

	}

];
