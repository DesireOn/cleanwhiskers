const assert = require('assert');
const path = require('path');
const { initCtaButton } = require(path.join(__dirname, '../../../src/public/js/cta-button.js'));

function createButton() {
  const button = {
    attributes: {},
    children: [],
    firstChild: null,
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; },
    getAttribute(name) { return this.attributes[name]; },
    after() {},
    insertBefore(node, ref) {
      if (ref === this.firstChild) {
        this.children.unshift(node);
      } else {
        this.children.push(node);
      }
      this.firstChild = this.children[0] || null;
      node.parentNode = this;
    },
    removeChild(node) {
      const idx = this.children.indexOf(node);
      if (idx > -1) {
        this.children.splice(idx, 1);
      }
      this.firstChild = this.children[0] || null;
    },
    addEventListener(event, handler) { this.eventListeners[event] = handler; },
    eventListeners: {},
  };
  return button;
}

function createDocument(buttons) {
  return {
    querySelectorAll: () => buttons,
    createElement: () => ({
      className: '',
      hidden: false,
      setAttribute() {},
      appendChild() {},
      after() {},
    }),
  };
}

function testDebounce() {
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
}

function testMultipleButtons() {
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
}

function testErrorRestoresState() {
  return new Promise((resolve) => {
    const button = createButton();
    const doc = createDocument([button]);
    function fakeFetch() {
      return Promise.reject(new Error('fail'));
    }
    initCtaButton(doc, fakeFetch, {});
    button.eventListeners.click({ preventDefault() {} });
    setTimeout(() => {
      assert.strictEqual(button.attributes['aria-disabled'], undefined, 'button re-enabled after error');
      assert.strictEqual(button.attributes['aria-busy'], undefined, 'aria-busy cleared after error');
      assert.strictEqual(button.children.length, 0, 'spinner removed after error');
      resolve();
    }, 0);
  });
}

(async function run() {
  testDebounce();
  testMultipleButtons();
  await testErrorRestoresState();
  console.log('CtaButton tests passed');
})();
