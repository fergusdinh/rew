/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 define(
    [
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, priceUtils) {
        return Component.extend({
            defaults: {
                //template: 'Lof_RewardPoints/cart/use_points'
            },
            totals: quote.getTotals(),
            isDisplayed: function() {
                return !!this.getValue();
            },
            getSpendPoints: function(){
                var rewardpoints = window.checkoutConfig.rewardpoints;
                if (rewardpoints.spendpoints.value) {
                    var value = rewardpoints.spendpoints.value + ' ' + rewardpoints.spendpoints.unit;
                }
                // Add code to refresh rewards data
                var totals = this.totals();
                if (typeof(quote.rewardpoints) != 'undefined' && quote.rewardpoints.spendpoints.value) {
                    value = quote.rewardpoints.spendpoints.value + ' ' + quote.rewardpoints.spendpoints.unit;
                }
                return value;
            },
            getValue: function() {
                var rewardpoints = window.checkoutConfig.rewardpoints;
                if (rewardpoints.discount.value) {
                    var value = "-" + priceUtils.formatPrice(rewardpoints.discount.value, quote.getPriceFormat());
                }

                // Add code to refresh rewards data
                var totals = this.totals();
                if (typeof(quote.rewardpoints) != 'undefined' && quote.rewardpoints.discount.value) {
                    if (quote.rewardpoints.discount.value) {
                        value = "-" + priceUtils.formatPrice(quote.rewardpoints.discount.value, quote.getPriceFormat());
                    } else {
                        value = 0;
                    }
                }
                return value;
            }
        });
    });