# Slideshow Widget

<!-- prettier-ignore-start -->

## What it does

Slideshow gives editors an accessible carousel for local images, captions, and calls to action. Visitors can move through slides with explicit controls and reduced-motion preferences are respected.

## Do I need to do anything?

Add **Slideshow** in Layout Builder and enter its required accessible label. Add one to 20 ordered slides; each needs a local image and meaningful alternative text. Captions are optional. A call to action appears only when both its label and URL are supplied; URLs must be a site-relative path or an `http`/`https` URL. Choose whether to show controls and whether slides should advance automatically, then preview the public page before publishing.

## Where it shows up

Editors configure ordered local media, captions, calls to action, controls, and playback behaviour in Layout Builder. The public carousel initially shows the first slide. The server-rendered output still contains every slide, so the content remains available without JavaScript.

## Good to know

- This free beta extension belongs to Capell Foundation and requires Layout Builder.
- Controls are on by default; automatic advancement is off by default. When enabled, it advances every five seconds.
- Any visitor interaction, keyboard focus, or reduced-motion preference stops automatic advancement. Visitors can pause or resume it when controls are shown.
- Slides use local media, and remote image URLs or unsafe CTA schemes are rejected.

## Screenshot status

Capture the ordered slide editor and a public slideshow with caption, call to action, controls, and position indicators visible. The committed Marketplace card is interim beta artwork until these two fixture captures are recorded.

---

For developers: see the [README](../README.md).

<!-- prettier-ignore-end -->
