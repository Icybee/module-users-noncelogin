# Nonce Login [![Build Status](https://travis-ci.org/Icybee/module-users-noncelogin.svg?branch=2.0)](https://travis-ci.org/Icybee/module-users-noncelogin)

Provides nonce login tickets to users who forgot their password, allowing them to reset their
password and login in the mean time.





## Requesting a ticket

Tickets are requested with the [NonceLoginRequestOperation][] operation. The
`api:nonce-login-request` and `api:inline-nonce-login-request` routes can be used to format a URL
for this operation. The operation can only be performed with the `POST` HTTP method.

```php
<?php

use ICanBoogie\HTTP\Request;

$app = ICanBoogie\app();

$request = Request::from([

	'uri' => $app->routes['api:nonce-login-request'],
	'is_post' => true,
	'is_xhr' => true,
	'request_params' => [

		'email' => "olivier.laviale@gmail.com"

	]

]);

# or

$request = Request::from([

	'uri' => $app->routes['api:inline-nonce-login-request']->format(['email' => "olivier.laviale@gmail.com"]),
	'is_post' => true,
	'is_xhr' => true

]);
```

To prevent abuses, a cooldown period is required before another ticket can be requested for the
same user. A [TicketAlreadySent][] exception is thrown in attempt to request a ticket before
the end of that period.

If everything goes well, a message is sent to the user with a link to a [NonceLoginForm][].
The message is sent by an event hook attached to the `Icybee\Modules\Users\NonceLogin\NonceLoginRequestOperation::process`
event, using the `mail()` prototype method, which is usually provided by the [icanboogie/mailer]
package.





### Altering the message or the mailer sending it

If the `ICanBoogie\Core::mail()` method is provided by the [icanboogie/mailer] package,
the `Icybee\Modules\Users\NonceLogin\NonceLoginRequestOperation::mail:before` event can be used to
alter the message or the mailer sending it.





## Exceptions

The following exception are defined:

- [TicketAlreadySent][]: Exception thrown in attempt to request a ticket before the end of
the cooldown period.





----------





## Requirement

The package requires PHP 5.4 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/):

```
$ composer require icybee/module-users-noncelogin
```

This module is part of the modules required by [Icybee](http://icybee.org).





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-users-noncelogin), its repository can be
cloned with the following command line:

	$ git clone https://github.com/Icybee/module-users-noncelogin.git users.noncelogin





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/module-users-noncelogin.svg?branch=2.0)](https://travis-ci.org/Icybee/module-users-noncelogin)






## License

The module is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.





[icanboogie/mailer]: https://github.com/ICanBoogie/Mailer
[NonceLoginForm]: http://icybee.org/docs/class-Icybee.Modules.Users.NonceLogin.NonceLoginForm.html
[NonceLoginRequestOperation]: http://icybee.org/docs/class-Icybee.Modules.Users.NonceLogin.NonceLoginRequestOperation.html
[TicketAlreadySent]: http://icybee.org/docs/class-Icybee.Modules.Users.NonceLogin.TicketAlreadySent.html
