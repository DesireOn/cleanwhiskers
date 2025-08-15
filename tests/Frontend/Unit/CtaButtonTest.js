const assert = require('assert');
const path = require('path');
const { initCtaButton } = require(path.join(__dirname, '../../../src/public/js/cta-button.js'));

function createButton() {
  const button = {
    classList: {
      list: [],
      add(cls) { this.list.push(cls); },
      remove(cls) { this.list = this.list.filter(c => c !== cls); },
      contains(cls) { return this.list.includes(cls); },
    },
    attributes: {},
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; },
    getAttribute(name) { return this.attributes[name]; },
    querySelector() { return null; },
    appendChild(node) { this.spinner = node; },
    after() {},
    addEventListener(event, handler) { this.eventListeners[event] = handler; },
    eventListeners: {},
  };
  return button;
}

function createDocument(button) {
  return {
    querySelector: () => button,
    createElement: () => ({ className: '', hidden: false, setAttribute() {}, after() {} }),
  };
}

(function testLoadingAndDebounce() {
  const button = createButton();
  const doc = createDocument(button);
  let fetchCalls = 0;
  function fakeFetch() {
    fetchCalls += 1;
    return new Promise(() => {});
  }
  initCtaButton(doc, fakeFetch, {});
  assert.strictEqual(button.classList.contains('is-loading'), false, 'initially not loading');
  button.eventListeners.click({ preventDefault() {} });
  assert.strictEqual(button.classList.contains('is-loading'), true, 'loading after click');
  button.eventListeners.click({ preventDefault() {} });
  assert.strictEqual(fetchCalls, 1, 'debounced double click');
})();

console.log('CtaButton tests passed');
