// Site-specific scripts that do not belong to a single template.

// The header overlays the hero (see templates/header.css), so the hero has to
// pad itself by the header's height. That height changes with the logo, the
// breakpoints and font loading, so it is measured rather than hard coded and
// published as --site-header-height on :root.
(function () {
  "use strict";

  var header = document.querySelector(".site-header");

  if (!header) {
    return;
  }

  function publish() {
    document.documentElement.style.setProperty("--site-header-height", header.offsetHeight + "px");
  }

  publish();

  if ("ResizeObserver" in window) {
    new ResizeObserver(publish).observe(header);
  } else {
    window.addEventListener("resize", publish);
  }
})();
