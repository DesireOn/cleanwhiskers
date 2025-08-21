const assert = require('assert');
const path = require('path');
const navToggle = require(path.join(__dirname, '../../../assets/js/mobile-nav-toggle.js'));

function createDocument() {
  return {
    body: { classList: { add: () => {}, remove: () => {} }, dataset: {}, style: {} },
    activeElement: null,
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
    setAttribute(name, value) { this[name] = value; },
  };
}

function createToggle() {
  return {
    attrs: {},
    setAttribute(name, value) { this.attrs[name] = value; },
    addEventListener: () => {},
  };
}

(function testOpenClose() {
  const doc = createDocument();
  const nav = createNav();
  const toggle = createToggle();

  navToggle.openMenu(doc, nav, toggle);
  assert.ok(nav.classList.contains('is-open'), 'menu open');
  assert.strictEqual(toggle.attrs['aria-expanded'], 'true', 'aria-expanded true');
  assert.strictEqual(nav['aria-hidden'], 'false', 'aria-hidden false');
  assert.strictEqual(doc.body.dataset.menuOpen, 'true', 'body dataset set');

  navToggle.closeMenu(doc, nav, toggle);
  assert.ok(!nav.classList.contains('is-open'), 'menu closed');
  assert.strictEqual(toggle.attrs['aria-expanded'], 'false', 'aria-expanded false');
  assert.strictEqual(nav['aria-hidden'], 'true', 'aria-hidden true');
  assert.strictEqual(doc.body.dataset.menuOpen, undefined, 'body dataset cleared');
})();

console.log('Nav toggle tests passed');
