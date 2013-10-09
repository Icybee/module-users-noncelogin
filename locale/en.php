<?php

return array
(
	# operation/nonce_login_request

	'nonce_login_request.operation' => array
	(
		'already_sent' => "A message has already been sent to your e-mail address. In order to reduce abuses, you won't be able to request a new one until :time.",

		'title' => 'Request a nonce login',
		'message' => array
		(
			'subject' => "Here's a message to help you login",
			'template' => <<<EOT
This message has been sent to help you login.

Using the following URL you'll be able to login instantly and update your password:

:url

This URL can only be used once and is only valid until :until.

If you didn't create an account neither asked for a new password, this message might be the result
of an attack attempt on the website. If you think this is the case, please contact its admin.

The remote address of the request was: :ip.
EOT
		),

		'success' => "A message to help you login has been sent to the email address %email.",

		'unknown_email' => array
		(
			'message' => array
			(
				'title' => 'Account access attempted',
				'template' => <<<EOT
You (or someone else) entered this email address when trying to change the password of an account.

However, this email address is not in our database of registered users and therefore the attempted
password change has failed.

If you are a user and where expecting this email, please try again using the email address you gave
when opening your account.

If you are not a user, please ignore this email.

The remote address of the request was: :ip.
EOT
			)
		)
	)
);