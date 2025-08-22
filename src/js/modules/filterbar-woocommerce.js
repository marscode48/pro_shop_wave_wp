// ---------------------------------
// WooCommerce用フィルターバーの挙動
// - PC: ドロップダウン開閉
// - SP: モーダル開閉
// - 親→子カテゴリの同期
// - タグ/ブランドの選択（ブランドは 親=メーカー→子=車種→孫=型式 の3階層）
// - 適用ボタンでURLパラメータを更新（product_cat / product_tag / product_brand or pa_brand）
// - 初期状態は URL から復元
// ---------------------------------

export class FilterbarWooCommerce {
  constructor(options = {}) {
    this.root = document;
    this.state = {
      // category
      catParent: "",
      catChild: "",
      // tag
      tag: "",
      tagLabel: "", // 人間向け表示用ラベル（例: "Tシャツ"）
      // brand (3階層)
      brandParent: "", // メーカー
      brandModel: "", // 車種
      brandChassis: "", // 型式
    };

    this.selectors = {
      openDropdownBtn: ".js-open-dropdown",
      dropdown: ".filterbar__dropdown",
      panel: ".filterbar__panel",
      pillCat: "#pill-cat",
      pillTag: "#pill-tag",
      pillBrand: "#pill-brand",
      // PC groups (category)
      pcCatParent: "#pc-cat-parent",
      pcCatChildrenPrefix: "#pc-cat-children-", // + parent slug
      // PC groups (tag)
      pcTagPopular: "#pc-tag-popular",
      pcTagResults: "#pc-tag-results",
      pcTagSearch: "#pc-tag-search",
      pcTagSelected: "#pc-tag-selected",
      // PC groups (brand)
      pcBrandMaker: "#pc-brand-maker",
      pcBrandModelBoxes: '[id^="pc-brand-model-"]',
      pcBrandChassisBoxes: '[id^="pc-brand-chassis-"]',
      // SP groups
      modal: "#filterbar-modal",
      spCatParent: "#sp-cat-parent",
      spCatChildren: "#sp-cat-children",
      spTagSearch: "#sp-tag-search",
      spTagPopular: "#sp-tag-popular",
      spTagSelected: "#sp-tag-selected",
      spBrandMaker: "#sp-brand-maker",
      spBrandModelBoxes: '[id^="sp-brand-model-"]',
      spBrandChassisBoxes: '[id^="sp-brand-chassis-"]',
      // Actions
      applyBtns: ".js-apply",
      resetCatBtn: ".js-reset",
      resetTagBtn: ".js-reset-tag",
      resetBrandBtn: ".js-reset-brand",
      spResetBtn: "#sp-reset",
      spApplyBtn: "#sp-apply",
    };

    // dataset 取得
    this.datasetEl = document.getElementById("filterbar-dataset");
    this.brandTax = this.datasetEl?.dataset.brandTax || ""; // product_brand or pa_brand or ""

    // category children map
    this.childrenMap = {};
    try {
      this.childrenMap = JSON.parse(this.datasetEl?.dataset.children || "{}");
    } catch (e) {
      this.childrenMap = {};
    }

    // brand hierarchy payload
    this.brandData = { parents: [], models: {}, chassis: {} };
    try {
      this.brandData = JSON.parse(this.datasetEl?.dataset.brand || "{}") || {
        parents: [],
        models: {},
        chassis: {},
      };
    } catch (e) {
      this.brandData = { parents: [], models: {}, chassis: {} };
    }

    // 逆引きインデックス（model -> parent, chassis -> model）と ラベル辞書（slug -> label）
    this.brandIndex = {
      labelBySlug: {},
      parentByModel: {},
      modelByChassis: {},
    };
    this._buildBrandIndexes();

    // タグ: スラッグ -> ラベル
    this.tagLabelBySlug = {};

    // 初期状態復元
    this._initFromURL();

    // バインド
    this._bindPC();
    this._bindSP();
    this._bindCommon();

    // URL直叩き時の補完
    this._inferTagLabelFromState();

    // 表示初期化
    this._renderBrandVisibilityPC();
    this._renderBrandVisibilitySP();
    this._renderPills();
  }

  // =====================
  // URL <-> state
  // =====================
  _initFromURL() {
    const a = new URL(window.location.href);
    const q = a.searchParams;
    // category: product_cat（子優先）
    const qCat = q.get("product_cat") || "";
    this.state.catChild = qCat || "";

    // tag
    this.state.tag = q.get("product_tag") || "";

    // brand: brandTax が存在する場合のみ
    if (this.brandTax) {
      const qBrand = q.get(this.brandTax) || "";
      if (qBrand) {
        this._initBrandFromSlug(qBrand);
      }
    }
  }

