document.observe(
	'dom:loaded',
	/**
	 * initializes first product
	 */
	function() {
		var oProduct = new OrderProduct($$('form#order_form .product')[0]);
	}
);

/**
 * presentation of a product row
 */
var OrderProduct = Class.create(
	{
		/**
		 * prototype element of the .product element
		 */
		_oLine: null,

		/**
		 * initializes observer for sku
		 *
		 * @param oLine
		 */
		initialize: function (oLine) {
			this._oLine = oLine;
			this.getElement('input.sku').observe(
				'change',
				this._onChangeSku.bind(this)
			);
		},

		/**
		 * displays more information about product, if allowed
		 *
		 * @private
		 */
		_onChangeSku: function () {
			new Ajax.Request('/b2bprofessional/product/info', {
				method: 'get',
				parameters: {
					'sku' : this.getElement('input.sku').value
				},
				requestHeaders: {Accept: 'application/json'},
				onSuccess: this._onSuccess.bind(this),
				onFailure: this._onFailure.bind(this)
			});
		},

		/**
		 * returns element of this product element
		 *
		 * @param sSelector
		 * @returns {*}
		 */
		getElement: function (sSelector) {
			return this._oLine.down(sSelector);
		},

		/**
		 * duplicates current line
		 *
		 * @private
		 */
		_duplicateLine: function () {
			var oParent = this._oLine.up();
			oParent.insert(this._oLine.outerHTML);
			var oProduct = new OrderProduct(oParent.childElements().last());
			oProduct._reset();
		},

		/**
		 * displaysinformation about product
		 *
		 * @param transport
		 * @private
		 */
		_onSuccess: function(transport) {
			var oResponse = transport.responseText.evalJSON(true);
			var oQty = this.getElement('input.qty');

			oQty.value = Math.max(1, oResponse.qty);
			oQty.disabled = false;
			this.getElement('.name').update(oResponse.name);
			this.getElement('.price').update(oResponse.price);
			this._duplicateLine();
		},

		/**
		 * resets information about product
		 *
		 * @private
		 */
		_reset: function () {
			this.getElement('input.qty').update('');
			this.getElement('input.qty').disabled = 'disabled';
			this.getElement('.name').update('');
			this.getElement('.price').update('');
		},

		/**
		 * remove last empty row after a failed ajax request, except current row is last row
		 *
		 * @private
		 */
		_removeEmptyRows: function () {
			var oLastLine = this._oLine.up().childElements().last();
			if (oLastLine != this._oLine) {
				oLastLine.remove();
			}
		},

		/**
		 * called on ajax request failure
		 *
		 * @private
		 */
		_onFailure: function () {
			this._reset();
			this._removeEmptyRows();
			alert(Translator.translate('The product does not exist.'));
		}
	}
);
