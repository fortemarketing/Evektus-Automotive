# Evektus

The standalone WordPress theme for Evektus Automotive.

Configure global colours and typography through Beaver's Customizer settings.
Template files support native Beaver Themer header, footer, and part locations.

## Brand

Colours and the type scale live in `assets/css/theme.css` as custom properties.

| Token              | Value     |
| ------------------ | --------- |
| `--primary`        | `#ff8400` |
| `--primary-dark`   | `#e67700` |
| `--secondary`      | `#161616` |
| `--light`          | `#ffffff` |
| `--dark`           | `#000000` |
| `--row-background` | `#f1f1f1` |

`--row-background` is the `.row-bg` Beaver Builder row background.

| Style       | Element / class         | Font            | Desktop size / line height |
| ----------- | ----------------------- | --------------- | -------------------------- |
| Head 1      | `h1`                    | Median 400      | 130 / 120px                |
| Head 2      | `h2`                    | Median 400      | 55 / 65px                  |
| Head 3      | `h3`                    | Inter 500       | 34 / 50px                  |
| Subhead     | `h4`, `.subhead`        | Median 400      | 22 / 32px                  |
| Paragraph 1 | `.paragraph-1`, `.text-lead` | Inter 400  | 20 / 35px                  |
| Paragraph 2 | `body`                  | Inter 400       | 18 / 27px                  |
| Button      | `.fm-button`            | Resonate 600, uppercase | 14 / 14px          |
| Navigation  | `.main-menu__top-link`  | Inter 500       | 16px / auto                |

Sizes scale fluidly from a 360px viewport up to the desktop size at 1440px,
which is also the width of the site container (`--content-max`).

### Fonts

Median is not a Google font, so the theme ships it in `assets/fonts/median/`.
`theme.json` declares its `@font-face`, which WordPress prints on the front
end (Beaver Builder included) and in the block editor; `editor.css` repeats it
for the classic editor. `evek_builder_system_fonts()` lists it in Beaver
Builder's font picker. Inter is a Google font and is loaded by Beaver Builder.

## Layout

```
404.php, index.php,       Page templates - pages have no theme container, so
page.php, single.php      Beaver Builder controls width and spacing
header.php, footer.php    Site chrome, or the Beaver Themer layout when one is assigned
style.css                 Theme header only - holds no rules
theme.json                Block editor settings - appearance tools, layout and fonts
assets/
  css/
    base.css              Reset, typography, accessibility, layout primitives
    classes.css           Reusable utility classes
    theme.css             Brand palette and type scale as custom properties
    inc/                  One stylesheet per component, mirroring inc/
      accordion.css         Accordion chrome shared by the accordion shortcodes
      branding.css          Site logo and placeholder crest
      builder-placeholder.css  Beaver Builder placeholders
      main-menu.css         Mega menu and mobile drill-down
      scroll-top.css        Back-to-top button
      shortcodes/         One stylesheet per shortcode, mirroring inc/shortcodes/
    templates/            One stylesheet per page template at the theme root
      content.css           index.php, single.php, 404.php
      footer.css            footer.php
      header.css            header.php
    custom.css            Site-specific styles spanning more than one template
    editor.css            Classic editor only - Median, body margin and colour, as Astra had them
  fonts/
    median/               Median Regular, woff2 and woff
  js/
    inc/                  One script per component, mirroring inc/
      accordion.js          Opens and closes every .evek-accordion panel
      main-menu.js          Mega menu and mobile drawer
      scroll-top.js         Back-to-top button
      shortcodes/         One script per shortcode, mirroring inc/shortcodes/
    templates/            One script per page template at the theme root
    custom.js             Site-specific scripts spanning more than one template
inc/
  accordion.php           Shared accordion toggle icon - evek_accordion_icon()
  branding.php            Site logo and placeholder crest - evek_site_branding()
  builder-placeholder.php  Beaver Builder placeholders for empty shortcodes
  customizer.php          Customizer settings - dark header logo, header
                          button, footer acknowledgement
  main-menu.php           Mega menu renderer - evek_main_menu()
  scroll-top.php          Back-to-top button
  theme-options.php       Per-post Theme Options panel - header selection
  uploads.php             Minimum image size check (450 x 350) - currently off
  shortcodes/             One file per shortcode - all shortcodes live here
fl-builder/
  modules/                The theme's own Beaver Builder modules, one folder each
```

Beaver Builder only loads a theme module whose folder is registered through
`fl_builder_load_modules_paths`. `evek_builder_module_paths()` registers every
folder in `fl-builder/modules/`, so a new module is a matter of dropping its
folder in. Name the folder and its main file after the module's slug.

Shortcodes are loaded by globbing `inc/shortcodes/`, so adding one is a matter
of dropping the file in - `functions.php` needs no edit. Each one renders
nothing when the data behind it is missing, and each registers a thin wrapper
around its renderer so that empty case becomes a labelled placeholder while
Beaver Builder is open - see `evek_builder_placeholder()`. A new shortcode gets
the same treatment by calling it in the same place. Their styles and
scripts follow the template convention below, named after the shortcode.

