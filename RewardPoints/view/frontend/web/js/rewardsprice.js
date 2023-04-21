define([
	'jquery',
	'Magento_Catalog/js/price-utils',
	'underscore',
	'mage/template',
	'jquery/ui'
	], function ($, utils, _, mageTemplate) {
		'use strict';

		var globalOptions = {
			productId: null,
			priceConfig: null,
			prices: {},
			priceTemplate: '<span class="price"><%- data.formatted %></span>',
			oldPriceTemplate: '<span class="price"><%- data.formatted %></span>',
			formatPoints: '',
			discount: 0
		};

		$.widget('lof.rewardsprice', {
			version: "1.0.0",
			options: globalOptions,
			cache: {},

        /**
         * Widget initialisation.
         * Every time when option changed prices also can be changed. So
         * changed options.prices -> changed cached prices -> recalculation -> redraw price box
         */
         _init: function initPriceBox() {
         	var box = this.element;
         	box.trigger('updatePrice');
         	this.cache.displayPrices = utils.deepClone(this.options.prices);
            this.oldprices = utils.deepClone(this.options.prices);
         },

        /**
         * Widget creating.
         */
         _create: function createPriceBox() {
         	var box = this.element;

         	this._setDefaultsFromPriceConfig();
         	this._setDefaultsFromDataSet();

         	box.on('reloadPrice', this.reloadPrice.bind(this));
         	box.on('updatePrice', this.onUpdatePrice.bind(this));
         },

         rewardsUpdatePrice: function updatePrice(newPrices) {
         	_.each(this.cache.displayPrices, function (price, priceCode) {
         		console.log(price);
         	});
         },

        /**
         * Call on event updatePrice. Proxy to updatePrice method.
         * @param {Event} event
         * @param {Object} prices
         */
         onUpdatePrice: function onUpdatePrice(event, prices) {
         	return this.updatePrice(prices);
         },

         reset: function()
         {
            var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                priceTemplate = mageTemplate(this.options.priceTemplate);

            this.options.discount = 0;
            this.options.points = 0;
            _.each(this.oldprices, function (price, priceCode) {
                price.final = _.reduce(price.adjustments, function(memo, amount) {
                    return memo + amount;
                }, price.amount);

                price.formatted = utils.formatPrice(price.final, priceFormat);

                $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({data: price}));
            }, this);
         },

        /**
         * Updates price via new (or additional values).
         * It expects object like this:
         * -----
         *   "option-hash":
         *      "price-code":
         *         "amount": 999.99999,
         *         ...
         * -----
         * Empty option-hash object or empty price-code object treats as zero amount.
         * @param {Object} newPrices
         */
         updatePrice: function updatePrice(newPrices) {
         	var prices = this.cache.displayPrices,
         	additionalPrice = {},
         	pricesCode = [];

         	this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};

         	if (newPrices) {
         		$.extend(this.cache.additionalPriceObject, newPrices);
         	}

         	if (!_.isEmpty(additionalPrice)) {
         		pricesCode = _.keys(additionalPrice);
         	} else if (!_.isEmpty(prices)) {
         		pricesCode = _.keys(prices);
         	}

         	_.each(this.cache.additionalPriceObject, function (additional) {
         		if (additional && !_.isEmpty(additional)) {
         			pricesCode = _.keys(additional);
         		}
         		_.each(pricesCode, function (priceCode) {
         			var priceValue = additional[priceCode] || {};
         			priceValue.amount = +priceValue.amount || 0;
         			priceValue.adjustments = priceValue.adjustments || {};

         			additionalPrice[priceCode] = additionalPrice[priceCode] || {
         				'amount': 0,
         				'adjustments': {}
         			};
         			additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0)
         			+ priceValue.amount;
         			_.each(priceValue.adjustments, function (adValue, adCode) {
         				additionalPrice[priceCode].adjustments[adCode] = 0
         				+ (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
         			});
         		});
         	});

         	if (_.isEmpty(additionalPrice)) {
         		this.cache.displayPrices = utils.deepClone(this.options.prices);
         	} else {
         		_.each(additionalPrice, function (option, priceCode) {
         			var origin = this.options.prices[priceCode] || {},
         			final = prices[priceCode] || {};
         			option.amount = option.amount || 0;
         			origin.amount = origin.amount || 0;
         			origin.adjustments = origin.adjustments || {};
         			final.adjustments = final.adjustments || {};

         			final.amount = 0 + origin.amount + option.amount;
         			_.each(option.adjustments, function (pa, paCode) {
         				final.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
         			});
         		}, this);
         	}

         	this.element.trigger('reloadPrice');
         },

         _setPrice: function(newPrices) {
         	this.options.prices.newPrices = newPrices;
         },

         _setOldPrice: function(oldPrices) {
         	this.options.oldprices = oldPrices;
         },

         updatePoints: function(newPoints) {
         	this._setPoints(newPoints);
         	this.element.trigger('reloadPrice');
         },

         _setPoints: function(newPoints) {
         	this.options.points = newPoints;
         },

         updateDiscount: function(newDiscount) {
         	this._setDiscount(newDiscount);
         	this.element.trigger('reloadPrice');
         },

         _setDiscount: function(newDiscount) {
         	this.options.discount = newDiscount;
         },


		  /**
         * Render price unit block.
         */
        reloadPrice: function() {
         	var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {};
         	var productPriceFinal = 0;

         	var priceTemplate = mageTemplate(this.options.priceTemplate);
         	_.each(this.cache.displayPrices, function (price, priceCode) {
         		var html = '';
         		price.final = _.reduce(price.adjustments, function(memo, amount) {
         			return memo + amount;
         		}, price.amount);
         		price.formatted = utils.formatPrice(price.final, priceFormat);

         		// FINAL PRICE
         		if (priceCode == 'finalPrice' && (this.options.points > 0)) {
         			var priceFinal = price.final - (this.options.discount);
         			priceFinal = utils.formatPrice(priceFinal, priceFormat);
         			productPriceFinal = price.final;
         			html = '<span class="price">' + priceFinal + ' with ' + this.options.points + ' ' + this.options.formatPoints + '</span>';
         		}

         		// OLD PRICE
         		if (priceCode == 'oldPrice' && this.options.points > 0) {
         			html = utils.formatPrice(productPriceFinal, priceFormat);
         		}
         		if(html!='') {
         			$('[data-price-type="' + priceCode + '"]', this.element).html(html);
         		}
         	}, this);
        },

        /**
         * setDefaultsFromDataSet
         */
         _setDefaultsFromDataSet: function _setDefaultsFromDataSet() {
         	var box = this.element,
         	priceHolders = $('[data-price-type]', box),
         	prices = this.options.prices;
         	this.options.productId = box.data('productId');

         	if (_.isEmpty(prices)) {
         		priceHolders.each(function (index, element) {
         			var type = $(element).data('priceType'),
         			amount = parseFloat($(element).data('priceAmount'));
         			if (type && !_.isNaN(amount)) {
         				prices[type] = {
         					amount: amount
         				};
         			}
         		});
         	} else {
         		console.log("empty");
         	}
         },

        /**
         * setDefaultsFromPriceConfig
         */
         _setDefaultsFromPriceConfig: function _setDefaultsFromPriceConfig() {
         	var config = this.options.priceConfig;

         	if (config) {
         		if (+config.productId !== +this.options.productId) {
         			return;
         		}
         		this.options.prices = config.prices;
         	}
         }

     });
return $.lof.rewardsprice;
})
