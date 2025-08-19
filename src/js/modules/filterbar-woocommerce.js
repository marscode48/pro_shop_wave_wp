// ---------------------------------
// WooCommerce用フィルターバーの挙動
// - PC: ドロップダウン開閉
// - SP: モーダル開閉
// - 親→子カテゴリの同期
// - タグ/ブランドの選択
// - 適用ボタンでURLパラメータを更新（product_cat / product_tag / product_brand or pa_brand）
// - 初期状態は URL から復元
// ---------------------------------

export class FilterbarWooCommerce {
  constructor(options = {}) {
    this.root = document;
    this.state = {
      catParent: "",
      catChild: "",
      tag: "",
      brand: "",
    };
    this.selectors = {
      openDropdownBtn: ".js-open-dropdown",
      dropdown: ".filterbar__dropdown",
      panel: ".filterbar__panel",
      pillCat: "#pill-cat",
      pillTag: "#pill-tag",
      pillBrand: "#pill-brand",
      // PC groups
      pcCatParent: "#pc-cat-parent",
      pcCatChildrenPrefix: "#pc-cat-children-", // + parent slug
      pcTagPopular: "#pc-tag-popular",
      pcTagResults: "#pc-tag-results",
      pcTagSearch: "#pc-tag-search",
      pcTagSelected: "#pc-tag-selected",
      pcBrandMaker: "#pc-brand-maker",
      // SP groups
      modal: "#filterbar-modal",
      spCatParent: "#sp-cat-parent",
      spCatChildren: "#sp-cat-children",
      spTagSearch: "#sp-tag-search",
      spTagPopular: "#sp-tag-popular",
      spTagSelected: "#sp-tag-selected",
      spBrandMaker: "#sp-brand-maker",
      // Actions
      applyBtns: ".js-apply",
      resetCatBtn: ".js-reset",
      resetTagBtn: ".js-reset-tag",
      resetBrandBtn: ".js-reset-brand",
      spResetBtn: "#sp-reset",
      spApplyBtn: "#sp-apply",
    };

    this.datasetEl = document.getElementById("filterbar-dataset");
    this.brandTax = this.datasetEl?.dataset.brandTax || "";
    this.childrenMap = {};
    try {
      this.childrenMap = JSON.parse(this.datasetEl?.dataset.children || "{}");
    } catch (e) {
      this.childrenMap = {};
    }

    this._initFromURL();
    this._bindPC();
    this._bindSP();
    this._bindCommon();
    this._renderPills();
  }

  // ===== URL <-> state =====
  _initFromURL() {
    const a = new URL(window.location.href);
    const q = a.searchParams;
    // カテゴリは product_cat を共通キーに
    const qCat = q.get("product_cat") || "";
    // 親か子かは不明なので、とりあえず子優先で適用
    this.state.catChild = qCat || "";
    // タグ
    this.state.tag = q.get("product_tag") || "";
    // ブランド（存在するタクソノミー名を採用）
    if (this.brandTax) {
      this.state.brand = q.get(this.brandTax) || "";
    }
  }

  _applyToURL() {
    const url = new URL(window.location.href);
    const sp = url.searchParams;

    // カテゴリ（子を優先。無ければ親。空なら削除）
    const catParam = this.state.catChild || this.state.catParent || "";
    if (catParam) sp.set("product_cat", catParam);
    else sp.delete("product_cat");

    // タグ
    if (this.state.tag) sp.set("product_tag", this.state.tag);
    else sp.delete("product_tag");

    // ブランド
    if (this.brandTax) {
      if (this.state.brand) sp.set(this.brandTax, this.state.brand);
      else sp.delete(this.brandTax);
    }

    // ページングをリセット
    sp.delete("paged");

    window.location.assign(url.toString());
  }

  // ===== UI helpers =====
  $(sel, ctx = this.root) {
    return ctx.querySelector(sel);
  }
  $$(sel, ctx = this.root) {
    return Array.from(ctx.querySelectorAll(sel));
  }

  _renderPills() {
    const catText = this.state.catChild || this.state.catParent || "All";
    const tagText = this.state.tag || "All";
    const brandText = this.state.brand || "All";
    this.$(this.selectors.pillCat)?.replaceChildren(
      document.createTextNode(catText)
    );
    this.$(this.selectors.pillTag)?.replaceChildren(
      document.createTextNode(tagText)
    );
    this.$(this.selectors.pillBrand)?.replaceChildren(
      document.createTextNode(brandText)
    );
  }

  _selectChip(groupSel, value) {
    this.$$(groupSel + " .filterbar__chip").forEach((c) => {
      c.dataset.selected = c.dataset.value === value ? "true" : "false";
    });
  }

