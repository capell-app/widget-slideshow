const initialiseSlideshow = (root) => {
    if (root.dataset.ready === 'true') return
    root.dataset.ready = 'true'
    const slides = Array.from(root.querySelectorAll('.capell-slideshow__slide'))
    if (slides.length < 2) return
    let index = 0
    let paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    let timer
    const show = (next) => {
        index = (next + slides.length) % slides.length
        slides.forEach((slide, position) => {
            slide.hidden = position !== index
        })
    }
    const stop = () => {
        window.clearInterval(timer)
        timer = undefined
    }
    const start = () => {
        stop()
        if (!paused && root.dataset.autoAdvance === 'true')
            timer = window.setInterval(() => show(index + 1), 5000)
    }
    root.querySelector('[data-slideshow-previous]')?.addEventListener(
        'click',
        () => {
            show(index - 1)
            start()
        },
    )
    root.querySelector('[data-slideshow-next]')?.addEventListener(
        'click',
        () => {
            show(index + 1)
            start()
        },
    )
    root.querySelector('[data-slideshow-toggle]')?.addEventListener(
        'click',
        (event) => {
            paused = !paused
            const toggle = event.currentTarget
            toggle.setAttribute('aria-pressed', String(paused))
            toggle.textContent = paused
                ? (toggle.dataset.labelResume ?? 'Resume')
                : (toggle.dataset.labelPause ?? 'Pause')
            toggle.setAttribute(
                'aria-label',
                paused
                    ? (toggle.dataset.labelResumeSlideshow ??
                          'Resume slideshow')
                    : (toggle.dataset.labelPauseSlideshow ?? 'Pause slideshow'),
            )
            start()
        },
    )
    root.addEventListener('mouseenter', stop)
    root.addEventListener('mouseleave', start)
    root.addEventListener('focusin', stop)
    root.addEventListener('focusout', start)
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
