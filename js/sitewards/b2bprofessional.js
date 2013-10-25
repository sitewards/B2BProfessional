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
         * URL of loading image
         */
        _sLoadingImage: null,

        /**
         * initializes observer for sku
         *
         * @param oLine
         */
        initialize: function (oLine) {
            if (typeof oLine == 'undefined') {
                return;
            }
            this._oLine = oLine;
            this.getElement('input.sku').observe(
                'change',
                this._onChangeSku.bind(this)
            );
            this.getElement('.qty').style.display = 'none';
            this._sLoadingImage = $$('.sitewards-b2bprofessional-order-form .loading').first().src;

            var that = this;
            this.getElement('.remove')
                .observe('click', function (oEvent) {
                    that._onRemove();
                    oEvent.preventDefault();
                    return false;
                })
                .hide()
            ;
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
            this.getElement('.name').update('<img src="'+this._sLoadingImage+'">');
            this.getElement('.qty').style.display = 'none';
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
            this._clearMessages();
            var oResponse = transport.responseText.evalJSON(true);
            if (oResponse.result == 0) {
                var oQty = this.getElement('input.qty');

                oQty.value = Math.max(1, oResponse.qty);
                oQty.disabled = false;
                this.getElement('.name').update(oResponse.name);
                this.getElement('.price').update(oResponse.price);
                if (this._hasEmptyLineInForm() == false) {
                    this._duplicateLine();
                }
                this.getElement('.qty').style.display = 'block';
                oQty.focus();
                oQty.select();

                if (this.getElement('input.sku').value.length > 0) {
                    this.getElement('.remove').show();
                }
            } else {
                this._reset();
                this.getElement('input.sku').value = '';
                this._showMessage(oResponse.error);
                this.getElement('input.sku').focus();
            }
        },

        /**
         * Show standard magento message
         *
         * @param sText
         * @param sType
         * @private
         */
        _showMessage : function (sText) {
            $$('.messages')[0].style.display = 'block';
            $$('.messages>li>ul>li')[0].update(sText);
        },

        /**
         * Remove all messages
         *
         * @private
         */
        _clearMessages : function () {
            $$('.messages>li>ul>li')[0].update('');
            $$('.messages')[0].style.display = 'none';
        },

        /**
         * Determines if there is an empty line in the form
         *
         * @return {Boolean} true if there is an empty line
         * @private
         */
        _hasEmptyLineInForm : function () {
            var aLines = this._oLine.up('tbody').select('tr');
            for (var i=0; i < aLines.length; i++) {
                var oInput = $(aLines[i]).select('input').first();
                if (oInput.value == '') {
                    return true;
                }
            }
            return false;
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
         * removes the line from the form
         *
         * @private
         */
        _onRemove: function () {
            var oLinesContainer = this._oLine.up();
            this._oLine.remove();
            // hide "remove line" if only one line is left
            if (oLinesContainer.childElements().length <= 1) {
                oLinesContainer.down('.remove').hide();
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
            this._showMessage(Translator.translate('The product does not exist.'));
            this.getElement('.name').focus();
        }
    }
);

/**
 * Date picker for the order date
 */
var OrderDatePicker = Class.create(
    {
        /**
         * Sets up all events of the input fields and
         * the Calender
         */
        initialize: function () {

            // initialize date picker with correct date format
            Calendar.setup({
                inputField : 'delivery_date',
                ifFormat : '%Y-%m-%d',
                align : 'Bl',
                button: 'delivery_date',
                singleClick : true
            });

            // initialize the input
            var oDateInput = $('delivery_date');
            if (oDateInput.value == '') {
                var oToday = new Date(),
                    sDay = ('0' + oToday.getDate()).slice(-2),
                    sMonth = ('0' + (oToday.getMonth()+1)).slice(-2),
                    sYear = oToday.getFullYear()
                    ;
                $('delivery_date').value =  sYear + '-' + sMonth + '-' + sDay;
            }
            // date can only be changed via date picker
            oDateInput.on('focus', function () {
                oDateInput.blur();
            });

            this._initializeLoca();
        },

        /**
         * initialize the localization of the calendar, if not already initialized
         *
         * @private
         */
        _initializeLoca: function () {

            if (this._isUndefined('enUS', window)) {
                window.enUS = {
                    "m": {
                        "wide": [
                            "January", "February", "March", "April", "May", "June", "July", "August",
                            "September", "October", "November", "December"
                        ],
                        "abbr": ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
                    }
                };
            }

            this._initCalendarLocaField('_DN', new Array ("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"));
            this._initCalendarLocaField('_SDN', new Array("Sun","Mon","Tue","Wed","Thu","Fri","Sat"));
            this._initCalendarLocaField('_FD', 0);
            this._initCalendarLocaField('_MN', window.enUS.m.wide);
            this._initCalendarLocaField('_SMN', window.enUS.m.abbr);
            this._initCalendarLocaField('_TT', {
                INFO: "About",
                ABOUT: "DHTML Date/Time Selector\n" +
                    "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" +
                    "For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
                    "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
                    "\n\n" +
                    "Date selection:\n" +
                    "- Use the \xab, \xbb buttons to select year\n" +
                    "- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
                    "- Hold mouse button on any of the above buttons for faster selection.",
                ABOUT_TIME: "\n\n" +
                    "Time selection:\n" +
                    "- Click on any of the time parts to increase it\n" +
                    "- or Shift-click to decrease it\n" +
                    "- or click and drag for faster selection.",
                PREV_YEAR : "Prev. year (hold for menu)",
                PREV_MONTH: "Prev. month (hold for menu)",
                GO_TODAY: "Go Today",
                NEXT_MONTH : "Next month (hold for menu)",
                NEXT_YEAR: "Next year (hold for menu)",
                SEL_DATE: "Select date",
                DRAG_TO_MOVE: "Drag to move",
                PART_TODAY: "(today)",
                DAY_FIRST: "Display %s first",
                SELECT_COLUMN: "Select all %ss of this month",
                SELECT_ROW: "Select all days of this week",
                WEEKEND: "0,6",
                CLOSE: "Close",
                TODAY: "Today",
                TIME_PART: "(Shift-)Click or drag to change value",
                DEF_DATE_FORMAT: "%Y-%m-%d",
                TT_DATE_FORMAT: "%a, %b %e",
                WK: "wk",
                TIME: "Time:",
                LAM: "am",
                AM: "AM",
                LPM: "pm",
                PM: "PM"
            })
            ;
            this._initCalendarLocaField('_DIR', 'ltr');
            this._initCalendarLocaField('_am', 'am');
            this._initCalendarLocaField('_pm', 'pm');
        },

        /**
         * Inits a localization part of the calendar
         *
         * @param {string} sFieldname name of the part
         * @param {mixed} mValue value of the field (array, string or number)
         * @private
         */
        _initCalendarLocaField: function (sFieldname, mValue) {
            if (this._isUndefined(sFieldname, Calendar)) {
                Calendar[sFieldname] = mValue;
            }
        },

        /**
         * Checks if element is undefined
         *
         * @param {mixed} mElement object or (string) name of property of oParent object
         * @param {object} oParent object to check the property mElement of (not required)
         * @return {Boolean} true if the element is undefined
         * @private
         */
        _isUndefined : function (mElement, oParent) {
            if (typeof oParent == 'undefined') {
                return (typeof  mElement == 'undefined');
            } else {
                return (typeof  oParent[mElement] == 'undefined');
            }
        }
    }
);

