# Evektus

A barebones standalone WordPress theme for Evektus Automotive. It includes
the Evektus framework directly and does not require a parent theme.

Set the brand palette, fonts and type scale in `assets/css/theme.css`. Template
files support native Beaver Themer header, footer, and part locations; the
header and footer in `header.php` and `footer.php` are only fallbacks, shown
wherever no Themer layout is assigned.

## Asset layout

```
assets/
  css/
    base.css              Reset, accessibility, layout primitives
    theme.css             Palette, fonts, type scale and button - the brand tokens
    inc/                  One stylesheet per component, mirroring inc/
      branding.css          Site logo and site title
      builder-placeholder.css  Beaver Builder placeholders
      main-menu.css         Mega menu and mobile drill-down
      scroll-top.css        Back-to-top button
      shortcodes/         One stylesheet per shortcode, mirroring inc/shortcodes/
    templates/            One stylesheet per page template at the theme root
      content.css           index.php, single.php, 404.php
      footer.css            footer.php
      header.css            header.php
    custom.css            Site-specific styles spanning more than one template
    editor.css            Classic editor only
  js/
    inc/                  One script per component, mirroring inc/
      main-menu.js          Mega menu and mobile drawer
      scroll-top.js         Back-to-top button
      shortcodes/         One script per shortcode, mirroring inc/shortcodes/
    templates/            One script per page template at the theme root
    custom.js             Site-specific scripts spanning more than one template
inc/
  branding.php            Site logo, or the site title - evek_site_branding()
  builder-placeholder.php  Beaver Builder placeholders for empty shortcodes
  main-menu.php           Mega menu renderer - evek_main_menu()
  scroll-top.php          Back-to-top button
  shortcodes/             One file per shortcode - all shortcodes live here
```

Under `assets/css/` and `assets/js/`, a file's folder mirrors where its PHP
lives: `inc/` for a component in `inc/`, `inc/shortcodes/` for a shortcode, and
`templates/` for a page template at the theme root. Name it after that PHP
file. To add one, drop the file in - `evek_enqueue_assets()` discovers all
three folders automatically, so no PHP change is needed.

## Load order

```
base.css -> theme.css -> style.css -> inc/*.css -> templates/*.css
  -> inc/shortcodes/*.css -> custom.css
```

Within each folder stylesheets load alphabetically, each chained to the
previous one so the cascade stays deterministic as files are added. Components
load before page templates because the header and footer restyle them in place,
and shortcodes load last so they can build on any component. `custom.css` loads
last of all and can override anything above it. Every local asset uses its
modification time as its cache version.

`builder-placeholder.css` is only loaded while Beaver Builder is open, since
its placeholders never show to visitors. List other builder-only stylesheets
in `evek_builder_only_styles()`.

Template scripts are self-contained and carry no dependencies on one another.

## Shortcodes

Shortcodes are loaded by globbing `inc/shortcodes/`, so adding one is a matter
of dropping the file in - `functions.php` needs no edit. Their styles and
scripts go in `assets/css/inc/shortcodes/` and `assets/js/inc/shortcodes/`,
named after the shortcode's PHP file.

A shortcode should render nothing when the data behind it is missing. Inside
Beaver Builder that leaves a module with no height that cannot be selected, so
return `evek_builder_placeholder()` from the empty case instead - it renders a
labelled box while the builder is open and an empty string everywhere else.

## Beaver Themer

The theme declares support for Themer headers, footers and parts, and
registers six hooks as Part positions:

| Group   | Hooks                                          |
| ------- | ---------------------------------------------- |
| Header  | `evek_before_header`, `evek_after_header`   |
| Content | `evek_before_content`, `evek_after_content` |
| Footer  | `evek_before_footer`, `evek_after_footer`   |

Saved Beaver Builder templates (`fl-builder-template`) render without the site
header and footer, since they are fragments rather than pages. Add post types
to that list with the `evek_chromeless_post_types` filter, or decide per view
with `evek_hides_site_chrome`.

## Header

The fallback header in `header.php` is a dark bar with the logo on the left,
the primary menu centred and a "Chat with us" button on the right. The button
links to `/contact-us/` by default; change its `label` or `url` with the
`evek_header_cta` filter, or return an empty `url` to hide it. It is hidden
below 480px, where the hamburger needs the room.

## Footer

The fallback footer in `footer.php` is a dark band. On the left are the
business address, phone, email and social icons; beside them is the Footer
menu; and below is a bar with the copyright, the Privacy Policy link (when one
is set under Settings > Privacy) and the Forte Marketing credit.

Change the business details with the `evek_footer_contact` filter. It takes
`address` (an array of lines), `phone`, `email` and `social` (`facebook` and
`instagram` URLs). An empty value hides that line or icon.

The Footer menu is one flat menu, flowed into balanced columns that fill down
before across: three from 768px, two below that.

## Animations

Beaver Builder rows, columns and modules can animate in as they scroll into
view (`inc/animations.php`, with `animations.css` and `animations.js` in the
`inc/` asset folders). A node opts in by its node ID in
`evek_animation_nodes()`, mapped to one of these effects:

| Effect    | Result                                                   |
| --------- | -------------------------------------------------------- |
| `up`      | Fades in while rising a short way                        |
| `fade`    | Fades in on the spot                                     |
| `zoom`    | Background photo or colour settles from a slight zoom    |
| `stagger` | Its FM Posts cards or FM List Icon items rise one by one |

Nodes that arrive together are staggered in page order. Nothing is hidden in
the builder, for visitors who prefer reduced motion, or if the script fails to
load.

## Menus

Two locations, `primary` and `footer`. The primary menu renders through the
main menu below. The footer renders its menu one level deep, and prints nothing
until a menu is assigned.

## Main menu

`evek_main_menu()` renders a nav menu as a mega menu on desktop (>= 992px) and
an off-canvas drill-down on mobile.

A **group** is a heading plus its links, and a **column** can stack several
groups. Nothing needs configuring in the menu editor: a level 2 item with
children is a heading, and one without children is a plain link.

```
About                       level 0  top level item
  -                         level 1  column
    Company                 level 2  has children -> heading
      Our Story             level 3  link
      Our Team              level 3  link
    Careers                 level 2  no children -> plain link
    Testimonials            level 2  no children -> plain link
```

Add another level 1 item for each additional column. Consecutive plain links
share a single list, so they read as loose links rather than separate groups.
A top level item with no children is a plain link with no panel.

Until a menu is assigned to the Primary location, a placeholder tree renders so
the menu is visible and testable.

The desktop top level and the mobile hamburger take their colour from
`--header-fg`, set on `.site-header` in `templates/header.css`. The mega panel
and the off-canvas panel sit on `--primary` with light text, so they stay the
same whatever the header looks like.

Two optional arguments move the mega panel: `mega_selector` takes a CSS
selector whose box sets the panel's width and left edge (default: the full
page width), and `mega_selector_vertical` one whose bottom edge sets its top
(default: the bottom of the header).

The menu registers its own Customizer partial, so it gets a pencil shortcut in
the preview. `evek_render_main_menu_partial()` re-renders it, so keep its
arguments in step with the call in `header.php`.
