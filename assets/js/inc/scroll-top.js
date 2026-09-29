// Back to top - shows the button from inc/scroll-top.php once the page has
// scrolled a screen or so, and returns to the top when it is pressed.
(function () {
  "use strict";

  const button = document.querySelector(".scroll-top");

  if (!button) {
    return;
  }

  // Roughly one screen down, so the button never covers content the reader
  // can still see without scrolling.
  const OFFSET = 600;

  function update() {
    const show = window.scrollY > OFFSET;

    // hidden is removed first so the class change can animate; putting it
    // back is deferred until the fade out has finished.
    if (show) {
      button.hidden = false;
      // Read back the layout so the browser registers the starting state
      // before the class flips it, otherwise the transition is skipped.
      void button.offsetWidth;
    }

    button.classList.toggle("is-visible", show);
  }

  button.addEventListener("transitionend", function (event) {
    if (event.propertyName === "opacity" && !button.classList.contains("is-visible")) {
      button.hidden = true;
    }
  });

  button.addEventListener("click", function () {
    // No behavior option: base.css sets scroll-behavior: smooth only when the
    // reader has no reduced motion preference, so this follows it.
    window.scrollTo({ top: 0 });
  });

  // Passive: this only reads scrollY, so it must never block scrolling.
  window.addEventListener("scroll", update, { passive: true });

  update();
})();
