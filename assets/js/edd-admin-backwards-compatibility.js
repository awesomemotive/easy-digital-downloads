!function(e){var i={};function n(t){if(i[t])return i[t].exports;var s=i[t]={i:t,l:!1,exports:{}};return e[t].call(s.exports,s,s.exports,n),s.l=!0,s.exports}n.m=e,n.c=i,n.d=function(e,i,t){n.o(e,i)||Object.defineProperty(e,i,{enumerable:!0,get:t})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,i){if(1&i&&(e=n(e)),8&i)return e;if(4&i&&"object"==typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(n.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&i&&"string"!=typeof e)for(var s in e)n.d(t,s,function(i){return e[i]}.bind(null,s));return t},n.n=function(e){var i=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(i,"a",i),i},n.o=function(e,i){return Object.prototype.hasOwnProperty.call(e,i)},n.p="",n(n.s=71)}({0:function(e,i){e.exports=jQuery},71:function(e,i,n){(function(e){e(document).ready((function(e){e(document.body).find(".edd-custom-price-option-sections .edd-legacy-setting-label").each((function(){e(this).prependTo(e(this).nextAll("span:not(:has(>.edd-legacy-setting-label))").first())})),e(document.body).find(".edd-custom-price-option-sections").each((function(){e(this).find('[class*="purchase_limit"]').wrapAll('<div class="edd-purchase-limit-price-option-settings-legacy edd-custom-price-option-section"></div>'),e(this).find('[class*="shipping"]').wrapAll('<div class="edd-simple-shipping-price-option-settings-legacy edd-custom-price-option-section" style="display: none;"></div>'),e(this).find('[class*="sl-"]').wrapAll('<div class="edd-sl-price-option-settings-legacy edd-custom-price-option-section"></div>'),e(this).find('[class*="edd-recurring-"]').wrapAll('<div class="edd-recurring-price-option-settings-legacy edd-custom-price-option-section"></div>')})),e(document.body).find("#edd_enable_shipping","#edd_license_enabled").each((function(){var i=e("#edd_variable_pricing").is(":checked"),n=e("#edd_enable_shipping").is(":checked"),t=e(".edd-simple-shipping-price-option-settings-legacy"),s=e("#edd_license_enabled").is(":checked"),c=e(".edd-sl-price-option-settings-legacy");i&&(n?t.show():t.hide(),s?c.show():c.hide())})),e("#edd_enable_shipping").on("change",(function(){var i=e(this).is(":checked"),n=e(".edd-simple-shipping-price-option-settings-legacy");i?n.show():n.hide()})),e("#edd_license_enabled").on("change",(function(){var i=e(this).is(":checked"),n=e(".edd-sl-price-option-settings-legacy");i?n.show():n.hide()})),e(document.body).find(".edd-purchase-limit-price-option-settings-legacy").each((function(){e(this).prepend('<span class="edd-custom-price-option-section-title">'+edd_backcompat_vars.purchase_limit_settings+"</span>")})),e(document.body).find(".edd-simple-shipping-price-option-settings-legacy").each((function(){e(this).prepend('<span class="edd-custom-price-option-section-title">'+edd_backcompat_vars.simple_shipping_settings+"</span>")})),e(document.body).find(".edd-sl-price-option-settings-legacy").each((function(){e(this).prepend('<span class="edd-custom-price-option-section-title">'+edd_backcompat_vars.software_licensing_settings+"</span>")})),e(document.body).find(".edd-recurring-price-option-settings-legacy").each((function(){e(this).prepend('<span class="edd-custom-price-option-section-title">'+edd_backcompat_vars.recurring_payments_settings+"</span>")}))}))}).call(this,n(0))}});
//# sourceMappingURL=edd-admin-backwards-compatibility.js.map