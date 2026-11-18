import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import test from 'node:test'
import vm from 'node:vm'

const source = readFileSync(
    new URL('../../resources/dist/widget.js', import.meta.url),
    'utf8',
)

function slideshow({ reducedMotion = false } = {}) {
    const listeners = {}
    const clicks = {}
    const intervals = new Map()
    let nextInterval = 1
    const attributes = {}
    const slides = [{ hidden: false }, { hidden: true }, { hidden: true }]
    const button = (name) => ({
        addEventListener: (event, callback) => {
            clicks[`${name}:${event}`] = callback
        },
    })
    const toggle = {
        dataset: {},
        attributes: {},
        textContent: 'Pause',
        setAttribute(name, value) {
            this.attributes[name] = value
        },
        addEventListener: (event, callback) => {
            clicks[`toggle:${event}`] = callback
        },
    }
    const track = {
        setAttribute: (name, value) => {
            attributes[name] = value
        },
    }
    class Node {}
    const inside = new Node()
    const root = {
        dataset: { autoAdvance: 'true' },
        contains: (node) => node === inside,
        querySelectorAll: (selector) =>
            selector === '.capell-slideshow__slide' ? slides : [],
        querySelector: (selector) =>
            ({
                '.capell-slideshow__track': track,
                '[data-slideshow-toggle]': toggle,
                '[data-slideshow-previous]': button('previous'),
                '[data-slideshow-next]': button('next'),
            })[selector] ?? null,
        addEventListener: (event, callback) => {
            listeners[event] = callback
        },
    }
    vm.runInNewContext(source, {
        Node,
        window: {
            matchMedia: () => ({ matches: reducedMotion }),
            setInterval: (callback) => {
                intervals.set(nextInterval, callback)
                return nextInterval++
            },
            clearInterval: (id) => intervals.delete(id),
        },
        document: { querySelectorAll: () => [root], addEventListener() {} },
    })
    return {
        attributes,
        clicks,
        inside,
        intervals,
        listeners,
        slides,
        toggle,
        visible: () => slides.findIndex((slide) => !slide.hidden),
        tick: () => [...intervals.values()].forEach((callback) => callback()),
    }
}

test('automatic advancement runs, and slide changes are not announced while it does', () => {
    const widget = slideshow()
    assert.equal(widget.intervals.size, 1)
    assert.equal(widget.attributes['aria-live'], 'off')
    widget.tick()
    assert.equal(widget.visible(), 1)
})

test('pressing a control while hovered does not restart automatic advancement', () => {
    const widget = slideshow()
    widget.listeners.mouseenter()
    assert.equal(widget.intervals.size, 0)
    assert.equal(widget.attributes['aria-live'], 'polite')
    widget.clicks['next:click']()
    assert.equal(widget.visible(), 1)
    assert.equal(widget.intervals.size, 0)
    widget.clicks['previous:click']()
    assert.equal(widget.intervals.size, 0)
    widget.listeners.mouseleave()
    assert.equal(widget.intervals.size, 1)
})

test('pressing a control while focused does not restart, and focus moving between controls keeps it stopped', () => {
    const widget = slideshow()
    widget.listeners.focusin()
    widget.clicks['next:click']()
    assert.equal(widget.intervals.size, 0)
    widget.listeners.focusout({ relatedTarget: widget.inside })
    assert.equal(widget.intervals.size, 0)
    widget.listeners.focusout({ relatedTarget: null })
    assert.equal(widget.intervals.size, 1)
})

test('a visitor who paused stays paused after pressing a control and resumes only on request', () => {
    const widget = slideshow()
    widget.clicks['toggle:click']()
    assert.equal(widget.intervals.size, 0)
    assert.equal(widget.toggle.attributes['aria-label'], 'Resume slideshow')
    assert.equal('aria-pressed' in widget.toggle.attributes, false)
    widget.clicks['next:click']()
    assert.equal(widget.intervals.size, 0)
    widget.clicks['toggle:click']()
    assert.equal(widget.intervals.size, 1)
})

test('reduced motion never starts automatic advancement', () => {
    const widget = slideshow({ reducedMotion: true })
    assert.equal(widget.intervals.size, 0)
    assert.equal(widget.attributes['aria-live'], 'polite')
})

test('arrow keys and swipes move between slides', () => {
    const widget = slideshow({ reducedMotion: true })
    let prevented = 0
    const key = (name) =>
        widget.listeners.keydown({
            key: name,
            preventDefault: () => prevented++,
        })
    key('ArrowRight')
    assert.equal(widget.visible(), 1)
    key('ArrowLeft')
    key('ArrowLeft')
    assert.equal(widget.visible(), 2)
    key('Tab')
    assert.equal(prevented, 3)
    const touch = (x) => ({ changedTouches: [{ clientX: x }] })
    widget.listeners.touchstart(touch(200))
    widget.listeners.touchend(touch(100))
    assert.equal(widget.visible(), 0)
    widget.listeners.touchstart(touch(100))
    widget.listeners.touchend(touch(110))
    assert.equal(widget.visible(), 0)
})
