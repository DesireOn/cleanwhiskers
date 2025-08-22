const assert = require('assert');
const path = require('path');
const navToggle = require(path.join(__dirname, '../../../assets/js/mobile-nav-toggle.js'));

function createDocument() {
  return {
    body: {
      classList: {
        classes: new Set(),
        add(c) { this.classes.add(c); },
        remove(c) { this.classes.delete(c); },
        contains(c) { return this.classes.has(c); },
      },
    },
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
    attrs: {},
    setAttribute(name, value) { this.attrs[name] = value; this[name] = value; },
    removeAttribute(name) { delete this.attrs[name]; delete this[name]; },
    contains: () => false,
  };
}

function createButton() {
  return {
    hidden: false,
    attrs: {},
    setAttribute(name, value) { this.attrs[name] = value; },
    addEventListener: () => {},
  };
}

(function testOpenClose() {
  const doc = createDocument();
  const nav = createNav();
  const openBtn = createButton();
  const closeBtn = createButton();
  closeBtn.hidden = true;

  navToggle.openMenu(doc, nav, openBtn, closeBtn);
  assert.ok(nav.classList.contains('is-open'), 'menu open');
  assert.strictEqual(nav.attrs['aria-hidden'], 'false', 'aria-hidden false');
  assert.strictEqual(openBtn.attrs['aria-expanded'], 'true', 'open button expanded');
  assert.ok(openBtn.hidden, 'open button hidden after open');
  assert.ok(!closeBtn.hidden, 'close button shown after open');
  assert.ok(doc.body.classList.contains('no-scroll'), 'body scroll locked');

  navToggle.closeMenu(doc, nav, openBtn, closeBtn);
  assert.ok(!nav.classList.contains('is-open'), 'menu closed');
  assert.strictEqual(nav.attrs['aria-hidden'], 'true', 'aria-hidden true');
  assert.strictEqual(openBtn.attrs['aria-expanded'], 'false', 'open button collapsed');
  assert.ok(!openBtn.hidden, 'open button shown after close');
  assert.ok(closeBtn.hidden, 'close button hidden after close');
  assert.ok(!doc.body.classList.contains('no-scroll'), 'body scroll unlocked');
})();

console.log('Nav toggle tests passed');
