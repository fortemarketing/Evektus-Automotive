// Scroll animations - reveals each [data-reveal] node from inc/animations.php
// as it scrolls into view. Nodes that arrive together are staggered in page
// order, and a stagger node staggers its own cards or list items.
(function () {
  "use strict";

  const root = document.documentElement;

  // The head script in inc/animations.php decides whether animations run.
  if (!root.classList.contains("evek-motion")) {
    return;
  }

  window.evekAnimationsReady = true;

  // Seconds between nodes that arrive together, and between a stagger node's
  // children. Kept short so a full row never feels slow.
  const STEP = 0.1;
  const CHILD_STEP = 0.08;
  const MAX_DELAY = 0.5;

  const nodes = Array.from(document.querySelectorAll("[data-reveal]"));

  if (!nodes.length) {
    return;
  }

  function reveal(node, delay) {
    node.style.setProperty("--reveal-delay", delay + "s");

    if ("stagger" === node.dataset.reveal) {
      node
        .querySelectorAll(".fm-posts__item, .fm-list-icon-item")
        .forEach(function (child, index) {
          child.style.setProperty(
            "--reveal-delay",
            delay + Math.min(index * CHILD_STEP, MAX_DELAY) + "s",
          );
        });
    }

    node.classList.add("is-revealed");
  }

  const observer = new IntersectionObserver(
    function (entries) {
      const arriving = entries
        .filter(function (entry) {
          return entry.isIntersecting;
        })
        .map(function (entry) {
          return entry.target;
        })
        .sort(function (a, b) {
          return nodes.indexOf(a) - nodes.indexOf(b);
        });

      arriving.forEach(function (node, index) {
        observer.unobserve(node);
        reveal(node, Math.min(index * STEP, MAX_DELAY));
      });
    },
    // Reveal a little before a node is fully in view, once it has cleared the
    // bottom tenth of the screen.
    { rootMargin: "0px 0px -10% 0px", threshold: 0 },
  );

  nodes.forEach(function (node) {
    observer.observe(node);
  });
})();
