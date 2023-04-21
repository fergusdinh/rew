define([
	'jquery',
	'ko',
	'jquery/ui',
	], function ($, ko, ui) {
		'use strict';
		$.widget('lof.rewardsslider', {
			version: "1.0.0",
			options: {
				sliderSettings: {
					step: 50,
					min: 0,
					max: 500,
					value: 100,
					id: 0,
					quote: null,
					message: null,
					rulemin: 0,
					rulemax: 0,
					discount: 0
				},
				progressbar: {
					steps: 14
				},
				slider: null,
				value: null,
				sliderMinusSelector: '#lrw-slider-minus',
				sliderPlusSelector: '#lrw-slider-plus',
				pointTarget: '.lrw-slider-points',
				ajaxUrl: null
			},

			isShowed: false,

			currentValue: 0,

			oldValue: 0,

			_create: function () {
				this._setCurrentValue(this.options.sliderSettings.value);
				this._initSlider();
				this._intSliderToolbar();
				this._initProgressBar();
				var self = this;
				var step = self.options.sliderSettings.step;
				$(this.options.sliderMinusSelector).unbind().on('click', function(e) {
					self.subValue(step);
					self._onStopSlider();
				});
				$(this.options.sliderPlusSelector).unbind().on('click', function(e) {
					self.addValue(step);
					self._onStopSlider();
				});
			},

			reset: function() {
				self._setCurrentValue(self.slider.slider("value"));
			},

			_initSlider: function() {
				var self = this;
				this.slider = this.element.slider({
					step: self.options.sliderSettings.step,
					min: self.options.sliderSettings.min,
					max: self.options.sliderSettings.max,
					value: self.options.sliderSettings.value,
					start: function(event, ui) {
						self.oldValue = ui.value;
						self._onStartSlider();
					},
					change: function(event, ui ) {
						self._setCurrentValue(ui.value);
					},
					slide: function( event, ui ) {
						self._setCurrentValue(ui.value);
					},
					stop: function(event, ui) {
						if (self.oldValue != ui.value) {
							self._setCurrentValue(ui.value);
							self._onStopSlider();
						}
					}
				});
			},

			_initProgressBar: function (){
				$('#lrw-slider-progressbar').remove();
				var style = 'style="width: ' + (((this.getValue() - this.options.sliderSettings.min) / (this.options.sliderSettings.max - this.options.sliderSettings.min)) * 100) + '%"';
				var html = '<div id="lrw-slider-progressbar" ' + style + '></div>';
				this.element.prepend(html);
			},

			onChange: function() {
				this._onStopSlider();
			},

			_onStartSlider: function() {
				this._trigger('onStartSlider', null, this);
			},

			_onStopSlider: function() {
				this._trigger('onStopSlider', null, this);
				var discount = parseInt((this.getValue() / this.options.sliderSettings.step) * this.options.sliderSettings.discount);
				this.updateDiscount(discount);
				this.ajaxSubmit();
			},

			_intSliderToolbar: function() {
				this.element.find('.lrw-slider-toolbar').remove();
				var html  = '<div class="lrw-slider-toolbar">';
				var steps = this.options.progressbar.steps?this.options.progressbar.steps:14;
				for (var i = 0; i < steps; i++) {
					var classes = 'lrw-slider-line';
					if(i==0){
						classes += ' lrw-slider-line-first';
					}
					var css;
					css ='left: ' + (((i / steps) * 100)+2) + '%';
					html += '<span style="' + css + '" class=' + classes + '><span></span></span>';
				}
				html += '<span style="left: 100%" class="' + classes + ' lrw-slider-line-last"><span></span></span>';
				html += '</div>';
				this.element.append(html);
			},

			addValue: function(step) {
				var value = this.currentValue + step;
				this._setCurrentValue(value);
				this.slider.slider("value", value);
			},

			getRuleId: function() {
				return this.options.sliderSettings.id;
			},

			subValue: function(step) {
				var value = this.currentValue - step;
				this._setCurrentValue(value);
				this.slider.slider("value", value);
			},

			_setCurrentValue: function(val) {
				this.currentValue = val;
				this._refresh();

				var discount = parseInt((val / this.options.sliderSettings.step) * this.options.sliderSettings.discount);
				this.updateDiscount(discount);
			},

			updateValue: function(val, useAjax) {
				if ( $.mage.parseNumber(val) == 0 || (!isNaN($.mage.parseNumber(val)) && /^\s*-?\d*(\.\d*)?\s*$/.test(val) && val >= this.options.sliderSettings.min)) {
					this.slider.slider("value", val);
					this._setCurrentValue(val);
					if (useAjax) {
						this.ajaxSubmit();
					}
				} else {
					this.slider.slider("value", this.getValue());
				}
				return this.getValue();
			},

			getRuleId: function() {
				return this.options.sliderSettings.id;
			},

			getValue: function() {
				return this.currentValue;
			},

			_refresh: function() {
				this._initProgressBar();
				this._trigger('onChangeSlider', null, this);
			},

			updateRewardField: function () {
				$('.lrw-product-fulldiscount').val(this.discount);	
				$('.lrw-product-discount').val(this.options.sliderSettings.discount);	
				$('.lrw-product-rule').val(this.options.sliderSettings.id);
				$('.lrw-product-stepdiscount').val(this.options.sliderSettings.step);
				$('.lrw-quote').val(this.options.sliderSettings.quote);
				$('.lrw-product-rulemin').val(this.options.sliderSettings.rulemin);
				$('.lrw-product-rulemax').val(this.options.sliderSettings.rulemax);
				$('.lrw-product-points').val(this.options.sliderSettings.step);
				$('.lrw-product-spendpoints').val(this.getValue()).trigger("change");
			},

			updateRule: function(rule) {
				this.options.sliderSettings = rule;
				this._initSlider();
			},

			updateDiscount: function(discount) {
				this.discount = discount;
				this.updateRewardField();
			},

			ajaxSubmit: function() {
				if(this.options.ajaxUrl) {
					var self = this;
					var deferred = $.Deferred();
					$("#cart-totals").trigger('contentUpdated');
					$.ajax({
						url: this.options.ajaxUrl,
						data: {
							isAjax: true,
							spendpoints: this.getValue(),
							discount: this.discount,
							rule: this.options.sliderSettings.id,
							stepdiscount: this.options.sliderSettings.step,
							quote: this.options.sliderSettings.quote,
							rulemin: this.options.sliderSettings.rulemin,
							rulemax: this.options.sliderSettings.rulemax,
						},
						type: 'post',
						dataType: 'json'
					}).fail(function (error) {
						console.log(JSON.stringify(error));
					});
				}
			}
		})
	return $.lof.rewardsslider;
})