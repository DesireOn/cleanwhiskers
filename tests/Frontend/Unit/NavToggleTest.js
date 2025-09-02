const assert = require('assert');
const path = require('path');
const navToggle = require(path.join(__dirname, '../../../assets/js/mobile-nav-toggle.js'));

function createDocument() {
  const listeners = {};
  return {
    body: { classList: { add: () => {}, remove: () => {} }, dataset: {}, style: {} },
    activeElement: null,
    addEventListener(event, handler) { listeners[event] = handler; },
    dispatch(event, e) { if (listeners[event]) { listeners[event](e); } },
  };
}

function createNav() {
  return {
    classList: {
      classes: new Set(),
      add(c) { this.classes.add(c); },
      remove(c) { this.classes.delete(c); },
      contains(c) { return this.classes.has(c); },
    },
    links: [],
    querySelectorAll() { return this.links; },
    querySelector() { return null; },
    contains(el) { return this.links.includes(el); },
    setAttribute(name, value) { this[name] = value; },
  };
}

function createToggle(doc) {
  const listeners = {};
  return {
    attrs: {},
    setAttribute(name, value) { this.attrs[name] = value; },
    addEventListener(event, handler) { listeners[event] = handler; },
    click() { if (listeners.click) { listeners.click(); } },
    focus() { doc.activeElement = this; },
    contains(el) { return el === this; },
  };
}

function createOverlay() {
  return {
    classList: {
      classes: new Set(),
      add(c) { this.classes.add(c); },
      remove(c) { this.classes.delete(c); },
      contains(c) { return this.classes.has(c); },
    },
    setAttribute(name, value) { this[name] = value; },
    addEventListener: () => {},
  };
}

function createLink() {
  let handler = null;
  return {
    addEventListener(event, h) { if (event === 'click') { handler = h; } },
    click() { if (handler) { handler(); } },
  };
}

(function testOpenCloseEscAndLink() {
  const doc = createDocument();
  const nav = createNav();
  const overlay = createOverlay();
  const toggle = createToggle(doc);
  const link = createLink();
  nav.links.push(link);

  doc.getElementById = (id) => {
    if (id === 'nav-toggle') return toggle;
    if (id === 'primary-nav') return nav;
    if (id === 'nav-overlay') return overlay;
    return null;
  };

  navToggle.initMobileNav(doc);

  // open menu and check overlay/attributes
  navToggle.openMenu(doc, nav, toggle, overlay);
  assert.ok(nav.classList.contains('is-open'), 'menu open');
  assert.ok(overlay.classList.contains('is-open'), 'overlay open');
  assert.strictEqual(toggle.attrs['aria-expanded'], 'true', 'aria-expanded true');
  assert.strictEqual(nav['aria-hidden'], 'false', 'aria-hidden false');
  assert.strictEqual(overlay['aria-hidden'], 'false', 'overlay aria-hidden false');
  assert.strictEqual(doc.body.dataset.menuOpen, 'true', 'body dataset set');
  assert.strictEqual(doc.body.style.overflow, 'hidden', 'body overflow hidden');

  // close via ESC
  doc.dispatch('keydown', { key: 'Escape' });
  assert.ok(!nav.classList.contains('is-open'), 'menu closed by ESC');
  assert.ok(!overlay.classList.contains('is-open'), 'overlay closed by ESC');
  assert.strictEqual(doc.body.style.overflow, '', 'body overflow cleared');
  assert.strictEqual(doc.activeElement, toggle, 'focus returned to toggle');

  // reopen and close via link click
  navToggle.openMenu(doc, nav, toggle, overlay);
  link.click();
  assert.ok(!nav.classList.contains('is-open'), 'menu closed by link click');
  assert.ok(!overlay.classList.contains('is-open'), 'overlay closed by link click');
})();

console.log('Nav toggle tests passed');
