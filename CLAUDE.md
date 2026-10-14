# Working in this theme

See `README.md` for the asset layout and how template stylesheets are loaded.

## CSS comments

**Do not write comments in CSS.** A selector and its properties already say
what a rule does; a comment restating it is noise that has to be kept in step
with the code underneath it.

This covers everything: no file header, no section banners, no explanations of
why a value was chosen. If a rule needs justifying, the justification belongs
in the commit message, not the stylesheet.

### The one exception

A **combined** stylesheet that holds blocks with nothing to do with each other
may use a short comment to mark where one block ends and the next begins.
`assets/css/custom.css` is the only such file in this theme:

```css
/* Card Styles */
.card-style-1 { ... }

/* Hero Gradient */
.hero-gradient { ... }
```

Those are labels, not prose. One line, naming the block.

### Per-template stylesheets

Everything in `assets/css/templates/` and `assets/css/inc/` (including
`inc/shortcodes/`) gets **no comments at all**. Each of those files covers
exactly one template, component or shortcode and its filename says which, so
there is nothing a header comment could add.

`assets/css/inc/scroll-top.css` is the reference for what
this should look like.

### When editing

Do not add comments to CSS that has had them removed. Existing comments in
older stylesheets are not a licence to write more.

## Customizer edit shortcuts

The pencil buttons in the Customizer preview are placed on purpose. Keep to
this when adding or moving any of them.

### Header: bottom left, on one line

Every header shortcut (logo, main menu, header button, and any added later)
sits at the **bottom left** of its item, and all of them share **one
horizontal line** along the bottom of the header row. The rules live in
`assets/css/templates/header.css`:

- The shortcut keeps its item's left edge but is measured vertically against
  `.site-header`: `top: auto; bottom: 1.15rem` (the row's `padding-block`),
  dropping to `bottom: 1rem` at 1024px and below to match.
- The pencil itself is lifted with `top: -21px` on the shortcut's `button`.
- Nothing between an item and `.site-header` may be positioned, or its
  shortcut measures against that instead. The main menu is set to
  `position: static` only while its shortcut is present.
- Core prepends the shortcut inside the item. Items that centre their
  content (the header button) need `justify-content: flex-start` so the
  shortcut lands at the left edge, and a `left` offset that clears the
  item's padding and border (`calc(-31px - 2rem)` for the button).
- To add a header item, add its selector to the `:is(...)` lists rather than
  writing separate offsets.

### Footer: above, core default

Footer shortcuts sit above the top left of each part, which is WordPress's
own placement. Leave them unstyled.

## Scope

This rule is about **CSS only**. PHP and JS in this theme are commented, and
those comments are wanted — they carry the reasoning behind non-obvious
decisions, which is exactly what CSS does not need.
