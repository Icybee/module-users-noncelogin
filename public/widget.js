!function (Brickrouge) {

	const DEFAULT_OPTIONS = {
		useXHR: true
	}

	class NonceRequest extends Brickrouge.Form
	{
		constructor(element, options)
		{
			super(element, Object.assign({}, DEFAULT_OPTIONS, options))
		}
	}

	Brickrouge.register('user-nonce-request', function (element, options) {

		return new NonceRequest(element, options)

	})

} (Brickrouge);
