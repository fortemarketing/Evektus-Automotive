/**
 * Theme Counter
 *
 * Counts a counter's number up from zero, and fills its circle, semicircle or
 * bar to match, once the counter scrolls into view. Each instance is started
 * from includes/frontend.js.php with its settings.
 *
 * A counter inside a closed tab, accordion or modal has no box, so the observer
 * only fires once it is opened - no per-container hooks are needed.
 */
(function () {
  "use strict";

  // jQuery's "swing", which the plugin's counter animated with.
  function swing(progress) {
    return 0.5 - Math.cos(progress * Math.PI) / 2;
  }

  function addCommas(value) {
    var parts = value.split(".");

    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
  }

  function ThemeCounter(settings) {
    this.settings = settings;
    this.el = document.querySelector(
      ".fl-node-" + settings.id + " .theme-counter",
    );

    if (!this.el || this.el.hasAttribute("data-counted")) {
      return;
    }

    this.int = this.el.querySelector(".theme-counter-number-int");
    this.fill = this.el.querySelector(".theme-counter-fill");
    this.bar = this.el.querySelector(".theme-counter-bar");
    this.observe();
  }

  ThemeCounter.prototype = {
    observe: function () {
      var self = this,
        observer;

      if (!("IntersectionObserver" in window)) {
        self.start();
        return;
      }

      observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            observer.disconnect();
            self.start();
          }
        });
      });
      observer.observe(self.el);
    },

    start: function () {
      var self = this,
        reduced =
          window.matchMedia &&
          window.matchMedia("(prefers-reduced-motion: reduce)").matches;

      self.el.setAttribute("data-counted", "");

      if (reduced) {
        self.render(1);
        return;
      }

      window.setTimeout(function () {
        self.animate();
      }, self.settings.delay);
    },

    animate: function () {
      var self = this,
        speed = self.settings.speed,
        begin = null;

      if (speed <= 0) {
        self.render(1);
        return;
      }

      function step(now) {
        var progress;

        if (null === begin) {
          begin = now;
        }

        progress = Math.min((now - begin) / speed, 1);
        self.render(swing(progress));

        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      }

      window.requestAnimationFrame(step);
    },

    // How full the circle or bar is at the end of the count, from 0 to 1.
    ratio: function () {
      var settings = this.settings,
        ratio =
          "percent" === settings.type
            ? settings.number / 100
            : settings.number / settings.max;

      return Math.max(0, Math.min(1, ratio || 0));
    },

    render: function (eased) {
      var settings = this.settings,
        filled = this.ratio() * eased * 100;

      if (this.int) {
        this.int.textContent = this.format(settings.number * eased);
      }

      if (this.fill) {
        this.fill.style.strokeDashoffset = 100 - filled;
      }

      if (this.bar) {
        this.bar.style.width = filled + "%";
      }
    },

    format: function (value) {
      var settings = this.settings,
        digits = settings.decimals;

      if ("locale" === settings.numberFormat) {
        try {
          return value.toLocaleString(settings.locale, {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
          });
        } catch (e) {
          return value.toLocaleString(undefined, {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
          });
        }
      }

      if ("none" === settings.numberFormat) {
        return value.toFixed(digits);
      }

      return addCommas(value.toFixed(digits));
    },
  };

  window.ThemeCounter = ThemeCounter;
})();
