const assert = require('assert');
const path = require('path');
const { initCtaButton } = require(path.join(__dirname, '../../../src/public/js/cta-button.js'));

function createButton() {
  const button = {
    attributes: {},
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; },
    getAttribute(name) { return this.attributes[name]; },
    after() {},
    addEventListener(event, handler) { this.eventListeners[event] = handler; },
    eventListeners: {},
  };
  return button;
}

function createDocument(buttons) {
  return {
    querySelectorAll: () => buttons,
    createElement: () => ({ className: '', hidden: false, setAttribute() {}, after() {} }),
  };
}

(function testDebounce() {
  const button = createButton();
  const doc = createDocument([button]);
  let fetchCalls = 0;
  function fakeFetch() {
    fetchCalls += 1;
    return new Promise(() => {});
  }
  initCtaButton(doc, fakeFetch, {});
  button.eventListeners.click({ preventDefault() {} });
  button.eventListeners.click({ preventDefault() {} });
  assert.strictEqual(fetchCalls, 1, 'double click debounced');
  assert.strictEqual(button.attributes['aria-disabled'], 'true', 'button disabled after click');
})();

(function testMultipleButtons() {
  const buttonOne = createButton();
  const buttonTwo = createButton();
  const doc = createDocument([buttonOne, buttonTwo]);
  let fetchCalls = 0;
  function fakeFetch() {
    fetchCalls += 1;
    return new Promise(() => {});
  }
  initCtaButton(doc, fakeFetch, {});
  buttonOne.eventListeners.click({ preventDefault() {} });
  buttonTwo.eventListeners.click({ preventDefault() {} });
  assert.strictEqual(fetchCalls, 2, 'both buttons handled');
  assert.strictEqual(buttonOne.attributes['aria-disabled'], 'true');
  assert.strictEqual(buttonTwo.attributes['aria-disabled'], 'true');
})();

console.log('CtaButton tests passed');
