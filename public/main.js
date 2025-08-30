(self["webpackChunkcleanwhiskers"] = self["webpackChunkcleanwhiskers"] || []).push([["main"],{

/***/ "./assets/app.js":
/*!***********************!*\
  !*** ./assets/app.js ***!
  \***********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _bootstrap_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./bootstrap.js */ "./assets/bootstrap.js");
/* harmony import */ var _bootstrap_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_bootstrap_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _styles_app_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/app.css */ "./assets/styles/app.css");
/* harmony import */ var _styles_utilities_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./styles/utilities.css */ "./assets/styles/utilities.css");
/* harmony import */ var _styles_blocks_groomer_card_css__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./styles/blocks/groomer-card.css */ "./assets/styles/blocks/groomer-card.css");
/* harmony import */ var _styles_components_badge_css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./styles/components/badge.css */ "./assets/styles/components/badge.css");
/* harmony import */ var _styles_components_city_card_css__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./styles/components/city-card.css */ "./assets/styles/components/city-card.css");
/* harmony import */ var _styles_components_spinner_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./styles/components/spinner.css */ "./assets/styles/components/spinner.css");
/* harmony import */ var _styles_components_sticky_cta_css__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./styles/components/sticky-cta.css */ "./assets/styles/components/sticky-cta.css");
/* harmony import */ var _styles_blocks_trust_box_css__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./styles/blocks/trust-box.css */ "./assets/styles/blocks/trust-box.css");
/* harmony import */ var _styles_blocks_card_css__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./styles/blocks/card.css */ "./assets/styles/blocks/card.css");
/* harmony import */ var _controllers_index_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./controllers/index.js */ "./assets/controllers/index.js");

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */









// Register controllers (side-effect imports)


/***/ }),

/***/ "./assets/bootstrap.js":
/*!*****************************!*\
  !*** ./assets/bootstrap.js ***!
  \*****************************/
/***/ (() => {

// import { startStimulusApp } from '@symfony/stimulus-bundle';

// const app = startStimulusApp();
// // register any custom, 3rd party controllers here
// // app.register('some_controller_name', SomeImportedController);

/***/ }),

/***/ "./assets/controllers/emailCapture.js":
/*!********************************************!*\
  !*** ./assets/controllers/emailCapture.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _helpers_dom_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helpers/dom.js */ "./assets/helpers/dom.js");
// Placeholder: safely wire up an email capture form if present

(function initEmailCapture() {
  var section = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_0__.qs)('#email-capture');
  if (!section) return;
  var form = section.querySelector('form');
  if (!form) return;
  (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_0__.on)(form, 'submit', function (e) {
    // Allow default submit; ensure basic validation without console errors
    var email = form.querySelector('input[type="email"]');
    if (email && !email.value) {
      e.preventDefault();
      email.focus();
    }
  });
})();

/***/ }),

/***/ "./assets/controllers/heroScroll.js":
/*!******************************************!*\
  !*** ./assets/controllers/heroScroll.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_string_starts_with_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.string.starts-with.js */ "./node_modules/core-js/modules/es.string.starts-with.js");
/* harmony import */ var core_js_modules_es_string_starts_with_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_starts_with_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helpers/dom.js */ "./assets/helpers/dom.js");




// Smooth-scroll handler for elements with [data-scroll-target]

(function initHeroScroll() {
  var triggers = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.qsa)('[data-scroll-target]');
  if (!triggers.length) return;
  var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  triggers.forEach(function (trigger) {
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(trigger, 'click', function (e) {
      var targetSel = trigger.getAttribute('data-scroll-target');
      if (!targetSel) return;
      var targetEl = document.querySelector(targetSel);
      if (!targetEl) return;
      e.preventDefault();
      if (prefersReduced) {
        // Respect reduced motion: jump-link fallback
        try {
          targetEl.setAttribute('tabindex', '-1');
          targetEl.focus({
            preventScroll: true
          });
        } catch (_) {}
        var id = targetEl.id ? "#".concat(targetEl.id) : targetSel;
        if (id && id.startsWith('#')) {
          window.location.hash = id.substring(1);
        } else {
          targetEl.scrollIntoView();
        }
      } else {
        (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.smoothScrollTo)(targetEl);
      }
    });
  });
})();

/***/ }),

/***/ "./assets/controllers/index.js":
/*!*************************************!*\
  !*** ./assets/controllers/index.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _heroScroll_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./heroScroll.js */ "./assets/controllers/heroScroll.js");
/* harmony import */ var _sortHandler_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./sortHandler.js */ "./assets/controllers/sortHandler.js");
/* harmony import */ var _sortHandler_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_sortHandler_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _tooltip_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./tooltip.js */ "./assets/controllers/tooltip.js");
/* harmony import */ var _emailCapture_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./emailCapture.js */ "./assets/controllers/emailCapture.js");
// Central registry for lightweight controllers (vanilla modules)
// Each imported module can self-initialize or export hooks as needed.






/***/ }),

/***/ "./assets/controllers/sortHandler.js":
/*!*******************************************!*\
  !*** ./assets/controllers/sortHandler.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! core-js/modules/es.array.iterator.js */ "./node_modules/core-js/modules/es.array.iterator.js");
__webpack_require__(/*! core-js/modules/es.date.to-string.js */ "./node_modules/core-js/modules/es.date.to-string.js");
__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
__webpack_require__(/*! core-js/modules/es.regexp.to-string.js */ "./node_modules/core-js/modules/es.regexp.to-string.js");
__webpack_require__(/*! core-js/modules/es.set.js */ "./node_modules/core-js/modules/es.set.js");
__webpack_require__(/*! core-js/modules/es.string.iterator.js */ "./node_modules/core-js/modules/es.string.iterator.js");
__webpack_require__(/*! core-js/modules/es.string.trim.js */ "./node_modules/core-js/modules/es.string.trim.js");
__webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");
__webpack_require__(/*! core-js/modules/web.url.js */ "./node_modules/core-js/modules/web.url.js");
__webpack_require__(/*! core-js/modules/web.url.to-json.js */ "./node_modules/core-js/modules/web.url.to-json.js");
__webpack_require__(/*! core-js/modules/web.url-search-params.js */ "./node_modules/core-js/modules/web.url-search-params.js");
// Sort dropdown: updates URL ?sort= and reloads, preserving other params.
(function initSortHandler() {
  var select = document.querySelector('#sort-control select#sort');
  if (!select) return;
  var allowed = new Set(['recommended', 'price_asc', 'rating_desc']);
  select.addEventListener('change', function () {
    var value = String(select.value || '').trim();
    if (!allowed.has(value)) return; // ignore unexpected values

    var url = new URL(window.location.href);
    url.searchParams.set('sort', value);

    // Reload with updated params, preserving path and other params
    window.location.assign(url.toString());
  });
})();

