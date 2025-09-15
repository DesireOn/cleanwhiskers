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
/* harmony import */ var _styles_blocks_hero_css__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./styles/blocks/hero.css */ "./assets/styles/blocks/hero.css");
/* harmony import */ var _styles_components_overlay_focus_css__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./styles/components/overlay-focus.css */ "./assets/styles/components/overlay-focus.css");
/* harmony import */ var _controllers_index_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./controllers/index.js */ "./assets/controllers/index.js");
/* harmony import */ var _styles_blocks_benefits_section_css__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./styles/blocks/benefits-section.css */ "./assets/styles/blocks/benefits-section.css");

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */











// Register controllers (side-effect imports)

// Benefits section styles (list page)


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

/***/ "./assets/controllers/focusCity.js":
/*!*****************************************!*\
  !*** ./assets/controllers/focusCity.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_symbol_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.symbol.js */ "./node_modules/core-js/modules/es.symbol.js");
/* harmony import */ var core_js_modules_es_symbol_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_symbol_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_symbol_description_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.symbol.description.js */ "./node_modules/core-js/modules/es.symbol.description.js");
/* harmony import */ var core_js_modules_es_symbol_description_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_symbol_description_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_function_bind_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.function.bind.js */ "./node_modules/core-js/modules/es.function.bind.js");
/* harmony import */ var core_js_modules_es_function_bind_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_function_bind_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es_object_create_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es.object.create.js */ "./node_modules/core-js/modules/es.object.create.js");
/* harmony import */ var core_js_modules_es_object_create_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_create_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var core_js_modules_es_object_define_property_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.object.define-property.js */ "./node_modules/core-js/modules/es.object.define-property.js");
/* harmony import */ var core_js_modules_es_object_define_property_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_define_property_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_es_object_get_prototype_of_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/es.object.get-prototype-of.js */ "./node_modules/core-js/modules/es.object.get-prototype-of.js");
/* harmony import */ var core_js_modules_es_object_get_prototype_of_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_get_prototype_of_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var core_js_modules_es_object_set_prototype_of_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! core-js/modules/es.object.set-prototype-of.js */ "./node_modules/core-js/modules/es.object.set-prototype-of.js");
/* harmony import */ var core_js_modules_es_object_set_prototype_of_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_set_prototype_of_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var core_js_modules_es_promise_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! core-js/modules/es.promise.js */ "./node_modules/core-js/modules/es.promise.js");
/* harmony import */ var core_js_modules_es_promise_js__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_promise_js__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var core_js_modules_web_timers_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! core-js/modules/web.timers.js */ "./node_modules/core-js/modules/web.timers.js");
/* harmony import */ var core_js_modules_web_timers_js__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_timers_js__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var _helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../helpers/dom.js */ "./assets/helpers/dom.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }










// Focus the city input after scrolling it into view, with a subtle overlay highlight
// - Handles clicks on anchors linking to #search-form or #city
// - Exposes window.CW_focusCityAfterScroll(targetEl) for other controllers (e.g., heroScroll)


function getCityInput() {
  // Prefer primary hero city field, fallback to sticky city input
  return (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.qs)('#city') || (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.qs)('#sticky-city') || (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.qs)('.city-input');
}
function ensureCentered(el) {
  var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  try {
    el.scrollIntoView({
      behavior: prefersReduced ? 'auto' : 'smooth',
      block: 'center',
      inline: 'nearest'
    });
  } catch (_) {
    // ignore if not supported
  }
}
function waitForScrollEnd() {
  var maxMs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 800;
  return new Promise(function (resolve) {
    var lastY = window.scrollY;
    var elapsed = 0;
    var step = 50;
    var timer = setInterval(function () {
      var y = window.scrollY;
      elapsed += step;
      if (Math.abs(y - lastY) < 2 || elapsed >= maxMs) {
        clearInterval(timer);
        resolve();
      }
      lastY = y;
    }, step);

    // Use the scrollend event when available for faster reaction
    var off = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.on)(window, 'scrollend', function () {
      clearInterval(timer);
      off();
      resolve();
    }, {
      once: true
    });
  });
}
function createOverlay() {
  var overlay = document.createElement('div');
  overlay.className = 'focus-overlay';
  document.body.appendChild(overlay);
  requestAnimationFrame(function () {
    overlay.classList.add('is-active');
  });
  return overlay;
}
function elevate(el) {
  el.classList.add('focus-elevated');
  el.classList.add('focus-highlight');
}
function deElevate(el) {
  el.classList.remove('focus-elevated');
  el.classList.remove('focus-highlight');
}
function getLiveRegion() {
  var region = document.getElementById('sr-announcer');
  if (!region) {
    region = document.createElement('div');
    region.id = 'sr-announcer';
    region.setAttribute('role', 'status');
    region.setAttribute('aria-live', 'polite');
    region.className = 'visually-hidden';
    document.body.appendChild(region);
  }
  return region;
}
function announce(msg) {
  var region = getLiveRegion();
  region.textContent = '';
  setTimeout(function () {
    region.textContent = msg;
  }, 30);
}

// Removed visual coachmark to avoid overlapping suggestions list

var isActive = false;
function centerAndFocusCity(_x) {
  return _centerAndFocusCity.apply(this, arguments);
} // Public hook for other controllers
function _centerAndFocusCity() {
  _centerAndFocusCity = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(targetEl) {
    var input, overlay, cleanup, offEsc, offBlur, offClick, offInput;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          if (!isActive) {
            _context.n = 1;
            break;
          }
          return _context.a(2);
        case 1:
          input = getCityInput();
          if (input) {
            _context.n = 2;
            break;
          }
          return _context.a(2);
        case 2:
          // If targetEl provided, center that; otherwise center the input itself
          ensureCentered(targetEl || input);
          _context.n = 3;
          return waitForScrollEnd();
        case 3:
          overlay = createOverlay();
          elevate(input);
          isActive = true;
          try {
            input.focus({
              preventScroll: true
            });
          } catch (_) {
            input.focus();
          }
          cleanup = function cleanup() {
            deElevate(input);
            overlay.classList.remove('is-active');
            // remove after transition
            setTimeout(function () {
              return overlay.remove();
            }, 200);
            offEsc();
            offBlur();
            offClick();
            offInput();
            isActive = false;
          };
          offEsc = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.on)(window, 'keydown', function (e) {
            if (e.key === 'Escape') cleanup();
          });
          offBlur = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.on)(input, 'blur', cleanup, {
            once: true
          });
          offClick = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.on)(overlay, 'click', cleanup); // Assist and dismiss on input
          offInput = (0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.on)(input, 'input', function () {
            cleanup();
          }); // Announce for screen readers only
          announce('Focus moved to city input. Start typing to see suggestions.');
        case 4:
          return _context.a(2);
      }
    }, _callee);
  }));
  return _centerAndFocusCity.apply(this, arguments);
}
window.CW_focusCityAfterScroll = function (targetEl) {
  return centerAndFocusCity(targetEl);
};

// Intercept anchor clicks to #search-form or #city to center+focus elegantly
(0,_helpers_dom_js__WEBPACK_IMPORTED_MODULE_10__.on)(document, 'click', function (e) {
  var a = e.target.closest('a[href]');
  if (!a) return;
  var href = a.getAttribute('href');
  if (href === '#search-form' || href === '#city') {
    var target = document.querySelector(href);
    e.preventDefault();
    centerAndFocusCity(target);
  }
});

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
        // If we're scrolling to the search form/city area, center and focus it nicely
        try {
          var _targetEl$querySelect;
          var hasCity = targetEl.matches('#search-form, #city') || !!((_targetEl$querySelect = targetEl.querySelector) !== null && _targetEl$querySelect !== void 0 && _targetEl$querySelect.call(targetEl, '.city-input'));
          if (hasCity && typeof window.CW_focusCityAfterScroll === 'function') {
            window.CW_focusCityAfterScroll(targetEl);
          }
        } catch (_) {}
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
/* harmony import */ var _focusCity_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./focusCity.js */ "./assets/controllers/focusCity.js");
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