  _closeAllDropdowns() {
    this.$$(this.selectors.dropdown).forEach((el) =>
      el.classList.remove("is-open")
    );
    this.$$(this.selectors.openDropdownBtn).forEach((b) =>
      b.setAttribute("aria-expanded", "false")
    );
  }

  // ===== PC bindings =====
  _bindPC() {
    // ドロップダウン開閉
    this.$$(this.selectors.openDropdownBtn).forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-target");
        const dd = document.getElementById(id);
        if (!dd) return;
        const willOpen = !dd.classList.contains("is-open");
        this._closeAllDropdowns();
        if (willOpen) {
          dd.classList.add("is-open");
          btn.setAttribute("aria-expanded", "true");
        }
      });
    });

    // 外側クリックで閉じる
    document.addEventListener("click", (e) => {
      const hit = e.target.closest(
        ".filterbar__dropdown, " + this.selectors.openDropdownBtn
      );
      if (!hit) this._closeAllDropdowns();
    });
    // Escで閉じる
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") this._closeAllDropdowns();
    });

    // 親カテゴリ
    this.$$(this.selectors.pcCatParent + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          this.state.catParent = chip.dataset.value;
          this.state.catChild = "";
          this._selectChip(this.selectors.pcCatParent, this.state.catParent);
          // 子のビジュアル（両方存在する前提で opacity 切替）
          Object.keys(this.childrenMap).forEach((parentSlug) => {
            const box = this.$(this.selectors.pcCatChildrenPrefix + parentSlug);
            if (box)
              box.style.opacity =
                parentSlug === this.state.catParent ? 1 : 0.35;
          });
          this._renderPills();
        });
      }
    );

    // 子カテゴリ（全親分まとめて拾う）
    this.$$(
      this.selectors.dropdown + " .filterbar__options .filterbar__chip"
    ).forEach((chip) => {
      const inCatChildren =
        chip.parentElement?.id?.startsWith("pc-cat-children-");
      if (!inCatChildren) return;
      chip.addEventListener("click", () => {
        this.state.catChild = chip.dataset.value;
        // すべての子グループから選択同期
        Object.keys(this.childrenMap).forEach((parentSlug) => {
          this._selectChip(
            this.selectors.pcCatChildrenPrefix + parentSlug,
            this.state.catChild
          );
        });
        this._renderPills();
      });
    });

    // タグ（人気）
    this.$$(this.selectors.pcTagPopular + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          this.state.tag = chip.dataset.value;
          this._selectChip(this.selectors.pcTagPopular, this.state.tag);
          this._renderTagSelected();
          this._renderPills();
        });
      }
    );

    // タグ検索（簡易：候補をその場で生成）
    const search = this.$(this.selectors.pcTagSearch);
    if (search) {
      search.addEventListener("input", (e) => {
        const q = e.target.value.toLowerCase().trim();
        const pool = this.$$(
          this.selectors.pcTagPopular + " .filterbar__chip"
        ).map((b) => b.dataset.value);
        const hit = Array.from(
          new Set(pool.filter((t) => t.includes(q)))
        ).slice(0, 10);
        const res = this.$(this.selectors.pcTagResults);
        if (!res) return;
        res.innerHTML = "";
        hit.forEach((v) => {
          const b = document.createElement("button");
          b.className = "filterbar__chip";
          b.dataset.value = v;
          b.textContent = v;
          b.addEventListener("click", () => {
            this.state.tag = v;
            this._renderTagSelected();
            this._renderPills();
          });
          res.appendChild(b);
        });
      });
    }

    // ブランド
    this.$$(this.selectors.pcBrandMaker + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          this.state.brand = chip.dataset.value;
          this._selectChip(this.selectors.pcBrandMaker, this.state.brand);
          this._renderPills();
        });
      }
    );

    // リセット群
    this.$$(this.selectors.resetCatBtn).forEach((b) =>
      b.addEventListener("click", () => {
        this.state.catParent = "";
        this.state.catChild = "";
        this._selectChip(this.selectors.pcCatParent, "");
        Object.keys(this.childrenMap).forEach((parentSlug) =>
          this._selectChip(this.selectors.pcCatChildrenPrefix + parentSlug, "")
        );
        this._renderPills();
      })
    );
    this.$$(this.selectors.resetTagBtn).forEach((b) =>
      b.addEventListener("click", () => {
        this.state.tag = "";
        this._selectChip(this.selectors.pcTagPopular, "");
        const box = this.$(this.selectors.pcTagSelected);
        if (box) box.innerHTML = "";
        this._renderPills();
      })
    );
    this.$$(this.selectors.resetBrandBtn).forEach((b) =>
      b.addEventListener("click", () => {
        this.state.brand = "";
        this._selectChip(this.selectors.pcBrandMaker, "");
        this._renderPills();
      })
    );

    // 適用
    this.$$(this.selectors.applyBtns).forEach((b) =>
      b.addEventListener("click", () => {
        this._closeAllDropdowns();
        this._renderPills();
        this._applyToURL();
      })
    );
  }

  _renderTagSelected() {
    const box = this.$(this.selectors.pcTagSelected);
    if (!box) return;
    box.innerHTML = "";
    if (this.state.tag) {
      const b = document.createElement("button");
      b.className = "filterbar__chip";
      b.dataset.selected = "true";
      b.textContent = `${this.state.tag} ×`;
      b.addEventListener("click", () => {
        this.state.tag = "";
        this._renderTagSelected();
        this._renderPills();
      });
      box.appendChild(b);
    }
  }

  // ===== SP bindings =====
  _bindSP() {
    const modal = this.$(this.selectors.modal);
    const openBtn = document.querySelector(".filterbar__open");
    const closeBtns = this.$$(".js-close-modal");

    if (openBtn && modal) {
      openBtn.addEventListener("click", () => modal.classList.add("is-open"));
      closeBtns.forEach((b) =>
        b.addEventListener("click", () => modal.classList.remove("is-open"))
      );
    }

    // 親カテゴリ（SP）
    this.$$(this.selectors.spCatParent + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          this.state.catParent = chip.dataset.value;
          this.state.catChild = "";
          this._selectChip(this.selectors.spCatParent, this.state.catParent);
          this._renderSpChildren();
          this._renderPills();
        });
      }
    );

    // タグ（SP）
    const spTagSearch = this.$(this.selectors.spTagSearch);
    if (spTagSearch) {
      spTagSearch.addEventListener("input", (e) => {
        const q = e.target.value.toLowerCase().trim();
        const pool = this.$$(
          this.selectors.spTagPopular + " .filterbar__chip"
        ).map((b) => b.dataset.value);
        const hit = Array.from(
          new Set(pool.filter((t) => t.includes(q)))
        ).slice(0, 10);
        const box = this.$(this.selectors.spTagSelected);
        if (!box) return;
        box.innerHTML = "";
        hit.forEach((v) => {
          const b = document.createElement("button");
          b.className = "filterbar__chip";
          b.textContent = v;
          b.addEventListener("click", () => {
            this.state.tag = v;
            this._renderPills();
          });
          box.appendChild(b);
        });
      });
    }
    this.$$(this.selectors.spTagPopular + " .filterbar__chip").forEach((chip) =>
      chip.addEventListener("click", () => {
        this.state.tag = chip.dataset.value;
        this._renderPills();
      })
    );

    // ブランド（SP）
    this.$$(this.selectors.spBrandMaker + " .filterbar__chip").forEach((chip) =>
      chip.addEventListener("click", () => {
        this.state.brand = chip.dataset.value;
        this._renderPills();
      })
    );

    // フッター
    this.$(this.selectors.spResetBtn)?.addEventListener("click", () => {
      this.state = { catParent: "", catChild: "", tag: "", brand: "" };
      this._renderSpChildren();
      this._renderPills();
    });
    this.$(this.selectors.spApplyBtn)?.addEventListener("click", () => {
      modal?.classList.remove("is-open");
      this._renderPills();
      this._applyToURL();
    });

    // 初期レンダリング
    this._renderSpChildren();
  }

  _renderSpChildren() {
    const box = this.$(this.selectors.spCatChildren);
    if (!box) return;
    box.innerHTML = "";
    const list = this.childrenMap[this.state.catParent] || [];
    list.forEach((item) => {
      const b = document.createElement("button");
      b.className = "filterbar__chip";
      b.dataset.value = item.value;
      b.textContent = item.label;
      if (this.state.catChild === item.value) b.dataset.selected = "true";
      b.addEventListener("click", () => {
        this.state.catChild = item.value;
        this._renderPills();
        this._renderSpChildren();
      });
      box.appendChild(b);
    });
  }

  // ===== common =====
  _bindCommon() {
    // 初期選択の視覚反映
    if (this.state.catParent)
      this._selectChip(this.selectors.pcCatParent, this.state.catParent);
    if (this.state.catChild) {
      Object.keys(this.childrenMap).forEach((parentSlug) =>
        this._selectChip(
          this.selectors.pcCatChildrenPrefix + parentSlug,
          this.state.catChild
        )
      );
    }
    if (this.state.tag)
      this._selectChip(this.selectors.pcTagPopular, this.state.tag);
    if (this.state.brand)
      this._selectChip(this.selectors.pcBrandMaker, this.state.brand);
  }
}
