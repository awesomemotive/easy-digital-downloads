!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=77)}({77:function(e,t,n){"use strict";n.r(t);var r=function(e){return e=(e=e.toLowerCase()).trim()};function o(){var e=document.querySelector(".edd-email-tags-filter-search"),t=document.querySelectorAll(".edd-email-tags-list-item");e.addEventListener("keyup",(function(e){var n=e.target.value,o=function(e,t){var n=r(t),o=function(e){return-1!==r(e).indexOf(n)};return _.filter(e,(function(e){return o(e.title)||_.some(e.keywords,o)}))}(eddEmailTagsInserter.items,n);_.each(t,(function(e){var t=_.findWhere(o,{tag:e.dataset.tag});e.style.display=t?"block":"none"}))}))}document.addEventListener("DOMContentLoaded",(function(){var e;document.querySelector(".edd-email-tags-inserter").addEventListener("click",tb_position),e=document.querySelectorAll(".edd-email-tags-list-button"),_.each(e,(function(e){e.addEventListener("click",(function(){tb_remove(),window.send_to_editor(e.dataset.to_insert)}))})),o()}))}});
//# sourceMappingURL=edd-admin-email-tags.js.map