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

`assets/css/inc/scroll-top.css` is the reference for what this should look
like.

## Scope

This rule is about **CSS only**. PHP and JS in this theme are commented, and
those comments are wanted - they carry the reasoning behind non-obvious
decisions, which is exactly what CSS does not need.
