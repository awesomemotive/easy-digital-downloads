(()=>{var e={216:(e,t,a)=>{var n=a(311);n(document).ready((function(e){function t(){var t=document.getElementById("edd-paypal-commerce-connect-wrap");t&&e.post(ajaxurl,{action:"edd_paypal_commerce_get_account_info",_ajax_nonce:t.getAttribute("data-nonce")},(function(e){var a="<p>"+eddPayPalConnectVars.defaultError+"</p>";e.success?(a=e.data.account_status,e.data.actions&&e.data.actions.length&&(a+='<p class="edd-paypal-connect-actions">'+e.data.actions.join(" ")+"</p>")):e.data&&e.data.message&&(a=e.data.message),t.innerHTML=a,t.classList.remove("notice-success","notice-warning","notice-error");var n=e.success&&e.data.status?"notice-"+e.data.status:"notice-error";t.classList.add(n)}))}e("#edd-paypal-commerce-connect").on("click",(function(t){t.preventDefault();var a=e("#edd-paypal-commerce-errors");a.empty().removeClass("notice notice-error");var n=document.getElementById("edd-paypal-commerce-connect");n.classList.add("updating-message"),n.disabled=!0,e.post(ajaxurl,{action:"edd_paypal_commerce_connect",_ajax_nonce:e(this).data("nonce")},(function(e){if(!e.success)return console.log("Connection failure",e.data),n.classList.remove("updating-message"),n.disabled=!1,void a.html("<p>"+e.data+"</p>").addClass("notice notice-error");var t=document.getElementById("edd-paypal-commerce-link");t.href=e.data.signupLink+"&displayMode=minibrowser",t.click()}))})),t(),e(document).on("click",".edd-paypal-connect-action",(function(a){a.preventDefault();var n=e(this);n.prop("disabled",!0),n.addClass("updating-message");var o=e("#edd-paypal-commerce-connect-wrap").find(".edd-paypal-actions-error-wrap");o.length&&o.remove(),e.post(ajaxurl,{action:n.data("action"),_ajax_nonce:n.data("nonce")},(function(e){n.prop("disabled",!1),n.removeClass("updating-message"),e.success?(n.addClass("updated-message"),t()):n.parent().after('<p class="edd-paypal-actions-error-wrap">'+e.data+"</p>")}))}))})),window.eddPayPalOnboardingCallback=function(e,t){var a=document.getElementById("edd-paypal-commerce-connect"),o=document.getElementById("edd-paypal-commerce-errors");n.post(ajaxurl,{action:"edd_paypal_commerce_get_access_token",auth_code:e,share_id:t,_ajax_nonce:a.getAttribute("data-nonce")},(function(e){if(a.classList.remove("updating-message"),!e.success)return a.disabled=!1,o.innerHTML="<p>"+e.data+"</p>",void o.classList.add("notice notice-error");a.classList.add("updated-message"),window.location.reload()}))}},311:e=>{"use strict";e.exports=jQuery}},t={};function a(n){var o=t[n];if(void 0!==o)return o.exports;var d=t[n]={exports:{}};return e[n](d,d.exports,a),d.exports}(()=>{"use strict";var e=a(311);a(216);var t=a(311),n=a(311);const o={init:function(){this.general(),this.misc(),this.gateways(),this.emails()},general:function(){const e=t(".edd-color-picker");if(e.length&&e.wpColorPicker(),"undefined"==typeof wp||"1"!==edd_vars.new_media_ui){const e=t(".edd_settings_upload_button");e.length>0&&(window.formfield="",t(document.body).on("click",e,(function(e){e.preventDefault(),window.formfield=t(this).parent().prev(),window.tbframe_interval=setInterval((function(){n("#TB_iframeContent").contents().find(".savesend .button").val(edd_vars.use_this_file).end().find("#insert-gallery, .wp-post-thumbnail").hide()}),2e3),tb_show(edd_vars.add_new_download,"media-upload.php?TB_iframe=true")})),window.edd_send_to_editor=window.send_to_editor,window.send_to_editor=function(e){window.formfield?(imgurl=t("a","<div>"+e+"</div>").attr("href"),window.formfield.val(imgurl),window.clearInterval(window.tbframe_interval),tb_remove()):window.edd_send_to_editor(e),window.send_to_editor=window.edd_send_to_editor,window.formfield="",window.imagefield=!1})}else{var a;window.formfield="",t(document.body).on("click",".edd_settings_upload_button",(function(e){e.preventDefault();const n=t(this);window.formfield=t(this).parent().prev(),a||((a=wp.media.frames.file_frame=wp.media({title:n.data("uploader_title"),library:{type:"image"},button:{text:n.data("uploader_button_text")},multiple:!1})).on("menu:render:default",(function(e){e.unset("library-separator"),e.unset("gallery"),e.unset("featured-image"),e.unset("embed"),e.set({})})),a.on("select",(function(){a.state().get("selection").each((function(e,t){e=e.toJSON(),window.formfield.val(e.url)}))}))),a.open()})),window.formfield=""}},misc:function(){const e=t('select[name="edd_settings[download_method]"]'),a=e.parent().parent().next();"direct"===e.val()&&(a.css("opacity","0.4"),a.find("input").prop("checked",!1).prop("disabled",!0)),e.on("change",(function(){"direct"===t(this).val()?(a.css("opacity","0.4"),a.find("input").prop("checked",!1).prop("disabled",!0)):(a.find("input").prop("disabled",!1),a.css("opacity","1"))}))},gateways:function(){t('#edd-payment-gateways input[type="checkbox"]').on("change",(function(){const e=t(this).data("gateway-key"),a=t("#edd_settings\\[default_gateway\\]"),n=a.find('option[value="'+e+'"]');n.prop("disabled",(function(e,t){return!t})),n.prop("selected")&&n.prop("selected",!1),a.trigger("chosen:updated")}))},emails:function(){t("#edd-recapture-connect").on("click",(function(a){a.preventDefault(),t(this).html(edd_vars.wait+' <span class="edd-loading"></span>'),document.body.style.cursor="wait",e.post(ajaxurl,{action:"edd_recapture_remote_install"},(function(e){e.success||!confirm(e.data.error)?window.location.href="https://recapture.io/register":location.reload()}))}))}};n(document).ready((function(e){o.init()}))})()})();