/***/ }),

/***/ "./assets/controllers/tooltip.js":
/*!***************************************!*\
  !*** ./assets/controllers/tooltip.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_string_trim_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.string.trim.js */ "./node_modules/core-js/modules/es.string.trim.js");
/* harmony import */ var core_js_modules_es_string_trim_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_trim_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../helpers/dom.js */ "./assets/helpers/dom.js");




// Accessible tooltip controller (inline tooltips, no focus trap)

(function initTooltips() {
  var triggers = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.qsa)('[data-tooltip][aria-describedby]');
  if (!triggers.length) return;
  var current = null; // { trigger, tip }
  var unbindDocHandlers = function unbindDocHandlers() {};
  function getTip(trigger) {
    var id = trigger.getAttribute('aria-describedby');
    if (!id) return null;
    return document.getElementById(id);
  }
  function setVisible(tip, yes) {
    if (!tip) return;
    tip.classList.toggle('tooltip--hidden', !yes);
    tip.toggleAttribute('data-open', !!yes);
  }
  function measureTip(tip) {
    // Temporarily ensure measurable without flashing
    var prevTrans = tip.style.transition;
    var prevVis = tip.style.visibility;
    tip.style.transition = 'none';
    tip.style.visibility = 'hidden';
    tip.classList.remove('tooltip--hidden');
    var rect = tip.getBoundingClientRect();
    tip.classList.add('tooltip--hidden');
    tip.style.visibility = prevVis;
    tip.style.transition = prevTrans;
    return rect;
  }
  function clamp(n, min, max) {
    return Math.max(min, Math.min(max, n));
  }
  function positionTip(trigger, tip) {
    var spacing = 8;
    var tRect = trigger.getBoundingClientRect();
    var vw = window.innerWidth;
    var vh = window.innerHeight;

    // Ensure content is up to date before measuring
    var tipRect = measureTip(tip);

    // Prefer above; if no room, place below
    var top = tRect.top - tipRect.height - spacing;
    var side = 'top';
    if (top < 8) {
      top = tRect.bottom + spacing;
      side = 'bottom';
    }
    var left = tRect.left + tRect.width / 2 - tipRect.width / 2;
    left = clamp(left, 8, vw - tipRect.width - 8);
    // Prevent bottom overflow
    if (top + tipRect.height > vh - 8) {
      top = Math.max(8, vh - tipRect.height - 8);
    }
    tip.style.top = "".concat(Math.round(top), "px");
    tip.style.left = "".concat(Math.round(left), "px");
    tip.setAttribute('data-side', side);
  }
  function show(trigger) {
    var _trigger$dataset$tool;
    var tip = getTip(trigger);
    if (!tip) return;
    // Put content
    var text = (_trigger$dataset$tool = trigger.dataset.tooltip) === null || _trigger$dataset$tool === void 0 ? void 0 : _trigger$dataset$tool.trim();
    if (text) tip.textContent = text;
    // Mark state
    trigger.setAttribute('aria-expanded', 'true');
    positionTip(trigger, tip);
    setVisible(tip, true);
    current = {
      trigger: trigger,
      tip: tip
    };

    // Bind global listeners
    var offKey = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(document, 'keydown', function (e) {
      if (e.key === 'Escape') hide();
    });
    var offClick = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(document, 'mousedown', function (e) {
      if (!current) return;
      var withinTrigger = current.trigger.contains(e.target);
      var withinTip = current.tip.contains(e.target);
      if (!withinTrigger && !withinTip) hide();
    });
    var offTouch = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(document, 'touchstart', function (e) {
      if (!current) return;
      var withinTrigger = current.trigger.contains(e.target);
      var withinTip = current.tip.contains(e.target);
      if (!withinTrigger && !withinTip) hide();
    }, {
      passive: true
    });
    var offScroll = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(window, 'scroll', function () {
      return current && positionTip(current.trigger, current.tip);
    });
    var offResize = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(window, 'resize', function () {
      return current && positionTip(current.trigger, current.tip);
    });
    unbindDocHandlers = function unbindDocHandlers() {
      offKey();
      offClick();
      offTouch();
      offScroll();
      offResize();
    };
  }
  function hide() {
    if (!current) return;
    setVisible(current.tip, false);
    current.trigger.setAttribute('aria-expanded', 'false');
    unbindDocHandlers();
    current = null;
  }
  triggers.forEach(function (el) {
    // Hover/focus show
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(el, 'mouseenter', function () {
      return show(el);
    });
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(el, 'focus', function () {
      return show(el);
    });
    // Mouseleave/blur hide (but allow click to toggle)
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(el, 'mouseleave', function () {
      if (document.activeElement !== el) hide();
    });
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(el, 'blur', function () {
      return hide();
    });
    // Tap/click toggle for mobile
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(el, 'click', function (e) {
      e.preventDefault();
      if (current && current.trigger === el) hide();else show(el);
    });
    (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_4__.on)(el, 'touchstart', function (e) {
      e.preventDefault();
      if (current && current.trigger === el) hide();else show(el);
    }, {
      passive: false
    });
  });
})();

/***/ }),

/***/ "./assets/helpers/dom.js":
/*!*******************************!*\
  !*** ./assets/helpers/dom.js ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   on: () => (/* binding */ on),
