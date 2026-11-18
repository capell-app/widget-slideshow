const initialiseSlideshow = (root) => {
    if (root.dataset.ready === 'true') return
    root.dataset.ready = 'true'
    const slides = Array.from(root.querySelectorAll('.capell-slideshow__slide'))
    if (slides.length < 2) return
    const track = root.querySelector('.capell-slideshow__track')
    let index = 0
    let paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    let hovered = false
    let focused = false
    let timer
    const show = (next) => {
        index = (next + slides.length) % slides.length
        slides.forEach((slide, position) => {
            slide.hidden = position !== index
        })
    }
    // Slide changes are announced only when the visitor moved them, never while the slideshow advances itself.
    const syncLive = () =>
        track?.setAttribute('aria-live', timer === undefined ? 'polite' : 'off')
    const stop = () => {
        window.clearInterval(timer)
        timer = undefined
        syncLive()
    }
    // Automatic advancement never runs while the visitor is hovering over or focused inside the slideshow.
    const start = () => {
        stop()
        if (
            !paused &&
            !hovered &&
            !focused &&
            root.dataset.autoAdvance === 'true'
        )
            timer = window.setInterval(() => show(index + 1), 5000)
        syncLive()
    }
    const toggle = root.querySelector('[data-slideshow-toggle]')
    const updateToggle = () => {
        if (!toggle) return
        toggle.textContent = paused
            ? (toggle.dataset.labelResume ?? 'Resume')
            : (toggle.dataset.labelPause ?? 'Pause')
        toggle.setAttribute(
            'aria-label',
            paused
                ? (toggle.dataset.labelResumeSlideshow ?? 'Resume slideshow')
                : (toggle.dataset.labelPauseSlideshow ?? 'Pause slideshow'),
        )
    }
    updateToggle()
    const move = (next) => {
        show(next)
        start()
    }
    root.querySelector('[data-slideshow-previous]')?.addEventListener(
        'click',
        () => move(index - 1),
    )
    root.querySelector('[data-slideshow-next]')?.addEventListener('click', () =>
        move(index + 1),
    )
    toggle?.addEventListener('click', () => {
        paused = !paused
        updateToggle()
        start()
    })
    root.addEventListener('mouseenter', () => {
        hovered = true
        start()
    })
    root.addEventListener('mouseleave', () => {
        hovered = false
        start()
    })
    root.addEventListener('focusin', () => {
        focused = true
        start()
    })
    root.addEventListener('focusout', (event) => {
        focused =
            event.relatedTarget instanceof Node &&
            root.contains(event.relatedTarget)
        start()
    })
    root.addEventListener('keydown', (event) => {
        if (event.altKey || event.ctrlKey || event.metaKey) return
        if (event.key === 'ArrowLeft') move(index - 1)
        else if (event.key === 'ArrowRight') move(index + 1)
        else return
        event.preventDefault()
    })
    let touchStart
    root.addEventListener(
        'touchstart',
        (event) => {
            touchStart = event.changedTouches[0]?.clientX
        },
        { passive: true },
    )
    root.addEventListener(
        'touchend',
        (event) => {
            const end = event.changedTouches[0]?.clientX
            if (touchStart === undefined || end === undefined) return
            const distance = end - touchStart
            touchStart = undefined
            if (Math.abs(distance) >= 40) move(index + (distance < 0 ? 1 : -1))
        },
        { passive: true },
    )
    start()
}
document
    .querySelectorAll('[data-capell-widget-slideshow]')
    .forEach(initialiseSlideshow)
document.addEventListener('capell:content-ready', (event) =>
    event.target
        ?.querySelectorAll?.('[data-capell-widget-slideshow]')
        .forEach(initialiseSlideshow),
)
