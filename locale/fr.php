<?php

return [

	# operation/nonce_login_request

	'nonce_login_request.operation' => [

		'already_sent' => "Un message a déjà été envoyé à votre adresse email. Afin de réduire les abus, le prochain pourra être envoyé à partir de :time.",

		'title' => 'Demander une connexion a usage unique',
		'message' => [

			'subject' => "Voici un message pour vous aider à vous connecter",
			'template' => <<<EOT
Ce message a été envoyé pour vous aider à vous connecter.

En utilisant l'URL suivante vous serez en mesure de vous connecter
et de mettre à jour votre mot de passe.

<:url>

Cette URL est a usage unique et n'est valable que jusqu'à :until (UTC :utc_relative).

Si vous n'avez pas créé de profil ni demandé un nouveau mot de passe, ce message peut être le
résultat d'une tentative d'attaque sur le site. Si vous pensez que c'est le cas, merci de contacter
son administrateur.

L'adresse distante était : :ip.
EOT
		],

		'success' => "Un message pour vous aider à vous connecter a été envoyé à l'adresse %email."
	],

	# operation/nonce_login_request

	"Invalid email address: %email." => "Adresse e-mail invalide : %email.",
	"Unknown email address." => "Adresse e-mail inconnue.",

	# operation/nonce_login

	"You are now logged in, please enter your password." => "Vous êtes maintenant connecté, saisissez votre mot de passe."
];
