<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

final class LoadingStateTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSearchButtonInitiallyHasNoSpinner(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $spinners = $crawler->filter('.search-form__button .spinner');
        self::assertCount(0, $spinners);
    }

    public function testSearchFormAddsSpinnerOnSubmit(): void
    {
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');

        $script = <<<'NODE'
const path = require('path');
const listeners = {};
const form = { eventListeners: {}, addEventListener(ev, fn) { this.eventListeners[ev] = fn; } };
const city = { value: 'Sofia', addEventListener() {} };
const submit = {
  disabled: false,
  children: [],
  firstChild: null,
  setAttribute() {},
  insertBefore(node) { this.children.unshift(node); this.firstChild = this.children[0]; node.parentNode = this; }
};
const documentStub = {
  addEventListener(ev, fn) { listeners[ev] = fn; },
  getElementById(id) {
    if (id === 'search-form') return form;
    if (id === 'city') return city;
    if (id === 'search-submit') return submit;
    return null;
  },
  createElement() { return { className: '', setAttribute() {}, appendChild() {} }; }
};
global.document = documentStub;
require(path.join(process.cwd(), 'assets/js/search-form.js'));
listeners['DOMContentLoaded']();
form.eventListeners['submit']();
console.log(JSON.stringify({ hasSpinner: submit.children.some(c => c.className === 'spinner') }));
NODE;

        $process = new Process(['node'], $projectDir);
        $process->setInput($script);
        $process->mustRun();
        $data = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($data['hasSpinner']);
    }

    public function testCtaButtonSpinnerRemovedAfterError(): void
    {
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');

        $script = <<<'NODE'
const path = require('path');
const { initCtaButton } = require(path.join(process.cwd(), 'src/public/js/cta-button.js'));
const button = {
  attributes: {},
  children: [],
  firstChild: null,
  setAttribute(name, value) { this.attributes[name] = value; },
  removeAttribute(name) { delete this.attributes[name]; },
  getAttribute(name) { return this.attributes[name]; },
  insertBefore(node) { this.children.unshift(node); this.firstChild = this.children[0]; node.parentNode = this; },
  removeChild(node) { const idx = this.children.indexOf(node); if (idx > -1) { this.children.splice(idx, 1); } this.firstChild = this.children[0] || null; },
  after() {},
  addEventListener(ev, handler) { this.handler = handler; }
};
const doc = {
  querySelectorAll() { return [button]; },
  createElement() { return { className: '', hidden: false, setAttribute() {}, appendChild() {}, after() {} }; }
};
function fakeFetch() { return Promise.reject(new Error('fail')); }
let added = false;
initCtaButton(doc, fakeFetch, { location: { href: '' } });
button.handler({ preventDefault() {} });
added = button.children.some(c => c.className === 'spinner');
setTimeout(() => {
  const removed = button.children.length === 0;
  console.log(JSON.stringify({ added, removed }));
}, 0);
NODE;

        $process = new Process(['node'], $projectDir);
        $process->setInput($script);
        $process->mustRun();
        $data = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($data['added']);
        self::assertTrue($data['removed']);
    }
}
