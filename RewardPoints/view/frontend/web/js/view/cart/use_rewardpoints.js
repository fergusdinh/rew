/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Lof_RewardPoints/js/rewardsslider'
],
    function ($, ko, Component) {
        'use strict'

        var isLoggedIn          = ko.observable(window.isCustomerLoggedIn);
        var showOnShoppingCart  = window.checkoutConfig.rewardpoints.showonshoppingcart;
        var showOnCheckoutPage  = window.checkoutConfig.rewardpoints.showoncheckoutpage;
        var spendingRules       = window.checkoutConfig.rewardpoints.rules;
        var currentRule         = window.checkoutConfig.rewardpoints.currentrule;
        var ajaxApplyUrl        = window.checkoutConfig.rewardpoints.ajaxurl;
        var pointsLabel         = window.checkoutConfig.rewardpoints.pointslabel;
        var pointsImage         = window.checkoutConfig.rewardpoints.pointsimage;
        var avaiblePoints       = parseFloat(window.checkoutConfig.rewardpoints.avaiblepoints);
        var rewardpoints = window.checkoutConfig.rewardpoints;

        var defaultSpendPoints  = 0;
        if (window.checkoutConfig.rewardpoints.spendpoints !== undefined && window.checkoutConfig.rewardpoints.spendpoints.value) {
            defaultSpendPoints = -parseFloat(rewardpoints.spendpoints.value);
        }
        avaiblePoints += defaultSpendPoints;
        if(typeof(currentRule) != 'undefined' && typeof(currentRule.value) != "undefined"){
            avaiblePoints += parseFloat(currentRule.value);
        }
        var rewardsSlider       = '';
        var isLimited           = false;
        var pointChanged        = true;
        var progressbarSettings = [];
        var currentAvailablePoints = avaiblePoints;

        if (window.rewardsprogressbar) {
            progressbarSettings = window.rewardsprogressbar;
        }

        if (currentRule.value == currentRule.max && currentRule.max > 0) {
            isLimited = true;
        }

        currentRule['loadmulti'] = true;
        if (window.isCustomerLoggedIn && spendingRules.length > 0) {
            isLoggedIn = true;
        } else {
            isLoggedIn = false;
        }

        if(showOnShoppingCart == 0){
            isLoggedIn = false;
        }
        if(showOnCheckoutPage == 0){
            isLoggedIn = false;
        }

        if ($('.lrw-product-usepoints').length) {
            $('.lrw-product-usepoints').show();
        }

        $('.lrw-available-points').val(avaiblePoints);

        ko.bindingHandlers.rewardslider = {
            init: function(element, valueAccessor, allBindingsAccessor) {
                rewardsSlider = $(element).rewardsslider({
                    sliderSettings : currentRule,
                    progressbar: progressbarSettings,
                    ajaxUrl: ajaxApplyUrl,
                    onStartSlider: function() {
                        pointChanged = false;
                    },
                    onStopSlider: function() {
                        pointChanged = true;
                    },
                    onChangeSlider: function(event, slider) {
                        for (var i = 0; i < spendingRules.length; i++) {
                            if (spendingRules[i]['id'] == slider.getRuleId()) {
                                spendingRules[i]['value'] = slider.getValue();
                                currentRule = spendingRules[i];
                                $(element).trigger('change');
                                break;
                            }
                        }
                    }
                }).trigger('change');
            },
            update: function(element, valueAccessor) {
                var rule = ko.utils.unwrapObservable(valueAccessor());
                if (rule.ruledata.max && rule.ruledata.value>rule.ruledata.max) {
                    return false;
                }
                rewardsSlider = $(element).rewardsslider({
                    sliderSettings: rule.ruledata,
                    progressbar: progressbarSettings
                });
                if (typeof(currentRule['loadmulti']) == 'undefined') {
                    currentRule['loadmulti'] = true;
                }
                $( element ).slider( "option", "step", rule.ruledata.step );
                $( element ).slider( "option", "max", rule.ruledata.max );
                $( element ).slider( "option", "min", rule.ruledata.min );
                rewardsSlider.data('lof-rewardsslider').updateValue(rule.ruledata.value, currentRule['loadmulti']);
                currentRule['loadmulti'] = false;
            }
        }

        var RuleModel = function(data) {
            this.id   = ko.observable(data.id);
            this.name = ko.observable(data.name);

            if (data.rulemax && data.value > data.rulemax) {
                data.value = 0;
            }

            this.value       = ko.observable(parseFloat(data.value));
            data['ruledata'] = [];
            this.ruledata    = data;
        }

        return Component.extend({
            defaults: {
                template: 'Lof_RewardPoints/cart/use_rewardpoints'
            },
            isLoggedIn: isLoggedIn,
            points: ko.observable(currentRule.value),
            maxPoints: ko.observable(currentRule.max),
            pointsLabel: ko.observable(pointsLabel),
            selected: ko.observable(isLimited),
            selectedRule : ko.observable(currentRule.id),
            slide: ko.observable(new RuleModel(currentRule)),
            availablePoints: ko.observable(avaiblePoints - currentRule.value),
            ruleMessage: ko.observable(currentRule.message),
            showSlide: ko.observable(currentRule.message?false:true),
            pointsImage: ko.observable(pointsImage),
            isShowRewardpoints: function() {
                return this.isLoggedIn && (currentAvailablePoints > 0) && typeof(rewardpoints) != "undefined"
            },
            getRules: function() {
                var data = [];
                for (var i = 0; i < spendingRules.length; i++) {
                    data[i] = new RuleModel(spendingRules[i]);
                }
                var rules = ko.observableArray(data);
                return rules;
            },
            changeRule: function(item) {
                var ruleId = this.selectedRule();
                var newRule;
                for (var i = 0; i <= spendingRules.length; i++) {
                    if (spendingRules[i]['id'] == ruleId) {
                        pointChanged             = false;
                        newRule                  = spendingRules[i];
                        currentRule              = newRule;
                        currentRule['loadmulti'] = true;
                        currentRule['ruledata']  = currentRule;
                        this.slide(new RuleModel(currentRule));
                        this.maxPoints(currentRule.max);
                        pointChanged             = true;
                        break;
                    }
                }
            },
            useMaxPoints: function() {
                pointChanged = false;
                currentRule['loadmulti'] = true;
                if (this.selected() === true) {
                    currentRule.value = currentRule.max;
                } else {
                    currentRule.value = currentRule.min;
                }
                this.slide(new RuleModel(currentRule));
                pointChanged = true;
                return true;
            },
            changeSlide: function(item, event) {
                var currentPoints = parseFloat(this.points());
                var currentValue  = parseFloat(currentRule.value);
                if (currentPoints!=currentValue) {
                    if (pointChanged != true) {
                        this.points(currentRule.value);
                    } else {
                        currentRule.value = parseFloat(currentPoints);
                    }

                    if (currentRule.max > 0 && currentRule.value>currentRule.max) {
                        return false;
                    }

                    if (isNaN(parseFloat(currentRule.value))) {
                        return false;
                    }

                    if (currentRule.value<0) {
                        return false;
                    }

                    this.availablePoints(avaiblePoints - currentRule.value);
                    this.checkIsLimitPoints();
                }
                if (currentRule.message) {
                    this.points(0);
                    this.availablePoints(avaiblePoints);
                    this.ruleMessage(currentRule.message);
                    this.showSlide(false);
                } else {
                    this.ruleMessage('');
                    this.showSlide(true);
                }
            },
            changePoints: function(item) {
                var points = this.points();
                if (!isNaN(parseFloat(points)) && /^\s*-?\d*(\.\d*)?\s*$/.test(points)) {
                    if ( points < currentRule.min) {
                        points = currentRule.min;
                    }
                    if ( points > currentRule.max) {
                        points = currentRule.max;
                    }
                    points = Math.round(points/currentRule.step) * currentRule.step;
                } else {
                    points = currentRule.max;
                }
                pointChanged = true;
                this.points(points);
                currentRule.value = points;
                currentRule['ruledata']['value'] = points;
                currentRule['loadmulti'] = true;
                this.availablePoints(avaiblePoints - points);
                this.slide(new RuleModel(currentRule));
                this.checkIsLimitPoints();
                return true;
            },
            checkIsLimitPoints: function() {
                if (currentRule.value == currentRule.max) {
                    this.selected(true);
                } else {
                    this.selected(false);
                }
            }
        });
});
