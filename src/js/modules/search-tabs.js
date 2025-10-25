export class SearchTabs {
  constructor(selector = ".search") {
    this.roots = document.querySelectorAll(selector);
    if (!this.roots.length) return;
    this.roots.forEach((root) => this.#bind(root));
  }

  #bind(root) {
    const tabs = root.querySelectorAll(".search-tabs__tab");
    const panels = root.querySelectorAll(".search-results");

    if (!tabs.length || !panels.length) return;

    const activate = (targetId) => {
      // タブ: アクティブ状態と aria-selected を切り替え
      tabs.forEach((btn) => {
        const isActive = btn.getAttribute("data-tab-target") === targetId;
        btn.classList.toggle("is-active", isActive);
        btn.setAttribute("aria-selected", isActive ? "true" : "false");
      });

      // パネル: 表示クラスと `hidden` 属性を切り替え（アクセシビリティ対応）
      panels.forEach((panel) => {
        const isActive = panel.id === targetId;
        panel.classList.toggle("is-active", isActive);
        if (isActive) {
          panel.removeAttribute("hidden");
        } else {
          panel.setAttribute("hidden", "");
        }
      });
    };

    // クリックで対象パネルをアクティブ化
    tabs.forEach((btn) => {
      btn.addEventListener("click", () => {
        const targetId = btn.getAttribute("data-tab-target");
        if (targetId) activate(targetId);
      });
    });

    // 初期アクティブ化: 事前に `.is-active` があるものを優先、なければ最初のタブ
    const preset =
      Array.from(tabs).find((b) => b.classList.contains("is-active")) ||
      tabs[0];
    if (preset) {
      const targetId = preset.getAttribute("data-tab-target");
      if (targetId) activate(targetId);
    }
  }
}