/***/ "./assets/styles/blocks/benefits-section.css":
/*!***************************************************!*\
  !*** ./assets/styles/blocks/benefits-section.css ***!
  \***************************************************/
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

/***/ "./assets/styles/blocks/hero.css":
/*!***************************************!*\
  !*** ./assets/styles/blocks/hero.css ***!
  \***************************************/
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

/***/ "./assets/styles/components/overlay-focus.css":
/*!****************************************************!*\
  !*** ./assets/styles/components/overlay-focus.css ***!
  \****************************************************/
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
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_modules_es_array_for-each_js-node_modules_core-js_modules_es_arr-ce185f"], () => (__webpack_exec__("./assets/app.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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Ozs7O0FDQUE7Ozs7Ozs7Ozs7Ozs7QUNBQTs7Ozs7Ozs7Ozs7OztBQ0FBOzs7Ozs7Ozs7Ozs7O0FDQUE7Ozs7Ozs7Ozs7Ozs7QUNBQTs7Ozs7Ozs7Ozs7OztBQ0FBOzs7Ozs7Ozs7Ozs7O0FDQUE7Ozs7Ozs7Ozs7Ozs7QUNBQTs7Ozs7Ozs7Ozs7OztBQ0FBOzs7Ozs7Ozs7Ozs7O0FDQUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2FwcC5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2Jvb3RzdHJhcC5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2NvbnRyb2xsZXJzL2VtYWlsQ2FwdHVyZS5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2NvbnRyb2xsZXJzL2ZvY3VzQ2l0eS5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2NvbnRyb2xsZXJzL2hlcm9TY3JvbGwuanMiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9jb250cm9sbGVycy9pbmRleC5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2NvbnRyb2xsZXJzL3NvcnRIYW5kbGVyLmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvY29udHJvbGxlcnMvdG9vbHRpcC5qcyIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL2hlbHBlcnMvZG9tLmpzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2FwcC5jc3M/M2ZiYSIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9ibG9ja3MvYmVuZWZpdHMtc2VjdGlvbi5jc3M/NDFjOSIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9ibG9ja3MvY2FyZC5jc3M/Y2MyNSIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9ibG9ja3MvZ3Jvb21lci1jYXJkLmNzcz9lMTJlIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2Jsb2Nrcy9oZXJvLmNzcz84MWYzIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2Jsb2Nrcy90cnVzdC1ib3guY3NzPzM4NTMiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9zdHlsZXMvY29tcG9uZW50cy9iYWRnZS5jc3M/OTg4YSIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9jb21wb25lbnRzL2NpdHktY2FyZC5jc3M/M2ZlOSIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy9jb21wb25lbnRzL292ZXJsYXktZm9jdXMuY3NzPzU4Y2MiLCJ3ZWJwYWNrOi8vY2xlYW53aGlza2Vycy8uL2Fzc2V0cy9zdHlsZXMvY29tcG9uZW50cy9zcGlubmVyLmNzcz8wMzJhIiwid2VicGFjazovL2NsZWFud2hpc2tlcnMvLi9hc3NldHMvc3R5bGVzL2NvbXBvbmVudHMvc3RpY2t5LWN0YS5jc3M/NmJlYiIsIndlYnBhY2s6Ly9jbGVhbndoaXNrZXJzLy4vYXNzZXRzL3N0eWxlcy91dGlsaXRpZXMuY3NzPzUyZWEiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0ICcuL2Jvb3RzdHJhcC5qcyc7XG4vKlxuICogV2VsY29tZSB0byB5b3VyIGFwcCdzIG1haW4gSmF2YVNjcmlwdCBmaWxlIVxuICpcbiAqIFRoaXMgZmlsZSB3aWxsIGJlIGluY2x1ZGVkIG9udG8gdGhlIHBhZ2UgdmlhIHRoZSBpbXBvcnRtYXAoKSBUd2lnIGZ1bmN0aW9uLFxuICogd2hpY2ggc2hvdWxkIGFscmVhZHkgYmUgaW4geW91ciBiYXNlLmh0bWwudHdpZy5cbiAqL1xuaW1wb3J0ICcuL3N0eWxlcy9hcHAuY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvdXRpbGl0aWVzLmNzcyc7XG5pbXBvcnQgJy4vc3R5bGVzL2Jsb2Nrcy9ncm9vbWVyLWNhcmQuY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvY29tcG9uZW50cy9iYWRnZS5jc3MnO1xuaW1wb3J0ICcuL3N0eWxlcy9jb21wb25lbnRzL2NpdHktY2FyZC5jc3MnO1xuaW1wb3J0ICcuL3N0eWxlcy9jb21wb25lbnRzL3NwaW5uZXIuY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvY29tcG9uZW50cy9zdGlja3ktY3RhLmNzcyc7XG5pbXBvcnQgJy4vc3R5bGVzL2Jsb2Nrcy90cnVzdC1ib3guY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvYmxvY2tzL2NhcmQuY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvYmxvY2tzL2hlcm8uY3NzJztcbmltcG9ydCAnLi9zdHlsZXMvY29tcG9uZW50cy9vdmVybGF5LWZvY3VzLmNzcyc7XG4vLyBSZWdpc3RlciBjb250cm9sbGVycyAoc2lkZS1lZmZlY3QgaW1wb3J0cylcbmltcG9ydCAnLi9jb250cm9sbGVycy9pbmRleC5qcyc7XG4vLyBCZW5lZml0cyBzZWN0aW9uIHN0eWxlcyAobGlzdCBwYWdlKVxuaW1wb3J0ICcuL3N0eWxlcy9ibG9ja3MvYmVuZWZpdHMtc2VjdGlvbi5jc3MnO1xuIiwiLy8gaW1wb3J0IHsgc3RhcnRTdGltdWx1c0FwcCB9IGZyb20gJ0BzeW1mb255L3N0aW11bHVzLWJ1bmRsZSc7XG5cbi8vIGNvbnN0IGFwcCA9IHN0YXJ0U3RpbXVsdXNBcHAoKTtcbi8vIC8vIHJlZ2lzdGVyIGFueSBjdXN0b20sIDNyZCBwYXJ0eSBjb250cm9sbGVycyBoZXJlXG4vLyAvLyBhcHAucmVnaXN0ZXIoJ3NvbWVfY29udHJvbGxlcl9uYW1lJywgU29tZUltcG9ydGVkQ29udHJvbGxlcik7XG4iLCIvLyBQbGFjZWhvbGRlcjogc2FmZWx5IHdpcmUgdXAgYW4gZW1haWwgY2FwdHVyZSBmb3JtIGlmIHByZXNlbnRcbmltcG9ydCB7IHFzLCBvbiB9IGZyb20gJy4uL2hlbHBlcnMvZG9tLmpzJztcblxuKGZ1bmN0aW9uIGluaXRFbWFpbENhcHR1cmUoKSB7XG4gIGNvbnN0IHNlY3Rpb24gPSBxcygnI2VtYWlsLWNhcHR1cmUnKTtcbiAgaWYgKCFzZWN0aW9uKSByZXR1cm47XG4gIGNvbnN0IGZvcm0gPSBzZWN0aW9uLnF1ZXJ5U2VsZWN0b3IoJ2Zvcm0nKTtcbiAgaWYgKCFmb3JtKSByZXR1cm47XG4gIG9uKGZvcm0sICdzdWJtaXQnLCAoZSkgPT4ge1xuICAgIC8vIEFsbG93IGRlZmF1bHQgc3VibWl0OyBlbnN1cmUgYmFzaWMgdmFsaWRhdGlvbiB3aXRob3V0IGNvbnNvbGUgZXJyb3JzXG4gICAgY29uc3QgZW1haWwgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoJ2lucHV0W3R5cGU9XCJlbWFpbFwiXScpO1xuICAgIGlmIChlbWFpbCAmJiAhZW1haWwudmFsdWUpIHtcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIGVtYWlsLmZvY3VzKCk7XG4gICAgfVxuICB9KTtcbn0pKCk7XG5cbiIsIi8vIEZvY3VzIHRoZSBjaXR5IGlucHV0IGFmdGVyIHNjcm9sbGluZyBpdCBpbnRvIHZpZXcsIHdpdGggYSBzdWJ0bGUgb3ZlcmxheSBoaWdobGlnaHRcbi8vIC0gSGFuZGxlcyBjbGlja3Mgb24gYW5jaG9ycyBsaW5raW5nIHRvICNzZWFyY2gtZm9ybSBvciAjY2l0eVxuLy8gLSBFeHBvc2VzIHdpbmRvdy5DV19mb2N1c0NpdHlBZnRlclNjcm9sbCh0YXJnZXRFbCkgZm9yIG90aGVyIGNvbnRyb2xsZXJzIChlLmcuLCBoZXJvU2Nyb2xsKVxuXG5pbXBvcnQgeyBxcywgb24gfSBmcm9tICcuLi9oZWxwZXJzL2RvbS5qcyc7XG5cbmZ1bmN0aW9uIGdldENpdHlJbnB1dCgpIHtcbiAgLy8gUHJlZmVyIHByaW1hcnkgaGVybyBjaXR5IGZpZWxkLCBmYWxsYmFjayB0byBzdGlja3kgY2l0eSBpbnB1dFxuICByZXR1cm4gKFxuICAgIHFzKCcjY2l0eScpIHx8XG4gICAgcXMoJyNzdGlja3ktY2l0eScpIHx8XG4gICAgcXMoJy5jaXR5LWlucHV0JylcbiAgKTtcbn1cblxuZnVuY3Rpb24gZW5zdXJlQ2VudGVyZWQoZWwpIHtcbiAgY29uc3QgcHJlZmVyc1JlZHVjZWQgPSB3aW5kb3cubWF0Y2hNZWRpYSAmJiB3aW5kb3cubWF0Y2hNZWRpYSgnKHByZWZlcnMtcmVkdWNlZC1tb3Rpb246IHJlZHVjZSknKS5tYXRjaGVzO1xuICB0cnkge1xuICAgIGVsLnNjcm9sbEludG9WaWV3KHsgYmVoYXZpb3I6IHByZWZlcnNSZWR1Y2VkID8gJ2F1dG8nIDogJ3Ntb290aCcsIGJsb2NrOiAnY2VudGVyJywgaW5saW5lOiAnbmVhcmVzdCcgfSk7XG4gIH0gY2F0Y2ggKF8pIHtcbiAgICAvLyBpZ25vcmUgaWYgbm90IHN1cHBvcnRlZFxuICB9XG59XG5cbmZ1bmN0aW9uIHdhaXRGb3JTY3JvbGxFbmQobWF4TXMgPSA4MDApIHtcbiAgcmV0dXJuIG5ldyBQcm9taXNlKChyZXNvbHZlKSA9PiB7XG4gICAgbGV0IGxhc3RZID0gd2luZG93LnNjcm9sbFk7XG4gICAgbGV0IGVsYXBzZWQgPSAwO1xuICAgIGNvbnN0IHN0ZXAgPSA1MDtcblxuICAgIGNvbnN0IHRpbWVyID0gc2V0SW50ZXJ2YWwoKCkgPT4ge1xuICAgICAgY29uc3QgeSA9IHdpbmRvdy5zY3JvbGxZO1xuICAgICAgZWxhcHNlZCArPSBzdGVwO1xuICAgICAgaWYgKE1hdGguYWJzKHkgLSBsYXN0WSkgPCAyIHx8IGVsYXBzZWQgPj0gbWF4TXMpIHtcbiAgICAgICAgY2xlYXJJbnRlcnZhbCh0aW1lcik7XG4gICAgICAgIHJlc29sdmUoKTtcbiAgICAgIH1cbiAgICAgIGxhc3RZID0geTtcbiAgICB9LCBzdGVwKTtcblxuICAgIC8vIFVzZSB0aGUgc2Nyb2xsZW5kIGV2ZW50IHdoZW4gYXZhaWxhYmxlIGZvciBmYXN0ZXIgcmVhY3Rpb25cbiAgICBjb25zdCBvZmYgPSBvbih3aW5kb3csICdzY3JvbGxlbmQnLCAoKSA9PiB7XG4gICAgICBjbGVhckludGVydmFsKHRpbWVyKTtcbiAgICAgIG9mZigpO1xuICAgICAgcmVzb2x2ZSgpO1xuICAgIH0sIHsgb25jZTogdHJ1ZSB9KTtcbiAgfSk7XG59XG5cbmZ1bmN0aW9uIGNyZWF0ZU92ZXJsYXkoKSB7XG4gIGxldCBvdmVybGF5ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gIG92ZXJsYXkuY2xhc3NOYW1lID0gJ2ZvY3VzLW92ZXJsYXknO1xuICBkb2N1bWVudC5ib2R5LmFwcGVuZENoaWxkKG92ZXJsYXkpO1xuXG4gIHJlcXVlc3RBbmltYXRpb25GcmFtZSgoKSA9PiB7XG4gICAgb3ZlcmxheS5jbGFzc0xpc3QuYWRkKCdpcy1hY3RpdmUnKTtcbiAgfSk7XG5cbiAgcmV0dXJuIG92ZXJsYXk7XG59XG5cbmZ1bmN0aW9uIGVsZXZhdGUoZWwpIHtcbiAgZWwuY2xhc3NMaXN0LmFkZCgnZm9jdXMtZWxldmF0ZWQnKTtcbiAgZWwuY2xhc3NMaXN0LmFkZCgnZm9jdXMtaGlnaGxpZ2h0Jyk7XG59XG5cbmZ1bmN0aW9uIGRlRWxldmF0ZShlbCkge1xuICBlbC5jbGFzc0xpc3QucmVtb3ZlKCdmb2N1cy1lbGV2YXRlZCcpO1xuICBlbC5jbGFzc0xpc3QucmVtb3ZlKCdmb2N1cy1oaWdobGlnaHQnKTtcbn1cblxuZnVuY3Rpb24gZ2V0TGl2ZVJlZ2lvbigpIHtcbiAgbGV0IHJlZ2lvbiA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzci1hbm5vdW5jZXInKTtcbiAgaWYgKCFyZWdpb24pIHtcbiAgICByZWdpb24gPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdkaXYnKTtcbiAgICByZWdpb24uaWQgPSAnc3ItYW5ub3VuY2VyJztcbiAgICByZWdpb24uc2V0QXR0cmlidXRlKCdyb2xlJywgJ3N0YXR1cycpO1xuICAgIHJlZ2lvbi5zZXRBdHRyaWJ1dGUoJ2FyaWEtbGl2ZScsICdwb2xpdGUnKTtcbiAgICByZWdpb24uY2xhc3NOYW1lID0gJ3Zpc3VhbGx5LWhpZGRlbic7XG4gICAgZG9jdW1lbnQuYm9keS5hcHBlbmRDaGlsZChyZWdpb24pO1xuICB9XG4gIHJldHVybiByZWdpb247XG59XG5cbmZ1bmN0aW9uIGFubm91bmNlKG1zZykge1xuICBjb25zdCByZWdpb24gPSBnZXRMaXZlUmVnaW9uKCk7XG4gIHJlZ2lvbi50ZXh0Q29udGVudCA9ICcnO1xuICBzZXRUaW1lb3V0KCgpID0+IHsgcmVnaW9uLnRleHRDb250ZW50ID0gbXNnOyB9LCAzMCk7XG59XG5cbi8vIFJlbW92ZWQgdmlzdWFsIGNvYWNobWFyayB0byBhdm9pZCBvdmVybGFwcGluZyBzdWdnZXN0aW9ucyBsaXN0XG5cbmxldCBpc0FjdGl2ZSA9IGZhbHNlO1xuXG5hc3luYyBmdW5jdGlvbiBjZW50ZXJBbmRGb2N1c0NpdHkodGFyZ2V0RWwpIHtcbiAgaWYgKGlzQWN0aXZlKSByZXR1cm47XG4gIGNvbnN0IGlucHV0ID0gZ2V0Q2l0eUlucHV0KCk7XG4gIGlmICghaW5wdXQpIHJldHVybjtcblxuICAvLyBJZiB0YXJnZXRFbCBwcm92aWRlZCwgY2VudGVyIHRoYXQ7IG90aGVyd2lzZSBjZW50ZXIgdGhlIGlucHV0IGl0c2VsZlxuICBlbnN1cmVDZW50ZXJlZCh0YXJnZXRFbCB8fCBpbnB1dCk7XG4gIGF3YWl0IHdhaXRGb3JTY3JvbGxFbmQoKTtcblxuICBjb25zdCBvdmVybGF5ID0gY3JlYXRlT3ZlcmxheSgpO1xuICBlbGV2YXRlKGlucHV0KTtcbiAgaXNBY3RpdmUgPSB0cnVlO1xuXG4gIHRyeSB7XG4gICAgaW5wdXQuZm9jdXMoeyBwcmV2ZW50U2Nyb2xsOiB0cnVlIH0pO1xuICB9IGNhdGNoIChfKSB7XG4gICAgaW5wdXQuZm9jdXMoKTtcbiAgfVxuXG4gIGNvbnN0IGNsZWFudXAgPSAoKSA9PiB7XG4gICAgZGVFbGV2YXRlKGlucHV0KTtcbiAgICBvdmVybGF5LmNsYXNzTGlzdC5yZW1vdmUoJ2lzLWFjdGl2ZScpO1xuICAgIC8vIHJlbW92ZSBhZnRlciB0cmFuc2l0aW9uXG4gICAgc2V0VGltZW91dCgoKSA9PiBvdmVybGF5LnJlbW92ZSgpLCAyMDApO1xuICAgIG9mZkVzYygpO1xuICAgIG9mZkJsdXIoKTtcbiAgICBvZmZDbGljaygpO1xuICAgIG9mZklucHV0KCk7XG4gICAgaXNBY3RpdmUgPSBmYWxzZTtcbiAgfTtcblxuICBjb25zdCBvZmZFc2MgPSBvbih3aW5kb3csICdrZXlkb3duJywgKGUpID0+IHtcbiAgICBpZiAoZS5rZXkgPT09ICdFc2NhcGUnKSBjbGVhbnVwKCk7XG4gIH0pO1xuXG4gIGNvbnN0IG9mZkJsdXIgPSBvbihpbnB1dCwgJ2JsdXInLCBjbGVhbnVwLCB7IG9uY2U6IHRydWUgfSk7XG4gIGNvbnN0IG9mZkNsaWNrID0gb24ob3ZlcmxheSwgJ2NsaWNrJywgY2xlYW51cCk7XG5cbiAgLy8gQXNzaXN0IGFuZCBkaXNtaXNzIG9uIGlucHV0XG4gIGNvbnN0IG9mZklucHV0ID0gb24oaW5wdXQsICdpbnB1dCcsICgpID0+IHtcbiAgICBjbGVhbnVwKCk7XG4gIH0pO1xuXG4gIC8vIEFubm91bmNlIGZvciBzY3JlZW4gcmVhZGVycyBvbmx5XG4gIGFubm91bmNlKCdGb2N1cyBtb3ZlZCB0byBjaXR5IGlucHV0LiBTdGFydCB0eXBpbmcgdG8gc2VlIHN1Z2dlc3Rpb25zLicpO1xufVxuXG4vLyBQdWJsaWMgaG9vayBmb3Igb3RoZXIgY29udHJvbGxlcnNcbndpbmRvdy5DV19mb2N1c0NpdHlBZnRlclNjcm9sbCA9ICh0YXJnZXRFbCkgPT4gY2VudGVyQW5kRm9jdXNDaXR5KHRhcmdldEVsKTtcblxuLy8gSW50ZXJjZXB0IGFuY2hvciBjbGlja3MgdG8gI3NlYXJjaC1mb3JtIG9yICNjaXR5IHRvIGNlbnRlcitmb2N1cyBlbGVnYW50bHlcbm9uKGRvY3VtZW50LCAnY2xpY2snLCAoZSkgPT4ge1xuICBjb25zdCBhID0gZS50YXJnZXQuY2xvc2VzdCgnYVtocmVmXScpO1xuICBpZiAoIWEpIHJldHVybjtcbiAgY29uc3QgaHJlZiA9IGEuZ2V0QXR0cmlidXRlKCdocmVmJyk7XG4gIGlmIChocmVmID09PSAnI3NlYXJjaC1mb3JtJyB8fCBocmVmID09PSAnI2NpdHknKSB7XG4gICAgY29uc3QgdGFyZ2V0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihocmVmKTtcbiAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgY2VudGVyQW5kRm9jdXNDaXR5KHRhcmdldCk7XG4gIH1cbn0pO1xuIiwiLy8gU21vb3RoLXNjcm9sbCBoYW5kbGVyIGZvciBlbGVtZW50cyB3aXRoIFtkYXRhLXNjcm9sbC10YXJnZXRdXG5pbXBvcnQgeyBxc2EsIG9uLCBzbW9vdGhTY3JvbGxUbyB9IGZyb20gJy4uL2hlbHBlcnMvZG9tLmpzJztcblxuKGZ1bmN0aW9uIGluaXRIZXJvU2Nyb2xsKCkge1xuICBjb25zdCB0cmlnZ2VycyA9IHFzYSgnW2RhdGEtc2Nyb2xsLXRhcmdldF0nKTtcbiAgaWYgKCF0cmlnZ2Vycy5sZW5ndGgpIHJldHVybjtcblxuICBjb25zdCBwcmVmZXJzUmVkdWNlZCA9IHdpbmRvdy5tYXRjaE1lZGlhICYmIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1yZWR1Y2VkLW1vdGlvbjogcmVkdWNlKScpLm1hdGNoZXM7XG5cbiAgdHJpZ2dlcnMuZm9yRWFjaCgodHJpZ2dlcikgPT4ge1xuICAgIG9uKHRyaWdnZXIsICdjbGljaycsIChlKSA9PiB7XG4gICAgICBjb25zdCB0YXJnZXRTZWwgPSB0cmlnZ2VyLmdldEF0dHJpYnV0ZSgnZGF0YS1zY3JvbGwtdGFyZ2V0Jyk7XG4gICAgICBpZiAoIXRhcmdldFNlbCkgcmV0dXJuO1xuICAgICAgY29uc3QgdGFyZ2V0RWwgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKHRhcmdldFNlbCk7XG4gICAgICBpZiAoIXRhcmdldEVsKSByZXR1cm47XG5cbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgaWYgKHByZWZlcnNSZWR1Y2VkKSB7XG4gICAgICAgIC8vIFJlc3BlY3QgcmVkdWNlZCBtb3Rpb246IGp1bXAtbGluayBmYWxsYmFja1xuICAgICAgICB0cnkge1xuICAgICAgICAgIHRhcmdldEVsLnNldEF0dHJpYnV0ZSgndGFiaW5kZXgnLCAnLTEnKTtcbiAgICAgICAgICB0YXJnZXRFbC5mb2N1cyh7IHByZXZlbnRTY3JvbGw6IHRydWUgfSk7XG4gICAgICAgIH0gY2F0Y2ggKF8pIHt9XG4gICAgICAgIGNvbnN0IGlkID0gdGFyZ2V0RWwuaWQgPyBgIyR7dGFyZ2V0RWwuaWR9YCA6IHRhcmdldFNlbDtcbiAgICAgICAgaWYgKGlkICYmIGlkLnN0YXJ0c1dpdGgoJyMnKSkge1xuICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5oYXNoID0gaWQuc3Vic3RyaW5nKDEpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIHRhcmdldEVsLnNjcm9sbEludG9WaWV3KCk7XG4gICAgICAgIH1cbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHNtb290aFNjcm9sbFRvKHRhcmdldEVsKTtcbiAgICAgICAgLy8gSWYgd2UncmUgc2Nyb2xsaW5nIHRvIHRoZSBzZWFyY2ggZm9ybS9jaXR5IGFyZWEsIGNlbnRlciBhbmQgZm9jdXMgaXQgbmljZWx5XG4gICAgICAgIHRyeSB7XG4gICAgICAgICAgY29uc3QgaGFzQ2l0eSA9IHRhcmdldEVsLm1hdGNoZXMoJyNzZWFyY2gtZm9ybSwgI2NpdHknKSB8fCAhIXRhcmdldEVsLnF1ZXJ5U2VsZWN0b3I/LignLmNpdHktaW5wdXQnKTtcbiAgICAgICAgICBpZiAoaGFzQ2l0eSAmJiB0eXBlb2Ygd2luZG93LkNXX2ZvY3VzQ2l0eUFmdGVyU2Nyb2xsID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICB3aW5kb3cuQ1dfZm9jdXNDaXR5QWZ0ZXJTY3JvbGwodGFyZ2V0RWwpO1xuICAgICAgICAgIH1cbiAgICAgICAgfSBjYXRjaCAoXykge31cbiAgICAgIH1cbiAgICB9KTtcbiAgfSk7XG59KSgpO1xuIiwiLy8gQ2VudHJhbCByZWdpc3RyeSBmb3IgbGlnaHR3ZWlnaHQgY29udHJvbGxlcnMgKHZhbmlsbGEgbW9kdWxlcylcbi8vIEVhY2ggaW1wb3J0ZWQgbW9kdWxlIGNhbiBzZWxmLWluaXRpYWxpemUgb3IgZXhwb3J0IGhvb2tzIGFzIG5lZWRlZC5cblxuaW1wb3J0ICcuL2hlcm9TY3JvbGwuanMnO1xuaW1wb3J0ICcuL3NvcnRIYW5kbGVyLmpzJztcbmltcG9ydCAnLi90b29sdGlwLmpzJztcbmltcG9ydCAnLi9lbWFpbENhcHR1cmUuanMnO1xuaW1wb3J0ICcuL2ZvY3VzQ2l0eS5qcyc7XG4iLCIvLyBTb3J0IGRyb3Bkb3duOiB1cGRhdGVzIFVSTCA/c29ydD0gYW5kIHJlbG9hZHMsIHByZXNlcnZpbmcgb3RoZXIgcGFyYW1zLlxuKGZ1bmN0aW9uIGluaXRTb3J0SGFuZGxlcigpIHtcbiAgY29uc3Qgc2VsZWN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignI3NvcnQtY29udHJvbCBzZWxlY3Qjc29ydCcpO1xuICBpZiAoIXNlbGVjdCkgcmV0dXJuO1xuXG4gIGNvbnN0IGFsbG93ZWQgPSBuZXcgU2V0KFsncmVjb21tZW5kZWQnLCAncHJpY2VfYXNjJywgJ3JhdGluZ19kZXNjJ10pO1xuXG4gIHNlbGVjdC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCAoKSA9PiB7XG4gICAgY29uc3QgdmFsdWUgPSBTdHJpbmcoc2VsZWN0LnZhbHVlIHx8ICcnKS50cmltKCk7XG4gICAgaWYgKCFhbGxvd2VkLmhhcyh2YWx1ZSkpIHJldHVybjsgLy8gaWdub3JlIHVuZXhwZWN0ZWQgdmFsdWVzXG5cbiAgICBjb25zdCB1cmwgPSBuZXcgVVJMKHdpbmRvdy5sb2NhdGlvbi5ocmVmKTtcbiAgICB1cmwuc2VhcmNoUGFyYW1zLnNldCgnc29ydCcsIHZhbHVlKTtcblxuICAgIC8vIFJlbG9hZCB3aXRoIHVwZGF0ZWQgcGFyYW1zLCBwcmVzZXJ2aW5nIHBhdGggYW5kIG90aGVyIHBhcmFtc1xuICAgIHdpbmRvdy5sb2NhdGlvbi5hc3NpZ24odXJsLnRvU3RyaW5nKCkpO1xuICB9KTtcbn0pKCk7XG4iLCIvLyBBY2Nlc3NpYmxlIHRvb2x0aXAgY29udHJvbGxlciAoaW5saW5lIHRvb2x0aXBzLCBubyBmb2N1cyB0cmFwKVxuaW1wb3J0IHsgcXNhLCBvbiB9IGZyb20gJy4uL2hlbHBlcnMvZG9tLmpzJztcblxuKGZ1bmN0aW9uIGluaXRUb29sdGlwcygpIHtcbiAgY29uc3QgdHJpZ2dlcnMgPSBxc2EoJ1tkYXRhLXRvb2x0aXBdW2FyaWEtZGVzY3JpYmVkYnldJyk7XG4gIGlmICghdHJpZ2dlcnMubGVuZ3RoKSByZXR1cm47XG5cbiAgbGV0IGN1cnJlbnQgPSBudWxsOyAvLyB7IHRyaWdnZXIsIHRpcCB9XG4gIGxldCB1bmJpbmREb2NIYW5kbGVycyA9ICgpID0+IHt9O1xuXG4gIGZ1bmN0aW9uIGdldFRpcCh0cmlnZ2VyKSB7XG4gICAgY29uc3QgaWQgPSB0cmlnZ2VyLmdldEF0dHJpYnV0ZSgnYXJpYS1kZXNjcmliZWRieScpO1xuICAgIGlmICghaWQpIHJldHVybiBudWxsO1xuICAgIHJldHVybiBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG4gIH1cblxuICBmdW5jdGlvbiBzZXRWaXNpYmxlKHRpcCwgeWVzKSB7XG4gICAgaWYgKCF0aXApIHJldHVybjtcbiAgICB0aXAuY2xhc3NMaXN0LnRvZ2dsZSgndG9vbHRpcC0taGlkZGVuJywgIXllcyk7XG4gICAgdGlwLnRvZ2dsZUF0dHJpYnV0ZSgnZGF0YS1vcGVuJywgISF5ZXMpO1xuICB9XG5cbiAgZnVuY3Rpb24gbWVhc3VyZVRpcCh0aXApIHtcbiAgICAvLyBUZW1wb3JhcmlseSBlbnN1cmUgbWVhc3VyYWJsZSB3aXRob3V0IGZsYXNoaW5nXG4gICAgY29uc3QgcHJldlRyYW5zID0gdGlwLnN0eWxlLnRyYW5zaXRpb247XG4gICAgY29uc3QgcHJldlZpcyA9IHRpcC5zdHlsZS52aXNpYmlsaXR5O1xuICAgIHRpcC5zdHlsZS50cmFuc2l0aW9uID0gJ25vbmUnO1xuICAgIHRpcC5zdHlsZS52aXNpYmlsaXR5ID0gJ2hpZGRlbic7XG4gICAgdGlwLmNsYXNzTGlzdC5yZW1vdmUoJ3Rvb2x0aXAtLWhpZGRlbicpO1xuICAgIGNvbnN0IHJlY3QgPSB0aXAuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XG4gICAgdGlwLmNsYXNzTGlzdC5hZGQoJ3Rvb2x0aXAtLWhpZGRlbicpO1xuICAgIHRpcC5zdHlsZS52aXNpYmlsaXR5ID0gcHJldlZpcztcbiAgICB0aXAuc3R5bGUudHJhbnNpdGlvbiA9IHByZXZUcmFucztcbiAgICByZXR1cm4gcmVjdDtcbiAgfVxuXG4gIGZ1bmN0aW9uIGNsYW1wKG4sIG1pbiwgbWF4KSB7IHJldHVybiBNYXRoLm1heChtaW4sIE1hdGgubWluKG1heCwgbikpOyB9XG5cbiAgZnVuY3Rpb24gcG9zaXRpb25UaXAodHJpZ2dlciwgdGlwKSB7XG4gICAgY29uc3Qgc3BhY2luZyA9IDg7XG4gICAgY29uc3QgdFJlY3QgPSB0cmlnZ2VyLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgIGNvbnN0IHZ3ID0gd2luZG93LmlubmVyV2lkdGg7XG4gICAgY29uc3QgdmggPSB3aW5kb3cuaW5uZXJIZWlnaHQ7XG5cbiAgICAvLyBFbnN1cmUgY29udGVudCBpcyB1cCB0byBkYXRlIGJlZm9yZSBtZWFzdXJpbmdcbiAgICBjb25zdCB0aXBSZWN0ID0gbWVhc3VyZVRpcCh0aXApO1xuXG4gICAgLy8gUHJlZmVyIGFib3ZlOyBpZiBubyByb29tLCBwbGFjZSBiZWxvd1xuICAgIGxldCB0b3AgPSB0UmVjdC50b3AgLSB0aXBSZWN0LmhlaWdodCAtIHNwYWNpbmc7XG4gICAgbGV0IHNpZGUgPSAndG9wJztcbiAgICBpZiAodG9wIDwgOCkge1xuICAgICAgdG9wID0gdFJlY3QuYm90dG9tICsgc3BhY2luZztcbiAgICAgIHNpZGUgPSAnYm90dG9tJztcbiAgICB9XG4gICAgbGV0IGxlZnQgPSB0UmVjdC5sZWZ0ICsgKHRSZWN0LndpZHRoIC8gMikgLSAodGlwUmVjdC53aWR0aCAvIDIpO1xuICAgIGxlZnQgPSBjbGFtcChsZWZ0LCA4LCB2dyAtIHRpcFJlY3Qud2lkdGggLSA4KTtcbiAgICAvLyBQcmV2ZW50IGJvdHRvbSBvdmVyZmxvd1xuICAgIGlmICh0b3AgKyB0aXBSZWN0LmhlaWdodCA+IHZoIC0gOCkge1xuICAgICAgdG9wID0gTWF0aC5tYXgoOCwgdmggLSB0aXBSZWN0LmhlaWdodCAtIDgpO1xuICAgIH1cblxuICAgIHRpcC5zdHlsZS50b3AgPSBgJHtNYXRoLnJvdW5kKHRvcCl9cHhgO1xuICAgIHRpcC5zdHlsZS5sZWZ0ID0gYCR7TWF0aC5yb3VuZChsZWZ0KX1weGA7XG4gICAgdGlwLnNldEF0dHJpYnV0ZSgnZGF0YS1zaWRlJywgc2lkZSk7XG4gIH1cblxuICBmdW5jdGlvbiBzaG93KHRyaWdnZXIpIHtcbiAgICBjb25zdCB0aXAgPSBnZXRUaXAodHJpZ2dlcik7XG4gICAgaWYgKCF0aXApIHJldHVybjtcbiAgICAvLyBQdXQgY29udGVudFxuICAgIGNvbnN0IHRleHQgPSB0cmlnZ2VyLmRhdGFzZXQudG9vbHRpcD8udHJpbSgpO1xuICAgIGlmICh0ZXh0KSB0aXAudGV4dENvbnRlbnQgPSB0ZXh0O1xuICAgIC8vIE1hcmsgc3RhdGVcbiAgICB0cmlnZ2VyLnNldEF0dHJpYnV0ZSgnYXJpYS1leHBhbmRlZCcsICd0cnVlJyk7XG4gICAgcG9zaXRpb25UaXAodHJpZ2dlciwgdGlwKTtcbiAgICBzZXRWaXNpYmxlKHRpcCwgdHJ1ZSk7XG4gICAgY3VycmVudCA9IHsgdHJpZ2dlciwgdGlwIH07XG5cbiAgICAvLyBCaW5kIGdsb2JhbCBsaXN0ZW5lcnNcbiAgICBjb25zdCBvZmZLZXkgPSBvbihkb2N1bWVudCwgJ2tleWRvd24nLCAoZSkgPT4ge1xuICAgICAgaWYgKGUua2V5ID09PSAnRXNjYXBlJykgaGlkZSgpO1xuICAgIH0pO1xuICAgIGNvbnN0IG9mZkNsaWNrID0gb24oZG9jdW1lbnQsICdtb3VzZWRvd24nLCAoZSkgPT4ge1xuICAgICAgaWYgKCFjdXJyZW50KSByZXR1cm47XG4gICAgICBjb25zdCB3aXRoaW5UcmlnZ2VyID0gY3VycmVudC50cmlnZ2VyLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGNvbnN0IHdpdGhpblRpcCA9IGN1cnJlbnQudGlwLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGlmICghd2l0aGluVHJpZ2dlciAmJiAhd2l0aGluVGlwKSBoaWRlKCk7XG4gICAgfSk7XG4gICAgY29uc3Qgb2ZmVG91Y2ggPSBvbihkb2N1bWVudCwgJ3RvdWNoc3RhcnQnLCAoZSkgPT4ge1xuICAgICAgaWYgKCFjdXJyZW50KSByZXR1cm47XG4gICAgICBjb25zdCB3aXRoaW5UcmlnZ2VyID0gY3VycmVudC50cmlnZ2VyLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGNvbnN0IHdpdGhpblRpcCA9IGN1cnJlbnQudGlwLmNvbnRhaW5zKGUudGFyZ2V0KTtcbiAgICAgIGlmICghd2l0aGluVHJpZ2dlciAmJiAhd2l0aGluVGlwKSBoaWRlKCk7XG4gICAgfSwgeyBwYXNzaXZlOiB0cnVlIH0pO1xuICAgIGNvbnN0IG9mZlNjcm9sbCA9IG9uKHdpbmRvdywgJ3Njcm9sbCcsICgpID0+IGN1cnJlbnQgJiYgcG9zaXRpb25UaXAoY3VycmVudC50cmlnZ2VyLCBjdXJyZW50LnRpcCkpO1xuICAgIGNvbnN0IG9mZlJlc2l6ZSA9IG9uKHdpbmRvdywgJ3Jlc2l6ZScsICgpID0+IGN1cnJlbnQgJiYgcG9zaXRpb25UaXAoY3VycmVudC50cmlnZ2VyLCBjdXJyZW50LnRpcCkpO1xuICAgIHVuYmluZERvY0hhbmRsZXJzID0gKCkgPT4geyBvZmZLZXkoKTsgb2ZmQ2xpY2soKTsgb2ZmVG91Y2goKTsgb2ZmU2Nyb2xsKCk7IG9mZlJlc2l6ZSgpOyB9O1xuICB9XG5cbiAgZnVuY3Rpb24gaGlkZSgpIHtcbiAgICBpZiAoIWN1cnJlbnQpIHJldHVybjtcbiAgICBzZXRWaXNpYmxlKGN1cnJlbnQudGlwLCBmYWxzZSk7XG4gICAgY3VycmVudC50cmlnZ2VyLnNldEF0dHJpYnV0ZSgnYXJpYS1leHBhbmRlZCcsICdmYWxzZScpO1xuICAgIHVuYmluZERvY0hhbmRsZXJzKCk7XG4gICAgY3VycmVudCA9IG51bGw7XG4gIH1cblxuICB0cmlnZ2Vycy5mb3JFYWNoKChlbCkgPT4ge1xuICAgIC8vIEhvdmVyL2ZvY3VzIHNob3dcbiAgICBvbihlbCwgJ21vdXNlZW50ZXInLCAoKSA9PiBzaG93KGVsKSk7XG4gICAgb24oZWwsICdmb2N1cycsICgpID0+IHNob3coZWwpKTtcbiAgICAvLyBNb3VzZWxlYXZlL2JsdXIgaGlkZSAoYnV0IGFsbG93IGNsaWNrIHRvIHRvZ2dsZSlcbiAgICBvbihlbCwgJ21vdXNlbGVhdmUnLCAoKSA9PiB7IGlmIChkb2N1bWVudC5hY3RpdmVFbGVtZW50ICE9PSBlbCkgaGlkZSgpOyB9KTtcbiAgICBvbihlbCwgJ2JsdXInLCAoKSA9PiBoaWRlKCkpO1xuICAgIC8vIFRhcC9jbGljayB0b2dnbGUgZm9yIG1vYmlsZVxuICAgIG9uKGVsLCAnY2xpY2snLCAoZSkgPT4ge1xuICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgaWYgKGN1cnJlbnQgJiYgY3VycmVudC50cmlnZ2VyID09PSBlbCkgaGlkZSgpOyBlbHNlIHNob3coZWwpO1xuICAgIH0pO1xuICAgIG9uKGVsLCAndG91Y2hzdGFydCcsIChlKSA9PiB7XG4gICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICBpZiAoY3VycmVudCAmJiBjdXJyZW50LnRyaWdnZXIgPT09IGVsKSBoaWRlKCk7IGVsc2Ugc2hvdyhlbCk7XG4gICAgfSwgeyBwYXNzaXZlOiBmYWxzZSB9KTtcbiAgfSk7XG59KSgpO1xuIiwiLy8gTGlnaHR3ZWlnaHQgRE9NIHV0aWxpdGllcyAobm8gZGVwZW5kZW5jaWVzKVxuXG4vKipcbiAqIFF1ZXJ5IGEgc2luZ2xlIGVsZW1lbnQgd2l0aGluIGFuIG9wdGlvbmFsIGNvbnRleHQuXG4gKiBAcGFyYW0ge3N0cmluZ30gc2VsZWN0b3JcbiAqIEBwYXJhbSB7UGFyZW50Tm9kZX0gW2N0eD1kb2N1bWVudF1cbiAqIEByZXR1cm5zIHtFbGVtZW50fG51bGx9XG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBxcyhzZWxlY3RvciwgY3R4ID0gZG9jdW1lbnQpIHtcbiAgcmV0dXJuIGN0eC5xdWVyeVNlbGVjdG9yKHNlbGVjdG9yKTtcbn1cblxuLyoqXG4gKiBRdWVyeSBhbGwgZWxlbWVudHMgYXMgYW4gYXJyYXkgd2l0aGluIGFuIG9wdGlvbmFsIGNvbnRleHQuXG4gKiBAcGFyYW0ge3N0cmluZ30gc2VsZWN0b3JcbiAqIEBwYXJhbSB7UGFyZW50Tm9kZX0gW2N0eD1kb2N1bWVudF1cbiAqIEByZXR1cm5zIHtFbGVtZW50W119XG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBxc2Eoc2VsZWN0b3IsIGN0eCA9IGRvY3VtZW50KSB7XG4gIHJldHVybiBBcnJheS5mcm9tKGN0eC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKSk7XG59XG5cbi8qKlxuICogQWRkIGFuIGV2ZW50IGxpc3RlbmVyIHdpdGggYSBzbWFsbCBjb252ZW5pZW5jZSB3cmFwcGVyLlxuICogQHBhcmFtIHtFdmVudFRhcmdldH0gdGFyZ2V0XG4gKiBAcGFyYW0ge3N0cmluZ30gdHlwZVxuICogQHBhcmFtIHtGdW5jdGlvbn0gaGFuZGxlclxuICogQHBhcmFtIHtBZGRFdmVudExpc3RlbmVyT3B0aW9uc3xib29sZWFufSBbb3B0aW9uc11cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIG9uKHRhcmdldCwgdHlwZSwgaGFuZGxlciwgb3B0aW9ucykge1xuICBpZiAoIXRhcmdldCkgcmV0dXJuICgpID0+IHt9O1xuICB0YXJnZXQuYWRkRXZlbnRMaXN0ZW5lcih0eXBlLCBoYW5kbGVyLCBvcHRpb25zKTtcbiAgcmV0dXJuICgpID0+IHRhcmdldC5yZW1vdmVFdmVudExpc3RlbmVyKHR5cGUsIGhhbmRsZXIsIG9wdGlvbnMpO1xufVxuXG4vKipcbiAqIFNtb290aGx5IHNjcm9sbCB0byBhbiBlbGVtZW50IChvciB5IHBvc2l0aW9uKSwgd2l0aCBzYWZlIGZhbGxiYWNrLlxuICogQHBhcmFtIHtFbGVtZW50fG51bWJlcn0gZWxPcllcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHNtb290aFNjcm9sbFRvKGVsT3JZKSB7XG4gIGNvbnN0IHkgPSB0eXBlb2YgZWxPclkgPT09ICdudW1iZXInXG4gICAgPyBlbE9yWVxuICAgIDogKGVsT3JZPy5nZXRCb3VuZGluZ0NsaWVudFJlY3QgPyAod2luZG93LnNjcm9sbFkgKyBlbE9yWS5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS50b3ApIDogMCk7XG5cbiAgdHJ5IHtcbiAgICB3aW5kb3cuc2Nyb2xsVG8oeyB0b3A6IHksIGJlaGF2aW9yOiAnc21vb3RoJyB9KTtcbiAgfSBjYXRjaCAoXykge1xuICAgIC8vIEZhbGxiYWNrIGZvciBvbGRlciBicm93c2Vyc1xuICAgIHdpbmRvdy5zY3JvbGxUbygwLCB5KTtcbiAgfVxufVxuXG4iLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiXSwibmFtZXMiOlsicXMiLCJvbiIsImluaXRFbWFpbENhcHR1cmUiLCJzZWN0aW9uIiwiZm9ybSIsInF1ZXJ5U2VsZWN0b3IiLCJlIiwiZW1haWwiLCJ2YWx1ZSIsInByZXZlbnREZWZhdWx0IiwiZm9jdXMiLCJ0IiwiciIsIlN5bWJvbCIsIm4iLCJpdGVyYXRvciIsIm8iLCJ0b1N0cmluZ1RhZyIsImkiLCJjIiwicHJvdG90eXBlIiwiR2VuZXJhdG9yIiwidSIsIk9iamVjdCIsImNyZWF0ZSIsIl9yZWdlbmVyYXRvckRlZmluZTIiLCJmIiwicCIsInkiLCJHIiwidiIsImEiLCJkIiwiYmluZCIsImxlbmd0aCIsImwiLCJUeXBlRXJyb3IiLCJjYWxsIiwiZG9uZSIsIkdlbmVyYXRvckZ1bmN0aW9uIiwiR2VuZXJhdG9yRnVuY3Rpb25Qcm90b3R5cGUiLCJnZXRQcm90b3R5cGVPZiIsInNldFByb3RvdHlwZU9mIiwiX19wcm90b19fIiwiZGlzcGxheU5hbWUiLCJfcmVnZW5lcmF0b3IiLCJ3IiwibSIsImRlZmluZVByb3BlcnR5IiwiX3JlZ2VuZXJhdG9yRGVmaW5lIiwiX2ludm9rZSIsImVudW1lcmFibGUiLCJjb25maWd1cmFibGUiLCJ3cml0YWJsZSIsImFzeW5jR2VuZXJhdG9yU3RlcCIsIlByb21pc2UiLCJyZXNvbHZlIiwidGhlbiIsIl9hc3luY1RvR2VuZXJhdG9yIiwiYXJndW1lbnRzIiwiYXBwbHkiLCJfbmV4dCIsIl90aHJvdyIsImdldENpdHlJbnB1dCIsImVuc3VyZUNlbnRlcmVkIiwiZWwiLCJwcmVmZXJzUmVkdWNlZCIsIndpbmRvdyIsIm1hdGNoTWVkaWEiLCJtYXRjaGVzIiwic2Nyb2xsSW50b1ZpZXciLCJiZWhhdmlvciIsImJsb2NrIiwiaW5saW5lIiwiXyIsIndhaXRGb3JTY3JvbGxFbmQiLCJtYXhNcyIsInVuZGVmaW5lZCIsImxhc3RZIiwic2Nyb2xsWSIsImVsYXBzZWQiLCJzdGVwIiwidGltZXIiLCJzZXRJbnRlcnZhbCIsIk1hdGgiLCJhYnMiLCJjbGVhckludGVydmFsIiwib2ZmIiwib25jZSIsImNyZWF0ZU92ZXJsYXkiLCJvdmVybGF5IiwiZG9jdW1lbnQiLCJjcmVhdGVFbGVtZW50IiwiY2xhc3NOYW1lIiwiYm9keSIsImFwcGVuZENoaWxkIiwicmVxdWVzdEFuaW1hdGlvbkZyYW1lIiwiY2xhc3NMaXN0IiwiYWRkIiwiZWxldmF0ZSIsImRlRWxldmF0ZSIsInJlbW92ZSIsImdldExpdmVSZWdpb24iLCJyZWdpb24iLCJnZXRFbGVtZW50QnlJZCIsImlkIiwic2V0QXR0cmlidXRlIiwiYW5ub3VuY2UiLCJtc2ciLCJ0ZXh0Q29udGVudCIsInNldFRpbWVvdXQiLCJpc0FjdGl2ZSIsImNlbnRlckFuZEZvY3VzQ2l0eSIsIl94IiwiX2NlbnRlckFuZEZvY3VzQ2l0eSIsIl9jYWxsZWUiLCJ0YXJnZXRFbCIsImlucHV0IiwiY2xlYW51cCIsIm9mZkVzYyIsIm9mZkJsdXIiLCJvZmZDbGljayIsIm9mZklucHV0IiwiX2NvbnRleHQiLCJwcmV2ZW50U2Nyb2xsIiwia2V5IiwiQ1dfZm9jdXNDaXR5QWZ0ZXJTY3JvbGwiLCJ0YXJnZXQiLCJjbG9zZXN0IiwiaHJlZiIsImdldEF0dHJpYnV0ZSIsInFzYSIsInNtb290aFNjcm9sbFRvIiwiaW5pdEhlcm9TY3JvbGwiLCJ0cmlnZ2VycyIsImZvckVhY2giLCJ0cmlnZ2VyIiwidGFyZ2V0U2VsIiwiY29uY2F0Iiwic3RhcnRzV2l0aCIsImxvY2F0aW9uIiwiaGFzaCIsInN1YnN0cmluZyIsIl90YXJnZXRFbCRxdWVyeVNlbGVjdCIsImhhc0NpdHkiLCJpbml0U29ydEhhbmRsZXIiLCJzZWxlY3QiLCJhbGxvd2VkIiwiU2V0IiwiYWRkRXZlbnRMaXN0ZW5lciIsIlN0cmluZyIsInRyaW0iLCJoYXMiLCJ1cmwiLCJVUkwiLCJzZWFyY2hQYXJhbXMiLCJzZXQiLCJhc3NpZ24iLCJ0b1N0cmluZyIsImluaXRUb29sdGlwcyIsImN1cnJlbnQiLCJ1bmJpbmREb2NIYW5kbGVycyIsImdldFRpcCIsInNldFZpc2libGUiLCJ0aXAiLCJ5ZXMiLCJ0b2dnbGUiLCJ0b2dnbGVBdHRyaWJ1dGUiLCJtZWFzdXJlVGlwIiwicHJldlRyYW5zIiwic3R5bGUiLCJ0cmFuc2l0aW9uIiwicHJldlZpcyIsInZpc2liaWxpdHkiLCJyZWN0IiwiZ2V0Qm91bmRpbmdDbGllbnRSZWN0IiwiY2xhbXAiLCJtaW4iLCJtYXgiLCJwb3NpdGlvblRpcCIsInNwYWNpbmciLCJ0UmVjdCIsInZ3IiwiaW5uZXJXaWR0aCIsInZoIiwiaW5uZXJIZWlnaHQiLCJ0aXBSZWN0IiwidG9wIiwiaGVpZ2h0Iiwic2lkZSIsImJvdHRvbSIsImxlZnQiLCJ3aWR0aCIsInJvdW5kIiwic2hvdyIsIl90cmlnZ2VyJGRhdGFzZXQkdG9vbCIsInRleHQiLCJkYXRhc2V0IiwidG9vbHRpcCIsIm9mZktleSIsImhpZGUiLCJ3aXRoaW5UcmlnZ2VyIiwiY29udGFpbnMiLCJ3aXRoaW5UaXAiLCJvZmZUb3VjaCIsInBhc3NpdmUiLCJvZmZTY3JvbGwiLCJvZmZSZXNpemUiLCJhY3RpdmVFbGVtZW50Iiwic2VsZWN0b3IiLCJjdHgiLCJBcnJheSIsImZyb20iLCJxdWVyeVNlbGVjdG9yQWxsIiwidHlwZSIsImhhbmRsZXIiLCJvcHRpb25zIiwicmVtb3ZlRXZlbnRMaXN0ZW5lciIsImVsT3JZIiwic2Nyb2xsVG8iXSwic291cmNlUm9vdCI6IiJ9