  _applyToURL() {
    const url = new URL(window.location.href);
    const sp = url.searchParams;

    // category（子→親の優先）
    const catParam = this.state.catChild || this.state.catParent || "";
    if (catParam) sp.set("product_cat", catParam);
    else sp.delete("product_cat");

    // tag
    if (this.state.tag) sp.set("product_tag", this.state.tag);
    else sp.delete("product_tag");

    // brand（型式→車種→メーカー の優先で1つだけ）
    if (this.brandTax) {
      const b = this._deriveBrandParam();
      if (b) sp.set(this.brandTax, b);
      else sp.delete(this.brandTax);
    }

    // ページングをリセット
    sp.delete("paged");

    window.location.assign(url.toString());
  }

  // =====================
  // brand helpers
  // =====================

  _toArray(val) {
    if (Array.isArray(val)) return val;
    if (val && typeof val === "object") return Object.values(val);
    return [];
  }

  _buildBrandIndexes() {
    // 親リストを配列化
    const parents = this._toArray(this.brandData.parents || []);
    parents.forEach((p) => {
      this.brandIndex.labelBySlug[p.value] = p.label;

      // 親→子（models）を配列化して走査
      const models = this._toArray(this.brandData.models?.[p.value]);
      models.forEach((m) => {
        this.brandIndex.labelBySlug[m.value] = m.label;
        this.brandIndex.parentByModel[m.value] = p.value;

        // 子→孫（chassis）を配列化して走査
        const chassis = this._toArray(this.brandData.chassis?.[m.value]);
        chassis.forEach((c) => {
          this.brandIndex.labelBySlug[c.value] = c.label;
          this.brandIndex.modelByChassis[c.value] = m.value;
        });
      });
    });
  }

  _initBrandFromSlug(slug) {
    if (!slug) return;
    // slug が孫なら model->parent を辿る
    const model = this.brandIndex.modelByChassis[slug];
    if (model) {
      this.state.brandChassis = slug;
      this.state.brandModel = model;
      this.state.brandParent = this.brandIndex.parentByModel[model] || "";
      return;
    }
    // slug が子なら parent をセット
    const parent = this.brandIndex.parentByModel[slug];
    if (parent) {
      this.state.brandChassis = "";
      this.state.brandModel = slug;
      this.state.brandParent = parent;
      return;
    }
    // slug が親ならそれをセット
    const isParent = (this.brandData.parents || []).some(
      (p) => p.value === slug
    );
    if (isParent) {
      this.state.brandChassis = "";
      this.state.brandModel = "";
      this.state.brandParent = slug;
    }
  }

  _deriveBrandParam() {
    return (
      this.state.brandChassis ||
      this.state.brandModel ||
      this.state.brandParent ||
      ""
    );
  }

  _brandLabel() {
    const slug = this._deriveBrandParam();
    return this.brandIndex.labelBySlug[slug] || "All";
  }

  _resetBrand() {
    this.state.brandParent = "";
    this.state.brandModel = "";
    this.state.brandChassis = "";
    // 視覚リセット
    this._selectBrandChips("");
    this._renderBrandVisibilityPC();
    this._renderBrandVisibilitySP();
  }

  _selectBrandChips(activeSlug) {
    // ブランドドロップダウン内の全チップの選択表示を更新
    const groups = [
      this.selectors.pcBrandMaker,
      this.selectors.pcBrandModelBoxes,
      this.selectors.pcBrandChassisBoxes,
      this.selectors.spBrandMaker,
      this.selectors.spBrandModelBoxes,
      this.selectors.spBrandChassisBoxes,
    ];
    groups.forEach((sel) => {
      this.$$(sel + " .filterbar__chip").forEach((chip) => {
        chip.dataset.selected =
          chip.dataset.value === activeSlug ? "true" : "false";
      });
    });
  }

  _renderBrandVisibilityPC() {
    // モデルグループ（親別）
    this.$$(this.selectors.pcBrandModelBoxes).forEach((box) => {
      const parent = box.getAttribute("data-parent");
      box.style.display =
        !this.state.brandParent || parent === this.state.brandParent
          ? "grid"
          : "none";
    });
    // 型式グループ（モデル別）
    this.$$(this.selectors.pcBrandChassisBoxes).forEach((box) => {
      const model = box.getAttribute("data-model");
      box.style.display =
        !this.state.brandModel || model === this.state.brandModel
          ? "grid"
          : "none";
    });
    // 選択マーク
    const active = this._deriveBrandParam();
    this._selectBrandChips(active);
  }