/* harmony export */   qs: () => (/* binding */ qs),
/* harmony export */   qsa: () => (/* binding */ qsa),
/* harmony export */   smoothScrollTo: () => (/* binding */ smoothScrollTo)
/* harmony export */ });
/* harmony import */ var core_js_modules_es_array_from_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.from.js */ "./node_modules/core-js/modules/es.array.from.js");
/* harmony import */ var core_js_modules_es_array_from_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_from_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_string_iterator_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.string.iterator.js */ "./node_modules/core-js/modules/es.string.iterator.js");
/* harmony import */ var core_js_modules_es_string_iterator_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_iterator_js__WEBPACK_IMPORTED_MODULE_1__);


// Lightweight DOM utilities (no dependencies)

/**
 * Query a single element within an optional context.
 * @param {string} selector
 * @param {ParentNode} [ctx=document]
 * @returns {Element|null}
 */
function qs(selector) {
  var ctx = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document;
  return ctx.querySelector(selector);
}

/**
 * Query all elements as an array within an optional context.
 * @param {string} selector
 * @param {ParentNode} [ctx=document]
 * @returns {Element[]}
 */
function qsa(selector) {
  var ctx = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document;
  return Array.from(ctx.querySelectorAll(selector));
}

/**
 * Add an event listener with a small convenience wrapper.
 * @param {EventTarget} target
 * @param {string} type
 * @param {Function} handler
 * @param {AddEventListenerOptions|boolean} [options]
 */
function on(target, type, handler, options) {
  if (!target) return function () {};
  target.addEventListener(type, handler, options);
  return function () {
    return target.removeEventListener(type, handler, options);
  };
}

/**
 * Smoothly scroll to an element (or y position), with safe fallback.
 * @param {Element|number} elOrY
 */
function smoothScrollTo(elOrY) {
  var y = typeof elOrY === 'number' ? elOrY : elOrY !== null && elOrY !== void 0 && elOrY.getBoundingClientRect ? window.scrollY + elOrY.getBoundingClientRect().top : 0;
  try {
    window.scrollTo({
      top: y,
      behavior: 'smooth'
    });
  } catch (_) {
    // Fallback for older browsers
    window.scrollTo(0, y);
  }
}

/***/ }),

