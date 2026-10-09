(() => {
  const toggle = document.getElementById("menu-toggle");
  const nav = document.getElementById("nav-utama");

  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      const open = nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(open));
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && nav.classList.contains("open")) {
        nav.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
      }
    });
    nav.addEventListener("click", (e) => {
      const t = e.target;
      if (t instanceof HTMLAnchorElement && nav.classList.contains("open")) {
        nav.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  const root = document.querySelector("[data-berita]");
  if (root) {
    const loading = root.querySelector('[data-state="loading"]');
    const list = root.querySelector('[data-state="list"]');
    const empty = root.querySelector('[data-state="empty"]');
    const error = root.querySelector('[data-state="error"]');
    const show = (name) => {
      for (const el of [loading, list, empty, error]) {
        if (el) el.hidden = el.getAttribute("data-state") !== name;
      }
    };
    const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    try {
      show("loading");
      const done = () => show("empty");
      if (reduce) {
        done();
      } else {
        window.setTimeout(done, 400);
      }
    } catch {
      show("error");
    }
  }
})();
