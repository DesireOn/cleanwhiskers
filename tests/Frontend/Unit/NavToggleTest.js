const assert = require('assert');
const path = require('path');
const navToggle = require(path.join(__dirname, '../../../src/public/js/nav-toggle.js'));

function createDocument() {
  const doc = {
    body: { dataset: {}, style: {} },
    activeElement: null,
    addEventListener: () => {},
    removeEventListener: () => {},
  };
  return doc;
}

function createLink(doc) {
  return {
    focusCalled: false,
    focus: function () {
      this.focusCalled = true;
      doc.activeElement = this;
    },
  };
}

(function testToggle() {
  const doc = createDocument();
  const first = createLink(doc);
  const last = createLink(doc);
  const nav = {
    querySelector: () => first,
    querySelectorAll: () => [first, last],
    contains: () => false,
    setAttribute: () => {},
  };
  const toggle = {
    setAttribute: () => {},
    addEventListener: () => {},
  };

  navToggle.openMenu(doc, nav, toggle);
  assert.strictEqual(doc.body.dataset.menuOpen, 'true', 'menu opens');
  assert.strictEqual(first.focusCalled, true, 'first element focused');

  navToggle.closeMenu(doc, nav, toggle);
  assert.ok(!('menuOpen' in doc.body.dataset), 'menu closes');
})();

(function testFocusTrap() {
  const doc = createDocument();
  const first = createLink(doc);
  const last = createLink(doc);
  const nav = {
    querySelectorAll: () => [first, last],
  };
  doc.body.dataset.menuOpen = 'true';
  doc.activeElement = first;
  const backward = { key: 'Tab', shiftKey: true, preventDefault: function () { this.called = true; }, called: false };
  navToggle.focusTrap(doc, nav, backward);
  assert.strictEqual(doc.activeElement, last, 'shift+tab cycles to last');
  assert.strictEqual(backward.called, true, 'default prevented');

  doc.activeElement = last;
  const forward = { key: 'Tab', shiftKey: false, preventDefault: function () { this.called = true; }, called: false };
  navToggle.focusTrap(doc, nav, forward);
  assert.strictEqual(doc.activeElement, first, 'tab cycles to first');
  assert.strictEqual(forward.called, true, 'default prevented');
})();

console.log('NavToggle tests passed');
