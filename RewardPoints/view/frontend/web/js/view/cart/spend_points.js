/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 define(
    ['Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        var rewardpoints = window.checkoutConfig.rewardpoints;
        if (rewardpoints.spendpoints.value) {
            var value = rewardpoints.spendpoints.value + ' ' + rewardpoints.spendpoints.unit;
        }
        return Component.extend({
            defaults: {
                template: 'Lof_RewardPoints/cart/spend_points'
            },
            rewardpoints: quote.rewardpoints,
            totals: quote.getTotals(),
            isDisplayed: function() {
                return !!this.getValue();
            },
            getValue: function() {
                // Add code to refresh rewards data
                var totals = this.totals();
                if (typeof(quote.rewardpoints) != 'undefined') {
                    if (quote.rewardpoints.spendpoints.value) {
                        value = quote.rewardpoints.spendpoints.value + ' ' + quote.rewardpoints.spendpoints.unit;
                    } else {
                        value = 0;
                    }
                }
                return value;
            }
        });
    });