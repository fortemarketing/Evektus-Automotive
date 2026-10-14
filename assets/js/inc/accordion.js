// Accordion - opens and closes the panels rendered by the shortcodes in
// inc/shortcodes/. It binds to the markup, not to any one shortcode, so every
// accordion built from the .evek-accordion classes is driven from here.
//
// The fm-accordion module this mirrors uses jQuery slideUp/slideDown, which
// animate height and vertical padding together over 400ms. That is reproduced
// here with a CSS transition driven from script, so the timing matches without
// pulling jQuery into a theme that has none.
(function () {
  "use strict";

  var DURATION_CLASS = "is-animating";
  var ACTIVE_CLASS = "evek-accordion-item-active";
  var ITEM_SELECTOR = ":scope > .evek-accordion-item." + ACTIVE_CLASS;

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  // Clear everything the animation put on the panel so it can reflow freely
  // once open - a panel left at a fixed height would clip content that grows.
  function settle(content) {
    content.classList.remove(DURATION_CLASS);
    content.style.height = "";
    content.style.paddingTop = "";
    content.style.paddingBottom = "";
  }

  function animate(content, open) {
    var item = content.closest(".evek-accordion-item");

    if (reduceMotion.matches) {
      item.classList.toggle(ACTIVE_CLASS, open);
      settle(content);
      return;
    }

    // Any in-flight animation on this panel is abandoned first, otherwise a
    // fast second click would measure a height that is still moving.
    settle(content);

    var padding = window.getComputedStyle(content).paddingTop;

    content.classList.add(DURATION_CLASS);

    if (open) {
      content.style.height = "0px";
      content.style.paddingTop = "0px";
      content.style.paddingBottom = "0px";
      item.classList.add(ACTIVE_CLASS);
    } else {
      content.style.height = content.scrollHeight + "px";
      content.style.paddingTop = padding;
      content.style.paddingBottom = padding;
    }

    // Read the layout back so the browser registers the starting state before
    // the next assignment moves it, otherwise the transition is skipped.
    void content.offsetHeight;

    if (open) {
      // scrollHeight is measured with the padding collapsed, so the resting
      // padding has to be added back on to get the final height.
      content.style.height = content.scrollHeight + parseFloat(padding) * 2 + "px";
      content.style.paddingTop = padding;
      content.style.paddingBottom = padding;
    } else {
      item.classList.remove(ACTIVE_CLASS);
      content.style.height = "0px";
      content.style.paddingTop = "0px";
      content.style.paddingBottom = "0px";
    }
  }

  function isOpen(item) {
    return item.classList.contains(ACTIVE_CLASS);
  }

  function setState(button, content, open) {
    button.setAttribute("aria-expanded", open ? "true" : "false");
    animate(content, open);
  }

  function toggle(accordion, button) {
    var item = button.closest(".evek-accordion-item");
    var content = document.getElementById(button.getAttribute("aria-controls"));

    if (!item || !content) {
      return;
    }

    var open = !isOpen(item);

    // With always-open set, the last open panel stays open - clicking its own
    // heading does nothing rather than leaving the accordion showing nothing.
    // Counting the open panels rather than assuming one means this still holds
    // when the accordion is not also single-open.
    if (
      !open &&
      accordion.classList.contains("evek-accordion-always-open") &&
      accordion.querySelectorAll(ITEM_SELECTOR).length < 2
    ) {
      return;
    }

    // The collapse class on the wrapper is what makes the accordion single
    // open, read here at click time rather than held as configuration - the
    // same arrangement as the module.
    if (open && accordion.classList.contains("evek-accordion-collapse")) {
      accordion.querySelectorAll(ITEM_SELECTOR).forEach(function (other) {
        if (other === item) {
          return;
        }

        var otherButton = other.querySelector(".evek-accordion-button");
        var otherContent = document.getElementById(otherButton.getAttribute("aria-controls"));

        if (otherContent) {
          setState(otherButton, otherContent, false);
        }
      });
    }

    setState(button, content, open);
  }

  function init(root) {
    var scope = root || document;

    scope.querySelectorAll(".evek-accordion").forEach(function (accordion) {
      if (accordion.dataset.evekAccordionReady === "true") {
        return;
      }

      accordion.dataset.evekAccordionReady = "true";

      // One delegated listener per accordion. The button is a real button, so
      // Enter and Space arrive as clicks and need no key handling of their own.
      accordion.addEventListener("click", function (event) {
        var button = event.target.closest(".evek-accordion-button");

        // Only buttons belonging to this accordion, so a nested one inside a
        // panel stays under its own control.
        if (!button || button.closest(".evek-accordion") !== accordion) {
          return;
        }

        toggle(accordion, button);
      });

      accordion.addEventListener("transitionend", function (event) {
        if (event.propertyName !== "height" || !event.target.classList.contains("evek-accordion-content")) {
          return;
        }

        settle(event.target);
      });
    });
  }

  // Open or close an item on the spot, with no slide. Used on arrival from a
  // link, where there is nothing to watch move - only a page to read.
  function setImmediately(item, open) {
    var button = item.querySelector(":scope > .evek-accordion-heading > .evek-accordion-button");
    var content = button && document.getElementById(button.getAttribute("aria-controls"));

    if (!content) {
      return;
    }

    button.setAttribute("aria-expanded", open ? "true" : "false");
    item.classList.toggle(ACTIVE_CLASS, open);
    settle(content);
  }

  // Open the item a link points at - #faq-opening-hours, from another
  // page - so a visitor lands on that item rather than on a
  // closed panel, or on whichever item happens to open first. Anything in the
  // hash that is not an accordion item is left for the browser to handle.
  function openFromHash() {
    var id = window.location.hash.slice(1);
    var item;

    if (!id) {
      return;
    }

    // WordPress stores a slug with non-Latin characters percent-encoded, so
    // an id can legitimately contain "%e2" and must be looked up as written.
    // Only if that misses is the decoded form tried.
    item = document.getElementById(id);

    if (!item) {
      try {
        item = document.getElementById(decodeURIComponent(id));
      } catch (error) {
        // A malformed escape in someone else's link is not worth an error.
        return;
      }
    }

    if (!item || !item.classList.contains("evek-accordion-item")) {
      return;
    }

    var accordion = item.closest(".evek-accordion");

    if (!accordion) {
      return;
    }

    // A single-open accordion shows only the linked item, which also closes
    // the first item if it was opened by default.
    if (accordion.classList.contains("evek-accordion-collapse")) {
      accordion.querySelectorAll(ITEM_SELECTOR).forEach(function (other) {
        if (other !== item) {
          setImmediately(other, false);
        }
      });
    }

    setImmediately(item, true);

    // The browser scrolled to the item before this ran, and closing the panel
    // above it has since moved it up the page. Scroll again once the new
    // layout is in place.
    window.requestAnimationFrame(function () {
      item.scrollIntoView({ block: "start" });
    });
  }

  // Exposed so a Beaver Builder preview can re-initialise the accordion after
  // it replaces the DOM, the same way the main menu does. The old name is kept
  // as an alias in case a builder setting somewhere still calls it.
  window.evekAccordion = { init: init };
  window.evekDirectoryAccordion = window.evekAccordion;

  function start() {
    init();
    openFromHash();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start);
  } else {
    start();
  }

  // A link to another item on the same page changes only the hash, which
  // does not reload it.
  window.addEventListener("hashchange", openFromHash);
})();
