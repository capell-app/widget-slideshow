# Slideshow Widget

<!-- prettier-ignore-start -->

On the page for a finished job or last month's event, visitors step through the photos at their own pace. Each is shown wide, at a 16 by 9 shape, with its caption underneath and Previous and Next buttons below that. Up to twenty photos share the one spot, and Next on the last photo takes a visitor back to the first.

Editors add a Slideshow block from Layout Builder, the page builder. You name the slideshow, and screen readers read that name out. For every slide you upload an image and describe it in a few words. A caption is optional, and so is a link, whose label and address have to be filled in together.

Tick Advance automatically and the slides move on every five seconds, with a Pause button for visitors. If a visitor's device asks for reduced motion, the slideshow starts paused and they can press Resume.

## What it does

Slideshow gives editors an accessible carousel for local images, captions, and calls to action. Visitors can move through slides with explicit controls and reduced-motion preferences are respected.

## Do I need to do anything?

Add **Slideshow** in Layout Builder and enter its required accessible label. Add one to 20 ordered slides; each needs a local image and meaningful alternative text. Captions are optional. A call to action appears only when both its label and URL are supplied; URLs must be a site-relative path or an `http`/`https` URL. Choose playback settings; Previous and Next always remain available for multiple slides, including when controls were previously switched off. Choose whether slides should advance automatically, then preview the public page before publishing.

## Where it shows up

Editors configure ordered local media, captions, calls to action, controls, and playback behaviour in Layout Builder. The public carousel initially shows the first slide. The server-rendered output still contains every slide, so the content remains available without JavaScript.

## Good to know

- This free beta extension belongs to Capell Essentials and requires Layout Builder.
- Controls are on by default; automatic advancement is off by default. When enabled, it advances every five seconds.
- Any visitor interaction, keyboard focus, or reduced-motion preference stops automatic advancement. Visitors can pause or resume automatic playback using the visible controls.
- Slides use local media, and remote image URLs or unsafe CTA schemes are rejected.

---

For developers: see the [README](../README.md).

<!-- prettier-ignore-end -->