  _renderBrandVisibilitySP() {
    // モデル（親別）
    this.$$(this.selectors.spBrandModelBoxes).forEach((box) => {
      const parent = box.getAttribute("data-parent");
      box.style.display =
        !this.state.brandParent || parent === this.state.brandParent
          ? "grid"
          : "none";
    });
    // 型式（モデル別）
    this.$$(this.selectors.spBrandChassisBoxes).forEach((box) => {
      const model = box.getAttribute("data-model");
      box.style.display =
        !this.state.brandModel || model === this.state.brandModel
          ? "grid"
          : "none";
    });
    // 選択マーク（SP側）
    this._selectChip(this.selectors.spBrandMaker, this.state.brandParent);
    this.$$(this.selectors.spBrandModelBoxes).forEach((box) => {
      this._selectChip("#" + box.id, this.state.brandModel);
    });
    this.$$(this.selectors.spBrandChassisBoxes).forEach((box) => {
      this._selectChip("#" + box.id, this.state.brandChassis);
    });
  }

  // =====================
  // UI helpers
  // =====================
  $(sel, ctx = this.root) {
    return ctx.querySelector(sel);
  }
  $$(sel, ctx = this.root) {
    return Array.from(ctx.querySelectorAll(sel));
  }

  _renderPills() {
    const catText = this.state.catChild || this.state.catParent || "All";
    const tagText =
      this.state.tagLabel ||
      (this.state.tag ? decodeURIComponent(this.state.tag) : "All");
    const brandText = this._brandLabel();
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

  // =====================
  // PC bindings
  // =====================
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
        const slug = chip.dataset.value;
        const label = chip.dataset.label || chip.textContent.trim();
        this.tagLabelBySlug[slug] = label;
        chip.addEventListener("click", () => {
          this.state.tag = slug;
          this.state.tagLabel =
            this.tagLabelBySlug[slug] || decodeURIComponent(slug);
          this._selectChip(this.selectors.pcTagPopular, this.state.tag);
          this._renderTagSelected();
          this._renderPills();
        });
      }
    );

    // タグ検索（簡易）
    const search = this.$(this.selectors.pcTagSearch);
    if (search) {
      search.addEventListener("input", (e) => {
        const q = e.target.value.toLowerCase().trim();
        // 候補は人気タグ辞書から
        const pool = Object.entries(this.tagLabelBySlug); // [[slug,label], ...]
        const hit = pool
          .filter(
            ([slug, label]) =>
              slug.includes(q) || label.toLowerCase().includes(q)
          )
          .slice(0, 10);
        const res = this.$(this.selectors.pcTagResults);
        if (!res) return;
        res.innerHTML = "";
        hit.forEach(([slug, label]) => {
          const b = document.createElement("button");
          b.className = "filterbar__chip";
          b.dataset.value = slug;
          b.textContent = label; // ラベル表示
          b.addEventListener("click", () => {
            this.state.tag = slug;
            this.state.tagLabel = label;
            this._renderTagSelected();
            this._renderPills();
          });
          res.appendChild(b);
        });
      });
    }

    // =====================
    // ブランド（PC）
    // =====================

    // メーカー（親）
    this.$$(this.selectors.pcBrandMaker + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          this.state.brandParent = chip.dataset.value;
          this.state.brandModel = "";
          this.state.brandChassis = "";
          this._renderBrandVisibilityPC();
          this._renderPills();
        });
      }
    );

    // 車種（子）
    this.$$(this.selectors.pcBrandModelBoxes + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          const model = chip.dataset.value;
          this.state.brandModel = model;
          this.state.brandChassis = "";
          // 親は逆引きで確定
          this.state.brandParent =
            this.brandIndex.parentByModel[model] || this.state.brandParent;
          this._renderBrandVisibilityPC();
          this._renderPills();
        });
      }
    );

    // 型式（孫）
    this.$$(this.selectors.pcBrandChassisBoxes + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          const chassis = chip.dataset.value;
          this.state.brandChassis = chassis;
          // 関連モデル/親を逆引き
          const model = this.brandIndex.modelByChassis[chassis];
          if (model) {
            this.state.brandModel = model;
            this.state.brandParent =
              this.brandIndex.parentByModel[model] || this.state.brandParent;
          }
          this._renderBrandVisibilityPC();
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
        this.state.tagLabel = "";
        this._selectChip(this.selectors.pcTagPopular, "");
        this._selectChip(this.selectors.spTagPopular, ""); // SP側もリセット
        const box = this.$(this.selectors.pcTagSelected);
        if (box) box.innerHTML = "";
        this._renderPills();
      })
    );
    this.$$(this.selectors.resetBrandBtn).forEach((b) =>
      b.addEventListener("click", () => {
        this._resetBrand();
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
      const label =
        this.state.tagLabel ||
        this.tagLabelBySlug[this.state.tag] ||
        decodeURIComponent(this.state.tag);
      const b = document.createElement("button");
      b.className = "filterbar__chip";
      b.dataset.selected = "true";
      b.textContent = `${label} ×`;
      b.addEventListener("click", () => {
        this.state.tag = "";
        this.state.tagLabel = "";
        this._renderTagSelected();
        this._renderPills();
      });
      box.appendChild(b);
    }
  }

  // =====================
  // SP bindings
  // =====================
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
        const pool = Object.entries(this.tagLabelBySlug);
        const hit = pool
          .filter(
            ([slug, label]) =>
              slug.includes(q) || label.toLowerCase().includes(q)
          )
          .slice(0, 10);
        const box = this.$(this.selectors.spTagSelected);
        if (!box) return;
        box.innerHTML = "";
        hit.forEach(([slug, label]) => {
          const b = document.createElement("button");
          b.className = "filterbar__chip";
          b.dataset.value = slug;
          b.textContent = label; // ラベル表示
          b.addEventListener("click", () => {
            this.state.tag = slug;
            this.state.tagLabel = label;
            this._selectChip(this.selectors.spTagPopular, this.state.tag); // 人気タグ側の選択も同期
            this._renderPills();
          });
          box.appendChild(b);
        });
      });
    }
    this.$$(this.selectors.spTagPopular + " .filterbar__chip").forEach(
      (chip) => {
        const slug = chip.dataset.value;
        const label = chip.dataset.label || chip.textContent.trim();
        this.tagLabelBySlug[slug] = this.tagLabelBySlug[slug] || label;
        chip.addEventListener("click", () => {
          this.state.tag = slug;
          this.state.tagLabel =
            this.tagLabelBySlug[slug] || decodeURIComponent(slug);
          this._selectChip(this.selectors.spTagPopular, this.state.tag); // 選択表示
          this._renderPills();
        });
      }
    );

    // ブランド（SP）
    // メーカー（親）
    this.$$(this.selectors.spBrandMaker + " .filterbar__chip").forEach((chip) =>
      chip.addEventListener("click", () => {
        this.state.brandParent = chip.dataset.value;
        this.state.brandModel = "";
        this.state.brandChassis = "";
        this._renderBrandVisibilitySP();
        this._selectBrandChips(this._deriveBrandParam());
        this._renderPills();
      })
    );

    // 車種（子）
    this.$$(this.selectors.spBrandModelBoxes + " .filterbar__chip").forEach(
      (chip) =>
        chip.addEventListener("click", () => {
          const model = chip.dataset.value;
          this.state.brandModel = model;
          this.state.brandChassis = "";
          this.state.brandParent =
            this.brandIndex.parentByModel[model] || this.state.brandParent;
          this._renderBrandVisibilitySP();
          this._selectBrandChips(this._deriveBrandParam());
          this._renderPills();
        })
    );

    // 型式（孫）
    this.$$(this.selectors.spBrandChassisBoxes + " .filterbar__chip").forEach(
      (chip) =>
        chip.addEventListener("click", () => {
          const chassis = chip.dataset.value;
          this.state.brandChassis = chassis;
          const model = this.brandIndex.modelByChassis[chassis];
          if (model) {
            this.state.brandModel = model;
            this.state.brandParent =
              this.brandIndex.parentByModel[model] || this.state.brandParent;
          }
          this._renderBrandVisibilitySP();
          this._selectBrandChips(this._deriveBrandParam());
          this._renderPills();
        })
    );

    // フッター
    this.$(this.selectors.spResetBtn)?.addEventListener("click", () => {
      this.state = {
        ...this.state,
        catParent: "",
        catChild: "",
        tag: "",
        tagLabel: "",
        brandParent: "",
        brandModel: "",
        brandChassis: "",
      };
      this._selectChip(this.selectors.spTagPopular, ""); // SP人気タグの選択解除
      this._renderSpChildren();
      this._renderBrandVisibilitySP();
      this._renderPills();
    });
    this.$(this.selectors.spApplyBtn)?.addEventListener("click", () => {
      const modal = this.$(this.selectors.modal);
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

    // ★ オブジェクトでも配列でも安全に forEach できるよう配列化
    const list = this._toArray(this.childrenMap?.[this.state.catParent]);

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

  // =====================
  // common (初期選択の視覚反映など)
  // =====================
  _bindCommon() {
    // 初期選択（カテゴリ）
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

    // 初期選択（タグ）
    if (this.state.tag) {
      this._selectChip(this.selectors.pcTagPopular, this.state.tag);
      this._selectChip(this.selectors.spTagPopular, this.state.tag); // SP 人気タグにも選択反映
    }
    this._inferTagLabelFromState();

    // 初期選択（ブランド）
    this._renderBrandVisibilityPC();
    this._renderBrandVisibilitySP();
  }

  // タグ: URL直叩き時のラベル補完
  _inferTagLabelFromState() {
    if (this.state.tag && !this.state.tagLabel) {
      this.state.tagLabel =
        this.tagLabelBySlug[this.state.tag] ||
        decodeURIComponent(this.state.tag);
    }
  }
}
