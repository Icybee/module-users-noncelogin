!function (Brickrouge) {

	Brickrouge.Widget.NonceRequest = new Class({

		Extends: Brickrouge.Form,

		options: {

			useXHR: true
		}
	})

	Brickrouge.register('user-nonce-request', function (element, options) {

		return new Brickrouge.Widget.NonceRequest(element, options)

	})

} (Brickrouge);
