<?php

namespace Icybee\Modules\Users\NonceLogin;

$form = new NonceRequestForm();
$form->add_class('form-inline');

echo $form;