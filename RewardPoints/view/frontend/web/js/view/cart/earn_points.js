/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {

        var rewardpoints = window.checkoutConfig.rewardpoints;
        if (rewardpoints.earnpoints.value) {
            var value = rewardpoints.earnpoints.value + ' ' + rewardpoints.earnpoints.unit;
        }

        return Component.extend({
            defaults: {
                template: 'Lof_RewardPoints/cart/earn_points'
            },
            totals: quote.getTotals(),
            isDisplayed: function() {
                return !!this.getValue();
            },
            getValue: function() {
                // Add code to refresh rewards data
                var totals = this.totals();
                if (typeof(quote.rewardpoints) != 'undefined' && quote.rewardpoints.earnpoints.value) {
                    value = quote.rewardpoints.earnpoints.value + ' ' + quote.rewardpoints.earnpoints.unit;
                }
                return value;
            }
        });
    }
);