Accordions share `accordion.css` and `accordion.js`, which bind to the
`.evek-accordion` markup rather than to any one shortcode. A new accordion only
needs a stylesheet for whatever goes inside its panels. Two classes on the
wrapper decide how it behaves, and the script reads them at click time:
`evek-accordion-collapse` keeps one panel open at most, and
`evek-accordion-always-open` keeps one open at least.

Under `assets/css/` and `assets/js/`, a file's folder mirrors where its PHP
lives: `inc/` for a component in `inc/`, `inc/shortcodes/` for a shortcode, and
`templates/` for a page template at the theme root. Name it after that PHP
file. To add one, drop the file in - `evek_enqueue_assets()` discovers all three
folders automatically, so no PHP change is needed.

## Load order

```
base.css -> classes.css -> theme.css -> style.css -> inc/*.css
  -> templates/*.css -> inc/shortcodes/*.css -> custom.css
```

Within each folder stylesheets load alphabetically, so the cascade stays
deterministic as files are added. Components load before page templates because
the header and footer restyle them in place, and shortcodes load after the
accordion chrome they build on. `custom.css` loads last and can override
anything above it. The order is defined once, in `evek_css_files()`.

On the front end those stylesheets arrive as a single bundle,
`uploads/evek-cache/theme-{hash}.css`, concatenated in that order with comments
and whitespace stripped - one render-blocking request instead of twenty. The
hash covers every file's path and modification time, so editing, adding or
removing a stylesheet builds a new bundle on the next request; there is no cache
to clear. Superseded bundles are deleted by a daily cron once they are a day
old, so pages still held in a page cache keep their styles until purged.

Each stylesheet is enqueued on its own instead, chained in the same order, when
`SCRIPT_DEBUG` is on (so dev tools name the file a rule came from), when the
bundle cannot be written to uploads, or when the `evek_bundle_css` filter
returns false. The editor's stylesheets are unaffected.

Scripts are not bundled. Each uses its modification time as its cache version.

Template scripts are self-contained and carry no dependencies on one another.

## Main menu

`evek_main_menu()` renders a nav menu as a mega menu on desktop (>= 992px) and
an off-canvas drill-down on mobile.

A **group** is a heading plus its links, and a **column** can stack several
groups. Nothing needs configuring in the menu editor: a level 2 item with
children is a heading, and one without children is a plain link.

```
About Us                    level 0  top level item
  -                         level 1  column
    History                 level 2  has children -> heading
      Our History           level 3  link
      Archives              level 3  link
    About Us                level 2  no children -> plain link
    Join the Team           level 2  no children -> plain link
```

Add another level 1 item for each additional column. Consecutive plain links
share a single list, so they read as loose links rather than separate groups.

Until a menu is assigned to the Primary location, a placeholder tree renders so
the menu is visible and testable.

## Theme Options

Every public post type gets a **Theme Options** panel in the editor sidebar,
holding a single **Header** control. Like any meta box it can be hidden per
user from Screen Options (classic editor) or Preferences > Advanced panels
(block editor).

| Choice        | Renders                                                                 |
| ------------- | ----------------------------------------------------------------------- |
| Theme Default | Whatever `evek_default_header_style()` returns - `light` unless filtered |
| Light         | White type, for a dark hero behind the header                           |
| Dark          | Dark type and the full colour logo, for a light hero                    |

Both headers overlay the hero: the markup, layout and position are identical,
and the only difference is colour. The choice becomes a `.site-header--{style}`
modifier and a `body.has-{style}-header` class, and each variant block in
`templates/header.css` re-points just `--header-fg`, which the rest of that
file reads, so a new header needs no new markup. The main
menu's top level and the hamburger follow `--header-fg` too, since they sit in
the header over the hero. The mobile off-canvas panel is the exception: it has
its own primary background, so everything inside it keeps the light palette set in
`inc/main-menu.css` whichever header is in use.

The one exception is the logo. The full colour mark is its own file rather than
a recolour, so the Dark header uses **Appearance > Customize > Site Identity >
Dark header logo**, falling back to the Site Identity logo when that slot is
empty.

To add a header, add an entry to `evek_header_styles()` in
`inc/theme-options.php` and a matching `.site-header--{key}` block in
`templates/header.css`.

Single posts are pinned to the Dark header by `evek_dark_header_for_posts()`
in `inc/theme-options.php`. It hooks `evek_header_style`, which runs after the
post meta is read, so it overrides the Theme Options choice on a post rather
than defaulting it. Remove that `add_filter()` to give the choice back.

Filters: `evek_header_styles` (available headers),
`evek_default_header_style` (site fallback), `evek_header_style` (final
per-request override) and `evek_theme_options_post_types` (where the panel
appears).