/***/ "./assets/styles/app.css":
/*!*******************************!*\
  !*** ./assets/styles/app.css ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/blocks/card.css":
/*!***************************************!*\
  !*** ./assets/styles/blocks/card.css ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/blocks/groomer-card.css":
/*!***********************************************!*\
  !*** ./assets/styles/blocks/groomer-card.css ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/blocks/trust-box.css":
/*!********************************************!*\
  !*** ./assets/styles/blocks/trust-box.css ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/components/badge.css":
/*!********************************************!*\
  !*** ./assets/styles/components/badge.css ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/components/city-card.css":
/*!************************************************!*\
  !*** ./assets/styles/components/city-card.css ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/components/spinner.css":
/*!**********************************************!*\
  !*** ./assets/styles/components/spinner.css ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/components/sticky-cta.css":
/*!*************************************************!*\
  !*** ./assets/styles/components/sticky-cta.css ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/styles/utilities.css":
/*!*************************************!*\
  !*** ./assets/styles/utilities.css ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_modules_es_array_for-each_js-node_modules_core-js_modules_es_arr-1aea14"], () => (__webpack_exec__("./assets/app.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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Q3ZCO0FBQ0YsQzs7Ozs7Ozs7Ozs7O0FDbERBOzs7Ozs7Ozs7Ozs7O0FDQUE7Ozs7Ozs7Ozs7Ozs7QUNBQTs7Ozs7Ozs7Ozs7OztBQ0FBOzs7Ozs7Ozs7Ozs7O0FDQUE7Ozs7Ozs7Ozs7Ozs7QUNBQTs7Ozs7Ozs7Ozs7OztBQ0FBOzs7Ozs7Ozs7Ozs7O0FDQUE7Ozs7Ozs7Ozs7Ozs7QUNBQSIsInNvdXJjZXMiOlsid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvYXBwLmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvYm9vdHN0cmFwLmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvY29udHJvbGxlcnMvZW1haWxDYXB0dXJlLmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvY29udHJvbGxlcnMvaGVyb1Njcm9sbC5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2NvbnRyb2xsZXJzL2luZGV4LmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvY29udHJvbGxlcnMvc29ydEhhbmRsZXIuanMiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9jb250cm9sbGVycy90b29sdGlwLmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvaGVscGVycy9kb20uanMiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9zdHlsZXMvYXBwLmNzcz8zZmJhIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2Jsb2Nrcy9jYXJkLmNzcz9jYzI1Iiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2Jsb2Nrcy9ncm9vbWVyLWNhcmQuY3NzP2UxMmUiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9zdHlsZXMvYmxvY2tzL3RydXN0LWJveC5jc3M/Mzg1MyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9jb21wb25lbnRzL2JhZGdlLmNzcz85ODhhIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2NvbXBvbmVudHMvY2l0eS1jYXJkLmNzcz8zZmU5Iiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2NvbXBvbmVudHMvc3Bpbm5lci5jc3M/MDMyYSIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9jb21wb25lbnRzL3N0aWNreS1jdGEuY3NzPzZiZWIiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9zdHlsZXMvdXRpbGl0aWVzLmNzcz81MmVhIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCAnLi9ib290c3RyYXAuanMnO1xuLypcbiAqIFdlbGNvbWUgdG8geW91ciBhcHAncyBtYWluIEphdmFTY3JpcHQgZmlsZSFcbiAqXG4gKiBUaGlzIGZpbGUgd2lsbCBiZSBpbmNsdWRlZCBvbnRvIHRoZSBwYWdlIHZpYSB0aGUgaW1wb3J0bWFwKCkgVHdpZyBmdW5jdGlvbixcbiAqIHdoaWNoIHNob3VsZCBhbHJlYWR5IGJlIGluIHlvdXIgYmFzZS5odG1sLnR3aWcuXG4gKi9cbmltcG9ydCAnLi9zdHlsZXMvYXBwLmNzcyc7XG5pbXBvcnQgJy4vc3R5bGVzL3V0aWxpdGllcy5jc3MnO1xuaW1wb3J0ICcuL3N0eWxlcy9ibG9ja3MvZ3Jvb21lci1jYXJkLmNzcyc7XG5pbXBvcnQgJy4vc3R5bGVzL2NvbXBvbmVudHMvYmFkZ2UuY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvY29tcG9uZW50cy9jaXR5LWNhcmQuY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvY29tcG9uZW50cy9zcGlubmVyLmNzcyc7XG5pbXBvcnQgJy4vc3R5bGVzL2NvbXBvbmVudHMvc3RpY2t5LWN0YS5jc3MnO1xuaW1wb3J0ICcuL3N0eWxlcy9ibG9ja3MvdHJ1c3QtYm94LmNzcyc7XG5pbXBvcnQgJy4vc3R5bGVzL2Jsb2Nrcy9jYXJkLmNzcyc7XG4vLyBSZWdpc3RlciBjb250cm9sbGVycyAoc2lkZS1lZmZlY3QgaW1wb3J0cylcbmltcG9ydCAnLi9jb250cm9sbGVycy9pbmRleC5qcyc7XG4iLCIvLyBpbXBvcnQgeyBzdGFydFN0aW11bHVzQXBwIH0gZnJvbSAnQHN5bWZvbnkvc3RpbXVsdXMtYnVuZGxlJztcblxuLy8gY29uc3QgYXBwID0gc3RhcnRTdGltdWx1c0FwcCgpO1xuLy8gLy8gcmVnaXN0ZXIgYW55IGN1c3RvbSwgM3JkIHBhcnR5IGNvbnRyb2xsZXJzIGhlcmVcbi8vIC8vIGFwcC5yZWdpc3Rlcignc29tZV9jb250cm9sbGVyX25hbWUnLCBTb21lSW1wb3J0ZWRDb250cm9sbGVyKTtcbiIsIi8vIFBsYWNlaG9sZGVyOiBzYWZlbHkgd2lyZSB1cCBhbiBlbWFpbCBjYXB0dXJlIGZvcm0gaWYgcHJlc2VudFxuaW1wb3J0IHsgcXMsIG9uIH0gZnJvbSAnLi4vaGVscGVycy9kb20uanMnO1xuXG4oZnVuY3Rpb24gaW5pdEVtYWlsQ2FwdHVyZSgpIHtcbiAgY29uc3Qgc2VjdGlvbiA9IHFzKCcjZW1haWwtY2FwdHVyZScpO1xuICBpZiAoIXNlY3Rpb24pIHJldHVybjtcbiAgY29uc3QgZm9ybSA9IHNlY3Rpb24ucXVlcnlTZWxlY3RvcignZm9ybScpO1xuICBpZiAoIWZvcm0pIHJldHVybjtcbiAgb24oZm9ybSwgJ3N1Ym1pdCcsIChlKSA9PiB7XG4gICAgLy8gQWxsb3cgZGVmYXVsdCBzdWJtaXQ7IGVuc3VyZSBiYXNpYyB2YWxpZGF0aW9uIHdpdGhvdXQgY29uc29sZSBlcnJvcnNcbiAgICBjb25zdCBlbWFpbCA9IGZvcm0ucXVlcnlTZWxlY3RvcignaW5wdXRbdHlwZT1cImVtYWlsXCJdJyk7XG4gICAgaWYgKGVtYWlsICYmICFlbWFpbC52YWx1ZSkge1xuICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgZW1haWwuZm9jdXMoKTtcbiAgICB9XG4gIH0pO1xufSkoKTtcblxuIiwiLy8gU21vb3RoLXNjcm9sbCBoYW5kbGVyIGZvciBlbGVtZW50cyB3aXRoIFtkYXRhLXNjcm9sbC10YXJnZXRdXG5pbXBvcnQgeyBxc2EsIG9uLCBzbW9vdGhTY3JvbGxUbyB9IGZyb20gJy4uL2hlbHBlcnMvZG9tLmpzJztcblxuKGZ1bmN0aW9uIGluaXRIZXJvU2Nyb2xsKCkge1xuICBjb25zdCB0cmlnZ2VycyA9IHFzYSgnW2RhdGEtc2Nyb2xsLXRhcmdldF0nKTtcbiAgaWYgKCF0cmlnZ2Vycy5sZW5ndGgpIHJldHVybjtcblxuICBjb25zdCBwcmVmZXJzUmVkdWNlZCA9IHdpbmRvdy5tYXRjaE1lZGlhICYmIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1yZWR1Y2VkLW1vdGlvbjogcmVkdWNlKScpLm1hdGNoZXM7XG5cbiAgdHJpZ2dlcnMuZm9yRWFjaCgodHJpZ2dlcikgPT4ge1xuICAgIG9uKHRyaWdnZXIsICdjbGljaycsIChlKSA9PiB7XG4gICAgICBjb25zdCB0YXJnZXRTZWwgPSB0cmlnZ2VyLmdldEF0dHJpYnV0ZSgnZGF0YS1zY3JvbGwtdGFyZ2V0Jyk7XG4gICAgICBpZiAoIXRhcmdldFNlbCkgcmV0dXJuO1xuICAgICAgY29uc3QgdGFyZ2V0RWwgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKHRhcmdldFNlbCk7XG4gICAgICBpZiAoIXRhcmdldEVsKSByZXR1cm47XG5cbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgaWYgKHByZWZlcnNSZWR1Y2VkKSB7XG4gICAgICAgIC8vIFJlc3BlY3QgcmVkdWNlZCBtb3Rpb246IGp1bXAtbGluayBmYWxsYmFja1xuICAgICAgICB0cnkge1xuICAgICAgICAgIHRhcmdldEVsLnNldEF0dHJpYnV0ZSgndGFiaW5kZXgnLCAnLTEnKTtcbiAgICAgICAgICB0YXJnZXRFbC5mb2N1cyh7IHByZXZlbnRTY3JvbGw6IHRydWUgfSk7XG4gICAgICAgIH0gY2F0Y2ggKF8pIHt9XG4gICAgICAgIGNvbnN0IGlkID0gdGFyZ2V0RWwuaWQgPyBgIyR7dGFyZ2V0RWwuaWR9YCA6IHRhcmdldFNlbDtcbiAgICAgICAgaWYgKGlkICYmIGlkLnN0YXJ0c1dpdGgoJyMnKSkge1xuICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gaWQuc3Vic3RyaW5nKDEpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIHRhcmdldEVsLnNjcm9sbEludG9WaWV3KCk7XG4gICAgICAgIH1cbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHNtb290aFNjcm9sbFRvKHRhcmdldEVsKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfSk7XG59KSgpO1xuIiwiLy8gQ2VudHJhbCByZWdpc3RyeSBmb3IgbGlnaHR3ZWlnaHQgY29udHJvbGxlcnMgKHZhbmlsbGEgbW9kdWxlcylcbi8vIEVhY2ggaW1wb3J0ZWQgbW9kdWxlIGNhbiBzZWxmLWluaXRpYWxpemUgb3IgZXhwb3J0IGhvb2tzIGFzIG5lZWRlZC5cblxuaW1wb3J0ICcuL2hlcm9TY3JvbGwuanMnO1xuaW1wb3J0ICcuL3NvcnRIYW5kbGVyLmpzJztcbmltcG9ydCAnLi90b29sdGlwLmpzJztcbmltcG9ydCAnLi9lbWFpbENhcHR1cmUuanMnO1xuXG4iLCIvLyBTb3J0IGRyb3Bkb3duOiB1cGRhdGVzIFVSTCA/c29ydD0gYW5kIHJlbG9hZHMsIHByZXNlcnZpbmcgb3RoZXIgcGFyYW1zLlxuKGZ1bmN0aW9uIGluaXRTb3J0SGFuZGxlcigpIHtcbiAgY29uc3Qgc2VsZWN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3NvcnQtY29udHJvbCBzZWxlY3Qjc29ydCcpO1xuICBpZiAoIXNlbGVjdCkgcmV0dXJuO1xuXG4gIGNvbnN0IGFsbG93ZWQgPSBuZXcgU2V0KFsncmVjb21tZW5kZWQnLCAncHJpY2VfYXNjJywgJ3JhdGluZ19kZXNjJ10pO1xuXG4gIHNlbGVjdC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCAoKSA9PiB7XG4gICAgY29uc3QgdmFsdWUgPSBTdHJpbmcoc2VsZWN0LnZhbHVlIHx8ICcnKS50cmltKCk7XG4gICAgaWYgKCFhbGxvd2VkLmhhcyh2YWx1ZSkpIHJldHVybjsgLy8gaWdub3JlIHVuZXhwZWN0ZWQgdmFsdWVzXG5cbiAgICBjb25zdCB1cmwgPSBuZXcgVVJMKHdpbmRvdy5sb2NhdGlvbi5ocmVmKTtcbiAgICB1cmwuc2VhcmNoUGFyYW1zLnNldCgnc29ydCcsIHZhbHVlKTtcblxuICAgIC8vIFJlbG9hZCB3aXRoIHVwZGF0ZWQgcGFyYW1zLCBwcmVzZXJ2aW5nIHBhdGggYW5kIG90aGVyIHBhcmFtc1xuICAgIHdpbmRvdy5sb2NhdGlvbi5hc3NpZ24odXJsLnRvU3RyaW5nKCkpO1xuICB9KTtcbn0pKCk7XG4iLCIvLyBBY2Nlc3NpYmxlIHRvb2x0aXAgY29udHJvbGxlciAoaW5saW5lIHRvb2x0aXBzLCBubyBmb2N1cyB0cmFwKVxuaW1wb3J0IHsgcXNhLCBvbiB9IGZyb20gJy4uL2hlbHBlcnMvZG9tLmpzJztcblxuKGZ1bmN0aW9uIGluaXRUb29sdGlwcygpIHtcbiAgY29uc3QgdHJpZ2dlcnMgPSBxc2EoJ1tkYXRhLXRvb2x0aXBdW2FyaWEtZGVzY3JpYmVkYnldJyk7XG4gIGlmICghdHJpZ2dlcnMubGVuZ3RoKSByZXR1cm47XG5cbiAgbGV0IGN1cnJlbnQgPSBudWxsOyAvLyB7IHRyaWdnZXIsIHRpcCB9XG4gIGxldCB1bmJpbmREb2NIYW5kbGVycyA9ICgpID0+IHt9O1xuXG4gIGZ1bmN0aW9uIGdldFRpcCh0cmlnZ2VyKSB7XG4gICAgY29uc3QgaWQgPSB0cmlnZ2VyLmdldEF0dHJpYnV0ZSgnYXJpYS1kZXNjcmliZWRieScpO1xuICAgIGlmICghaWQpIHJldHVybiBudWxsO1xuICAgIHJldHVybiBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG4gIH1cblxuICBmdW5jdGlvbiBzZXRWaXNpYmxlKHRpcCwgeWVzKSB7XG4gICAgaWYgKCF0aXApIHJldHVybjtcbiAgICB0aXAuY2xhc3NMaXN0LnRvZ2dsZSgndG9vbHRpcC0taGlkZGVuJywgIXllcyk7XG4gICAgdGlwLnRvZ2dsZUF0dHJpYnV0ZSgnZGF0YS1vcGVuJywgISF5ZXMpO1xuICB9XG5cbiAgZnVuY3Rpb24gbWVhc3VyZVRpcCh0aXApIHtcbiAgICAvLyBUZW1wb3JhcmlseSBlbnN1cmUgbWVhc3VyYWJsZSB3aXRob3V0IGZsYXNoaW5nXG4gICAgY29uc3QgcHJldlRyYW5zID0gdGlwLnN0eWxlLnRyYW5zaXRpb247XG4gICAgY29uc3QgcHJldlZpcyA9IHRpcC5zdHlsZS52aXNpYmlsaXR5O1xuICAgIHRpcC5zdHlsZS50cmFuc2l0aW9uID0gJ25vbmUnO1xuICAgIHRpcC5zdHlsZS52aXNpYmlsaXR5ID0gJ2hpZGRlbic7XG4gICAgdGlwLmNsYXNzTGlzdC5yZW1vdmUoJ3Rvb2x0aXAtLWhpZGRlbicpO1xuICAgIGNvbnN0IHJlY3QgPSB0aXAuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgdGlwLmNsYXNzTGlzdC5hZGQoJ3Rvb2x0aXAtLWhpZGRlbicpO1xuICAgIHRpcC5zdHlsZS52aXNpYmlsaXR5ID0gcHJldlZpcztcbiAgICB0aXAuc3R5bGUudHJhbnNpdGlvbiA9IHByZXZUcmFucztcbiAgICByZXR1cm4gcmVjdDtcbiAgfVxuXG4gIGZ1bmN0aW9uIGNsYW1wKG4sIG1pbiwgbWF4KSB7IHJldHVybiBNYXRoLm1heChtaW4sIE1hdGgubWluKG1heCwgbikpOyB9XG5cbiAgZnVuY3Rpb24gcG9zaXRpb25UaXAodHJpZ2dlciwgdGlwKSB7XG4gICAgY29uc3Qgc3BhY2luZyA9IDg7XG4gICAgY29uc3QgdFJlY3QgPSB0cmlnZ2VyLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgIGNvbnN0IHZ3ID0gd2luZG93LmlubmVyV2lkdGg7XG4gICAgY29uc3QgdmggPSB3aW5kb3cuaW5uZXJIZWlnaHQ7XG5cbiAgICAvLyBFbnN1cmUgY29udGVudCBpcyB1cCB0byBkYXRlIGJlZm9yZSBtZWFzdXJpbmdcbiAgICBjb25zdCB0aXBSZWN0ID0gbWVhc3VyZVRpcCh0aXApO1xuXG4gICAgLy8gUHJlZmVyIGFib3ZlOyBpZiBubyByb29tLCBwbGFjZSBiZWxvd1xuICAgIGxldCB0b3AgPSB0UmVjdC50b3AgLSB0aXBSZWN0LmhlaWdodCAtIHNwYWNpbmc7XG4gICAgbGV0IHNpZGUgPSAndG9wJztcbiAgICBpZiAodG9wIDwgOCkge1xuICAgICAgdG9wID0gdFJlY3QuYm90dG9tICsgc3BhY2luZztcbiAgICAgIHNpZGUgPSAnYm90dG9tJztcbiAgICB9XG4gICAgbGV0IGxlZnQgPSB0UmVjdC5sZWZ0ICsgKHRSZWN0LndpZHRoIC8gMikgLSAodGlwUmVjdC53aWR0aCAvIDIpO1xuICAgIGxlZnQgPSBjbGFtcChsZWZ0LCA4LCB2dyAtIHRpcFJlY3Qud2lkdGggLSA4KTtcbiAgICAvLyBQcmV2ZW50IGJvdHRvbSBvdmVyZmxvd1xuICAgIGlmICh0b3AgKyB0aXBSZWN0LmhlaWdodCA+IHZoIC0gOCkge1xuICAgICAgdG9wID0gTWF0aC5tYXgoOCwgdmggLSB0aXBSZWN0LmhlaWdodCAtIDgpO1xuICAgIH1cblxuICAgIHRpcC5zdHlsZS50b3AgPSBgJHtNYXRoLnJvdW5kKHRvcCl9cHhgO1xuICAgIHRpcC5zdHlsZS5sZWZ0ID0gYCR7TWF0aC5yb3VuZChsZWZ0KX1weGA7XG4gICAgdGlwLnNldEF0dHJpYnV0ZSgnZGF0YS1zaWRlJywgc2lkZSk7XG4gIH1cblxuICBmdW5jdGlvbiBzaG93KHRyaWdnZXIpIHtcbiAgICBjb25zdCB0aXAgPSBnZXRUaXAodHJpZ2dlcik7XG4gICAgaWYgKCF0aXApIHJldHVybjtcbiAgICAvLyBQdXQgY29udGVudFxuICAgIGNvbnN0IHRleHQgPSB0cmlnZ2VyLmRhdGFzZXQudG9vbHRpcD8udHJpbSgpO1xuICAgIGlmICh0ZXh0KSB0aXAudGV4dENvbnRlbnQgPSB0ZXh0O1xuICAgIC8vIE1hcmsgc3RhdGVcbiAgICB0cmlnZ2VyLnNldEF0dHJpYnV0ZSgnYXJpYS1leHBhbmRlZCcsICd0cnVlJyk7XG4gICAgcG9zaXRpb25UaXAodHJpZ2dlciwgdGlwKTtcbiAgICBzZXRWaXNpYmxlKHRpcCwgdHJ1ZSk7XG4gICAgY3VycmVudCA9IHsgdHJpZ2dlciwgdGlwIH07XG5cbiAgICAvLyBCaW5kIGdsb2JhbCBsaXN0ZW5lcnNcbiAgICBjb25zdCBvZmZLZXkgPSBvbihkb2N1bWVudCwgJ2tleWRvd24nLCAoZSkgPT4ge1xuICAgICAgaWYgKGUua2V5ID09PSAnRXNjYXBlJykgaGlkZSgpO1xuICAgIH0pO1xuICAgIGNvbnN0IG9mZkNsaWNrID0gb24oZG9jdW1lbnQsICdtb3VzZWRvd24nLCAoZSkgPT4ge1xuICAgICAgaWYgKCFjdXJyZW50KSByZXR1cm47XG4gICAgICBjb25zdCB3aXRoaW5UcmlnZ2VyID0gY3VycmVudC50cmlnZ2VyLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGNvbnN0IHdpdGhpblRpcCA9IGN1cnJlbnQudGlwLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGlmICghd2l0aGluVHJpZ2dlciAmJiAhd2l0aGluVGlwKSBoaWRlKCk7XG4gICAgfSk7XG4gICAgY29uc3Qgb2ZmVG91Y2ggPSBvbihkb2N1bWVudCwgJ3RvdWNoc3RhcnQnLCAoZSkgPT4ge1xuICAgICAgaWYgKCFjdXJyZW50KSByZXR1cm47XG4gICAgICBjb25zdCB3aXRoaW5UcmlnZ2VyID0gY3VycmVudC50cmlnZ2VyLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGNvbnN0IHdpdGhpblRpcCA9IGN1cnJlbnQudGlwLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGlmICghd2l0aGluVHJpZ2dlciAmJiAhd2l0aGluVGlwKSBoaWRlKCk7XG4gICAgfSwgeyBwYXNzaXZlOiB0cnVlIH0pO1xuICAgIGNvbnN0IG9mZlNjcm9sbCA9IG9uKHdpbmRvdywgJ3Njcm9sbCcsICgpID0+IGN1cnJlbnQgJiYgcG9zaXRpb25UaXAoY3VycmVudC50cmlnZ2VyLCBjdXJyZW50LnRpcCkpO1xuICAgIGNvbnN0IG9mZlJlc2l6ZSA9IG9uKHdpbmRvdywgJ3Jlc2l6ZScsICgpID0+IGN1cnJlbnQgJiYgcG9zaXRpb25UaXAoY3VycmVudC50cmlnZ2VyLCBjdXJyZW50LnRpcCkpO1xuICAgIHVuYmluZERvY0hhbmRsZXJzID0gKCkgPT4geyBvZmZLZXkoKTsgb2ZmQ2xpY2soKTsgb2ZmVG91Y2goKTsgb2ZmU2Nyb2xsKCk7IG9mZlJlc2l6ZSgpOyB9O1xuICB9XG5cbiAgZnVuY3Rpb24gaGlkZSgpIHtcbiAgICBpZiAoIWN1cnJlbnQpIHJldHVybjtcbiAgICBzZXRWaXNpYmxlKGN1cnJlbnQudGlwLCBmYWxzZSk7XG4gICAgY3VycmVudC50cmlnZ2VyLnNldEF0dHJpYnV0ZSgnYXJpYS1leHBhbmRlZCcsICdmYWxzZScpO1xuICAgIHVuYmluZERvY0hhbmRsZXJzKCk7XG4gICAgY3VycmVudCA9IG51bGw7XG4gIH1cblxuICB0cmlnZ2Vycy5mb3JFYWNoKChlbCkgPT4ge1xuICAgIC8vIEhvdmVyL2ZvY3VzIHNob3dcbiAgICBvbihlbCwgJ21vdXNlZW50ZXInLCAoKSA9PiBzaG93KGVsKSk7XG4gICAgb24oZWwsICdmb2N1cycsICgpID0+IHNob3coZWwpKTtcbiAgICAvLyBNb3VzZWxlYXZlL2JsdXIgaGlkZSAoYnV0IGFsbG93IGNsaWNrIHRvIHRvZ2dsZSlcbiAgICBvbihlbCwgJ21vdXNlbGVhdmUnLCAoKSA9PiB7IGlmIChkb2N1bWVudC5hY3RpdmVFbGVtZW50ICE9PSBlbCkgaGlkZSgpOyB9KTtcbiAgICBvbihlbCwgJ2JsdXInLCAoKSA9PiBoaWRlKCkpO1xuICAgIC8vIFRhcC9jbGljayB0b2dnbGUgZm9yIG1vYmlsZVxuICAgIG9uKGVsLCAnY2xpY2snLCAoZSkgPT4ge1xuICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgaWYgKGN1cnJlbnQgJiYgY3VycmVudC50cmlnZ2VyID09PSBlbCkgaGlkZSgpOyBlbHNlIHNob3coZWwpO1xuICAgIH0pO1xuICAgIG9uKGVsLCAndG91Y2hzdGFydCcsIChlKSA9PiB7XG4gICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICBpZiAoY3VycmVudCAmJiBjdXJyZW50LnRyaWdnZXIgPT09IGVsKSBoaWRlKCk7IGVsc2Ugc2hvdyhlbCk7XG4gICAgfSwgeyBwYXNzaXZlOiBmYWxzZSB9KTtcbiAgfSk7XG59KSgpO1xuIiwiLy8gTGlnaHR3ZWlnaHQgRE9NIHV0aWxpdGllcyAobm8gZGVwZW5kZW5jaWVzKVxuXG4vKipcbiAqIFF1ZXJ5IGEgc2luZ2xlIGVsZW1lbnQgd2l0aGluIGFuIG9wdGlvbmFsIGNvbnRleHQuXG4gKiBAcGFyYW0ge3N0cmluZ30gc2VsZWN0b3JcbiAqIEBwYXJhbSB7UGFyZW50Tm9kZX0gW2N0eD1kb2N1bWVudF1cbiAqIEByZXR1cm5zIHtFbGVtZW50fG51bGx9XG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBxcyhzZWxlY3RvciwgY3R4ID0gZG9jdW1lbnQpIHtcbiAgcmV0dXJuIGN0eC5xdWVyeVNlbGVjdG9yKHNlbGVjdG9yKTtcbn1cblxuLyoqXG4gKiBRdWVyeSBhbGwgZWxlbWVudHMgYXMgYW4gYXJyYXkgd2l0aGluIGFuIG9wdGlvbmFsIGNvbnRleHQuXG4gKiBAcGFyYW0ge3N0cmluZ30gc2VsZWN0b3JcbiAqIEBwYXJhbSB7UGFyZW50Tm9kZX0gW2N0eD1kb2N1bWVudF1cbiAqIEByZXR1cm5zIHtFbGVtZW50W119XG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBxc2Eoc2VsZWN0b3IsIGN0eCA9IGRvY3VtZW50KSB7XG4gIHJldHVybiBBcnJheS5mcm9tKGN0eC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKSk7XG59XG5cbi8qKlxuICogQWRkIGFuIGV2ZW50IGxpc3RlbmVyIHdpdGggYSBzbWFsbCBjb252ZW5pZW5jZSB3cmFwcGVyLlxuICogQHBhcmFtIHtFdmVudFRhcmdldH0gdGFyZ2V0XG4gKiBAcGFyYW0ge3N0cmluZ30gdHlwZVxuICogQHBhcmFtIHtGdW5jdGlvbn0gaGFuZGxlclxuICogQHBhcmFtIHtBZGRFdmVudExpc3RlbmVyT3B0aW9uc3xib29sZWFufSBbb3B0aW9uc11cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIG9uKHRhcmdldCwgdHlwZSwgaGFuZGxlciwgb3B0aW9ucykge1xuICBpZiAoIXRhcmdldCkgcmV0dXJuICgpID0+IHt9O1xuICB0YXJnZXQuYWRkRXZlbnRMaXN0ZW5lcih0eXBlLCBoYW5kbGVyLCBvcHRpb25zKTtcbiAgcmV0dXJuICgpID0+IHRhcmdldC5yZW1vdmVFdmVudExpc3RlbmVyKHR5cGUsIGhhbmRsZXIsIG9wdGlvbnMpO1xufVxuXG4vKipcbiAqIFNtb290aGx5IHNjcm9sbCB0byBhbiBlbGVtZW50IChvciB5IHBvc2l0aW9uKSwgd2l0aCBzYWZlIGZhbGxiYWNrLlxuICogQHBhcmFtIHtFbGVtZW50fG51bWJlcn0gZWxPcllcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHNtb290aFNjcm9sbFRvKGVsT3JZKSB7XG4gIGNvbnN0IHkgPSB0eXBlb2YgZWxPclkgPT09ICdudW1iZXInXG4gICAgPyBlbE9yWVxuICAgIDogKGVsT3JZPy5nZXRCb3VuZGluZ0NsaWVudFJlY3QgPyAod2luZG93LnNjcm9sbFkgKyBlbE9yWS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS50b3ApIDogMCk7XG5cbiAgdHJ5IHtcbiAgICB3aW5kb3cuc2Nyb2xsVG8oeyB0b3A6IHksIGJlaGF2aW9yOiAnc21vb3RoJyB9KTtcbiAgfSBjYXRjaCAoXykge1xuICAgIC8vIEZhbGxiYWNrIGZvciBvbGRlciBicm93c2Vyc1xuICAgIHdpbmRvdy5zY3JvbGxUbygwLCB5KTtcbiAgfVxufVxuXG4iLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiXSwibmFtZXMiOlsicXMiLCJvbiIsImluaXRFbWFpbENhcHR1cmUiLCJzZWN0aW9uIiwiZm9ybSIsInF1ZXJ5U2VsZWN0b3IiLCJlIiwiZW1haWwiLCJ2YWx1ZSIsInByZXZlbnREZWZhdWx0IiwiZm9jdXMiLCJxc2EiLCJzbW9vdGhTY3JvbGxUbyIsImluaXRIZXJvU2Nyb2xsIiwidHJpZ2dlcnMiLCJsZW5ndGgiLCJwcmVmZXJzUmVkdWNlZCIsIndpbmRvdyIsIm1hdGNoTWVkaWEiLCJtYXRjaGVzIiwiZm9yRWFjaCIsInRyaWdnZXIiLCJ0YXJnZXRTZWwiLCJnZXRBdHRyaWJ1dGUiLCJ0YXJnZXRFbCIsImRvY3VtZW50Iiwic2V0QXR0cmlidXRlIiwicHJldmVudFNjcm9sbCIsIl8iLCJpZCIsImNvbmNhdCIsInN0YXJ0c1dpdGgiLCJsb2NhdGlvbiIsImhhc2giLCJzdWJzdHJpbmciLCJzY3JvbGxJbnRvVmlldyIsImluaXRTb3J0SGFuZGxlciIsInNlbGVjdCIsImFsbG93ZWQiLCJTZXQiLCJhZGRFdmVudExpc3RlbmVyIiwiU3RyaW5nIiwidHJpbSIsImhhcyIsInVybCIsIlVSTCIsImhyZWYiLCJzZWFyY2hQYXJhbXMiLCJzZXQiLCJhc3NpZ24iLCJ0b1N0cmluZyIsImluaXRUb29sdGlwcyIsImN1cnJlbnQiLCJ1bmJpbmREb2NIYW5kbGVycyIsImdldFRpcCIsImdldEVsZW1lbnRCeUlkIiwic2V0VmlzaWJsZSIsInRpcCIsInllcyIsImNsYXNzTGlzdCIsInRvZ2dsZSIsInRvZ2dsZUF0dHJpYnV0ZSIsIm1lYXN1cmVUaXAiLCJwcmV2VHJhbnMiLCJzdHlsZSIsInRyYW5zaXRpb24iLCJwcmV2VmlzIiwidmlzaWJpbGl0eSIsInJlbW92ZSIsInJlY3QiLCJnZXRCb3VuZGluZ0NsaWVudFJlY3QiLCJhZGQiLCJjbGFtcCIsIm4iLCJtaW4iLCJtYXgiLCJNYXRoIiwicG9zaXRpb25UaXAiLCJzcGFjaW5nIiwidFJlY3QiLCJ2dyIsImlubmVyV2lkdGgiLCJ2aCIsImlubmVySGVpZ2h0IiwidGlwUmVjdCIsInRvcCIsImhlaWdodCIsInNpZGUiLCJib3R0b20iLCJsZWZ0Iiwid2lkdGgiLCJyb3VuZCIsInNob3ciLCJfdHJpZ2dlciRkYXRhc2V0JHRvb2wiLCJ0ZXh0IiwiZGF0YXNldCIsInRvb2x0aXAiLCJ0ZXh0Q29udGVudCIsIm9mZktleSIsImtleSIsImhpZGUiLCJvZmZDbGljayIsIndpdGhpblRyaWdnZXIiLCJjb250YWlucyIsInRhcmdldCIsIndpdGhpblRpcCIsIm9mZlRvdWNoIiwicGFzc2l2ZSIsIm9mZlNjcm9sbCIsIm9mZlJlc2l6ZSIsImVsIiwiYWN0aXZlRWxlbWVudCIsInNlbGVjdG9yIiwiY3R4IiwiYXJndW1lbnRzIiwidW5kZWZpbmVkIiwiQXJyYXkiLCJmcm9tIiwicXVlcnlTZWxlY3RvckFsbCIsInR5cGUiLCJoYW5kbGVyIiwib3B0aW9ucyIsInJlbW92ZUV2ZW50TGlzdGVuZXIiLCJlbE9yWSIsInkiLCJzY3JvbGxZIiwic2Nyb2xsVG8iLCJiZWhhdmlvciJdLCJzb3VyY2VSb290IjoiIn0=