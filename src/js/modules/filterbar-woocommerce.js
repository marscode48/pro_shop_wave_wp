// ---------------------------------
// WooCommerce用フィルターバーの挙動
// - PC: ドロップダウン開閉
// - SP: モーダル開閉
// - 親→子カテゴリの同期
// - タグ/ブランドの選択（ブランドは 親=メーカー→子=車種→孫=型式 の3階層）
// - 適用ボタンでURLパラメータを更新（product_cat / product_tag / product_brand or pa_brand）
// - 初期状態は URL から復元

// 理解のコツ
// •	データはPHPから data- にJSONで注入* → JSが JSON.parse で読んで描画。
// •	state が唯一の真実：UIは常に state をもとに再描画。
// •	URLは常に1値/1軸：カテゴリは子優先で1つ、タグは1つ、ブランドは型式>車種>メーカーで1つ。
// •	PCはドロップダウン、SPはモーダルだが、中身のロジックは同じ（可視領域を切り替えるだけ）。
// ---------------------------------

export class FilterbarWooCommerce {
  constructor(options = {}) {
    this.root = document;
    this.state = {
      // カテゴリ
      catParent: "",
      catChild: "",
      // タグ
      tag: "",
      tagLabel: "", // 人間向け表示用ラベル（例: "Tシャツ"）
      // ブランド (3階層)
      brandParent: "", // メーカー
      brandModel: "", // 車種
      brandChassis: "", // 型式
    };

    // 必要なDOMセレクタをまとめる
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

    // dataset 取得（#filterbar-dataset から JSON形式のデータ属性を取得）
    this.datasetEl = document.getElementById("filterbar-dataset");

    // ブランド用タクソノミー名（文字列）datasetElからdata-brand-taxを取得
    // ?.（オプショナルチェーン） null だった場合でも、エラーにならずに undefined を返す
    //  || "" 値が undefined や空の場合のフォールバックで空文字をセット
    this.brandTax = this.datasetEl?.dataset.brandTax || "";

    // カテゴリの親→子マップ（JSON文字列をオブジェクトに）datasetElからdata-childrenを取得
    this.childrenMap = {};
    try {
      this.childrenMap = JSON.parse(this.datasetEl?.dataset.children || "{}");
    } catch (e) {
      this.childrenMap = {};
    }

    // ブランド3階層データ（parents/models/chassis）（JSON文字列をオブジェクトに）datasetElからdata-brandを取得
    // parents（親）はメーカー一覧をそのまま並べて表示したいので、配列 [] を使用
    // models（子）やchassis（孫）は「選択値で分岐表示」するため、キー（親slug/車種slug）で直アクセスできる形が効率的（キーで即参照 例：chassis["silvia"] ）なので、オブジェクト{}を使用
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

    // パス型アーカイブ用の現在スラッグ（PHPが data-* で注入）
    this.currentCatFromPath = this.datasetEl?.dataset.currentCat || "";
    this.currentBrandFromPath = this.datasetEl?.dataset.currentBrand || "";
    this.currentTagFromPath = this.datasetEl?.dataset.currentTag || "";

    // 逆引きインデックス（model -> parent, chassis -> model）と ラベル辞書（slug -> label）
    // 選択された slug から、親子関係や表示ラベルを即座に逆引きできる辞書をつくる
    // 型式だけが分かっても車種・メーカーまで一瞬で辿れるようにする
    this.brandIndex = {
      // 任意の slug → 表示名（人間可読用のピルやボタン表示用）
      labelBySlug: {},
      // 車種 slug → メーカー slug（親を即特定）
      parentByModel: {},
      // 型式 slug → 車種 slug（1段上を即特定）
      modelByChassis: {},
    };
    this._buildBrandIndexes();

    // タグ: スラッグ -> ラベル
    this.tagLabelBySlug = {};
    // カテゴリ: スラッグ -> ラベル（親/子とも）
    this.catLabelBySlug = {};
    this._buildCategoryLabelIndex();

    // URL から現在値を復元（初期状態復元）
    this._initFromURL();

    // クリック等のイベントを紐付け（バインド）
    this._bindPC();
    this._bindSP();
    this._bindCommon();

    // URL直叩き時の補完（タグのラベル補完）
    this._inferTagLabelFromState();

    // 表示初期化
    this._renderBrandVisibilityPC();
    this._renderBrandVisibilitySP();
    this._renderPills();
  }

  // =====================
  // URL <-> state
  // =====================

  //  URLのクエリパラメータ（?product_cat=… など）を読み取って、フィルターバーの state を初期化と復元をする処理
  // ページ読み込み時に一回だけ走る
  _initFromURL() {
    // 現在の URL をオブジェクト化（安全にクエリ編集するため）
    const a = new URL(window.location.href);

    // URLSearchParams インスタンスを取得（以降 q.get('key') で ?key=value を読める）
    const q = a.searchParams;

    // category: product_cat（子優先）
    // URLに ?product_cat=slug があれば =slug を取得、値が無ければ ""（空文字）
    const qCat = q.get("product_cat") || "";
    this.state.catChild = qCat || "";

    // Fallback: /product-category/... のようなパス型URLでクエリが無い場合
    if (!this.state.catChild && this.currentCatFromPath) {
      this.state.catChild = this.currentCatFromPath;
      // 親も分かるなら補完（childrenMap を走査してどの親配下かを特定）
      // childrenMap のすべての親スラッグ（"parts" / "apparel" など）を走査する
      for (const parentSlug of Object.keys(this.childrenMap || {})) {
        // その親に対応する子カテゴリリストを取得（配列に正規化）
        const list = this._toArray(this.childrenMap[parentSlug]);
        // そのリストの中に、state.catChild と一致する slug が含まれているか確認
        // some() は「条件を満たす要素が 1つでもあれば true」を返す
        if (list.some((it) => it && it.value === this.state.catChild)) {
          // 見つかった場合、その子が属する親カテゴリを state にセット
          this.state.catParent = parentSlug;
          break; // もう親が特定できたのでループを打ち切り
        }
      }
    }

    // tag
    // URLに ?product_tag=slug があれば =slug を取得、値が無ければ ""（空文字）
    this.state.tag = q.get("product_tag") || "";
    // Fallback: パス型URL等でクエリが無い場合に、サーバ側が埋めた currentTagFromPath から復元
    if (!this.state.tag && this.currentTagFromPath) {
      this.state.tag = this.currentTagFromPath;
    }

    // brand: brandTax が存在する場合のみ
    // URLに ?product_brand=slug があれば =slug を取得、値が無ければ ""（空文字）
    if (this.brandTax) {
      const qBrand = q.get(this.brandTax) || "";
      if (qBrand) {
        this._initBrandFromSlug(qBrand);
      }
      // Fallback: /brand/... などパス型URLでクエリが無い場合も、サーバ側が埋めた currentBrandFromPath から復元
      if (!qBrand && this.currentBrandFromPath) {
        this._initBrandFromSlug(this.currentBrandFromPath);
      }
    }
  }

  // 「適用」ボタンで現在の state を URL クエリへ正規化し、ページを再読込する
  // 方針: 「カテゴリ=1値」「タグ=1値」「ブランド=1値（型式>車種>メーカーの優先）」に統一し、未選択はパラメータを削除
  // 変更後はページング（paged）をクリアして 1 ページ目から再検索し、location.assign() で遷移する
  _applyToURL() {
    // 現在の URL をオブジェクト化（安全にクエリ編集するため）
    const url = new URL(window.location.href);
    // URLSearchParams で ?key=value を編集（set: 置換/正規化, delete: 完全削除）
    const sp = url.searchParams;

    // category（子優先→親→未選択）
    // 常に 1キー=1値（?product_cat=slug）に正規化。未選択なら削除。
    const catParam = this.state.catChild || this.state.catParent || "";
    if (catParam) sp.set("product_cat", catParam); // 例: ?product_cat=apparel
    else sp.delete("product_cat");

    // tag（1値に正規化。未選択なら削除）
    if (this.state.tag) sp.set("product_tag", this.state.tag);
    else sp.delete("product_tag");

    // brand（型式→車種→メーカーの優先で 1 値に正規化。タクソノミー名は brandTax=product_brand|pa_brand）
    if (this.brandTax) {
      const b = this._deriveBrandParam();
      if (b) sp.set(this.brandTax, b); // 例: ?product_brand=s15
      else sp.delete(this.brandTax);
    }

    // ページングをリセット（フィルタ変更後に古いページ番号が残るとヒット 0 になりがち）
    sp.delete("paged");

    // 生成した URL に遷移（履歴を残す）。履歴を残したくない場合は location.replace(...) を使用
    window.location.assign(url.toString());
  }

  // =====================
  // brand helpers
  // =====================

  // オブジェクトでも配列でも forEach 可能な配列に正規化（安全に回すため）
  _toArray(val) {
    // 引数がすでに配列なら、そのまま返す
    if (Array.isArray(val)) return val;
    // 	Object.values(obj) は、オブジェクトの値を 配列 にして返す
    if (val && typeof val === "object") return Object.values(val);
    return [];
  }

  // カテゴリ用の「slug → ラベル」辞書を構築
  // ---------------------------------
  // 取得元:
  //  - 親カテゴリ: PC/SP の親チップ（data-value=slug, textContent=ラベル）
  //  - 子カテゴリ: childrenMap[{ value, label }] から value→label を収集
  _buildCategoryLabelIndex() {
    const dict = {};

    // 親（PC）
    this.$$(this.selectors.pcCatParent + " .filterbar__chip").forEach((b) => {
      const slug = b.dataset.value;
      const label = (b.textContent || "").trim();
      if (slug) dict[slug] = label || dict[slug] || slug;
    });

    // 親（SP）
    this.$$(this.selectors.spCatParent + " .filterbar__chip").forEach((b) => {
      const slug = b.dataset.value;
      const label = (b.textContent || "").trim();
      if (slug && !dict[slug]) dict[slug] = label || slug;
    });

    // 子（childrenMap から）
    const map = this.childrenMap || {};
    Object.keys(map).forEach((parentSlug) => {
      const list = this._toArray(map[parentSlug]);
      list.forEach((item) => {
        if (!item) return;
        const slug = item.value;
        const label = item.label;
        if (slug) dict[slug] = label || dict[slug] || slug;
      });
    });

    this.catLabelBySlug = dict;
  }

  // ブランド3階層（メーカー → 車種 → 型式） の関係を「逆引きしやすいインデックス（辞書）」に作り直す関数
  _buildBrandIndexes() {
    // 親ブランド（メーカー）リストを配列化
    const parents = this._toArray(this.brandData.parents || []);

    // 	ループ内の p は 親ブランド（メーカー）オブジェクト 1件分
    parents.forEach((p) => {
      // labelBySlug に slug → ラベル（人間向け名称） を登録
      // 例: nissan => "NISSAN"
      this.brandIndex.labelBySlug[p.value] = p.label;

      // 親（メーカー）→子（models）を配列化して走査
      // models は今ループしているメーカー slug に対応する車種リスト
      const models = this._toArray(this.brandData.models?.[p.value]);
      models.forEach((m) => {
        // 「slug → 表示名」を登録して slug から人間向けの名前を一瞬で取り出せるようにする
        // 例: "silvia" → "Silvia"
        this.brandIndex.labelBySlug[m.value] = m.label;

        //「車種 slug → 親メーカー slug」の対応を登録して車種からメーカーを逆引きできるようにする
        // 例: "silvia" → "nissan"
        this.brandIndex.parentByModel[m.value] = p.value;

        // 子→孫（chassis）を配列化して走査
        // その車種に属する「型式リスト」を安全に取り出す
        // 例: "silvia" に対して [ {value: "s13", label: "S13"}, {value: "s14", label: "S14"} ]
        const chassis = this._toArray(this.brandData.chassis?.[m.value]);

        chassis.forEach((c) => {
          // "s13" → "S13" のように slug→表示名 を登録
          this.brandIndex.labelBySlug[c.value] = c.label;
          // "s13" → "silvia" のように 型式→車種 の逆引きを作成
          this.brandIndex.modelByChassis[c.value] = m.value;
        });
      });
    });
  }

  // URLやクリックなどで渡された slug が「型式(孫) / 車種(子) / メーカー(親)」のどれかを判定し、state を復元する
  _initBrandFromSlug(slug) {
    if (!slug) return;
    // URLやクリックなどで渡された slug が
    // 「型式(孫) / 車種(子) / メーカー(親)」のどれかを判定し、state を復元する。

    // 1) 型式(孫)の可能性をチェック：型式→車種 を逆引き
    //    見つかれば 型式=slug, 車種=model, 親メーカー=parent を一気に確定
    const model = this.brandIndex.modelByChassis[slug];
    if (model) {
      this.state.brandChassis = slug; // 型式をセット
      this.state.brandModel = model; // 逆引きで車種確定
      this.state.brandParent = this.brandIndex.parentByModel[model] || ""; // 逆引きで親メーカー確定
      return;
    }

    // 2) 車種(子)の可能性をチェック：車種→親メーカー を逆引き
    //    見つかれば 車種=slug, 親メーカー=parent を確定（型式は未選択にリセット）
    const parent = this.brandIndex.parentByModel[slug];
    if (parent) {
      this.state.brandChassis = ""; // 型式は未選択
      this.state.brandModel = slug; // 車種をセット
      this.state.brandParent = parent; // 親メーカーを確定
      return;
    }

    // 3) メーカー(親)の可能性をチェック：parents 配列に slug が含まれているか
    //    見つかれば メーカー=slug を確定（車種/型式は未選択にリセット）
    // 　　some() で配列の中に条件を満たす要素が “1つでもあるか” を真偽値で返す
    //    最初に条件を満たした時点で即座にループを打ち切る（ショートサーキット）
    const isParent = (this.brandData.parents || []).some(
      (p) => p.value === slug
    );
    if (isParent) {
      this.state.brandChassis = ""; // 型式は未選択
      this.state.brandModel = ""; // 車種は未選択
      this.state.brandParent = slug; // メーカーをセット
    }
  }

  // ブランド送出値を 1 つに正規化
  // ---------------------------------
  // 優先順位: 型式(孫) > 車種(子) > メーカー(親)
  // URL は 1キー=1値（?product_brand=xxxx など）に揃えたいので、
  // 最下層まで選ばれていればそれを優先し、無ければ上位へフォールバック。
  // `||` は「短絡評価（左から順に truthy を返す）」のため、
  // 先に見つかった値が即返され、以降は評価されない。
  _deriveBrandParam() {
    return (
      this.state.brandChassis || // 1) 型式があれば最優先
      this.state.brandModel || // 2) 無ければ車種
      this.state.brandParent || // 3) それも無ければメーカー
      "" // 4) 何も未選択なら空文字（クエリ削除用）
    );
  }

  // 優先順（型式→車種→メーカー）で選んだslugをラベル辞書から引いてピル表示用テキストにする
  _brandLabel() {
    const slug = this._deriveBrandParam();
    return this.brandIndex.labelBySlug[slug] || "All";
  }

  // ブランド選択のリセット
  // ---------------------------------
  // state 側の 3階層（メーカー=parent / 車種=model / 型式=chassis）をすべて未選択に戻し、
  // その状態に合わせて UI の選択表示や可視ブロックを再描画する。
  // - state: brandParent/brandModel/brandChassis を空文字に
  // - 視覚: 全ブランド関連チップの [data-selected] を解除
  // - レイアウト: PC/SP それぞれの可視グループを初期状態へ
  _resetBrand() {
    // 1) 内部状態のリセット
    this.state.brandParent = ""; // メーカー（親）
    this.state.brandModel = ""; // 車種（子）
    this.state.brandChassis = ""; // 型式（孫）

    // 2) 視覚リセット（チップの選択解除）
    // 空文字を渡すことで、全チップの data-selected が "false" になるように調整
    this._selectBrandChips("");

    // 3) 可視ブロックの再描画
    // 選択が無い状態に合わせて、PC/SP のモデル・型式の表示/非表示を初期化
    this._renderBrandVisibilityPC();
    this._renderBrandVisibilitySP();
  }

  // チップの [data-selected] を一括更新
  // ---------------------------------
  // activeSlug（現在の選択＝型式>車種>メーカーの優先で 1 値に正規化された slug）に基づき、
  // PC/SP すべてのブランド系チップの `data-selected` を "true"/"false" に更新する。
  // これにより CSS 側で `[data-selected="true"]` をスタイルフックとして利用できる。
  _selectBrandChips(activeSlug) {
    // 選択対象となるブランド領域（メーカー/車種/型式）のグループセレクタを列挙
    const groups = [
      this.selectors.pcBrandMaker, // PC: メーカー（親）
      this.selectors.pcBrandModelBoxes, // PC: 車種（子）コンテナ群
      this.selectors.pcBrandChassisBoxes, // PC: 型式（孫）コンテナ群
      this.selectors.spBrandMaker, // SP: メーカー（親）
      this.selectors.spBrandModelBoxes, // SP: 車種（子）コンテナ群
      this.selectors.spBrandChassisBoxes, // SP: 型式（孫）コンテナ群
    ];

    // 各グループ配下の .filterbar__chip を走査して、選択状態を同期
    groups.forEach((sel) => {
      this.$$(sel + " .filterbar__chip").forEach((chip) => {
        // chip の値（slug）が activeSlug と一致すれば選択状態、違えば非選択
        // dataset は文字列属性なので "true"/"false" を明示的にセット
        chip.dataset.selected =
          chip.dataset.value === activeSlug ? "true" : "false";
      });
    });
  }

  // ブランドの可視状態（PCドロップダウン）を state に合わせて更新
  // ----------------------------------------------------
  // ・メーカー(親)が未選択なら車種・型式は非表示。メーカー選択中のみ該当車種を表示
  // ・車種(子)が未選択なら型式は非表示。車種選択中のみ該当型式を表示
  // ・最後に現在の選択（型式>車種>メーカーの優先）に合わせて各チップの [data-selected] を更新
  // ・操作可否も state に合わせて制御
  _renderBrandVisibilityPC() {
    // 1) 車種（子）グループの表示制御（data-parent でメーカーを識別）
    this.$$(this.selectors.pcBrandModelBoxes).forEach((box) => {
      const parent = box.getAttribute("data-parent");
      box.style.display =
        this.state.brandParent && parent === this.state.brandParent
          ? "grid" // メーカー選択中 → 一致メーカーだけ表示
          : "none"; // 未選択 or 不一致 → 非表示
    });

    // 2) 型式（孫）グループの表示制御（data-model で車種を識別）
    this.$$(this.selectors.pcBrandChassisBoxes).forEach((box) => {
      const model = box.getAttribute("data-model");
      box.style.display =
        this.state.brandModel && model === this.state.brandModel
          ? "grid" // 車種選択中 → 一致モデルだけ表示
          : "none"; // 未選択 or 不一致 → 非表示
    });

    // 3) 選択状態の視覚反映（型式>車種>メーカーの優先で 1 値を導出）
    const active = this._deriveBrandParam();
    this._selectBrandChips(active);

    // 4) 操作可否（クリック/フォーカス）を state に合わせて制御
    this._setBrandInteractivity();
  }

  // ブランドの可視状態（SPモーダル）を state に合わせて更新
  // ----------------------------------------------------
  // ・メーカー(親)が未選択なら車種・型式は非表示。メーカー選択中のみ該当車種を表示
  // ・車種(子)が未選択なら型式は非表示。車種選択中のみ該当型式を表示
  // ・最後に、現在の state に合わせて SP 側のチップ選択状態（[data-selected]）を同期
  // ・操作可否も state に合わせて制御
  _renderBrandVisibilitySP() {
    // 1) 車種（子）グループの表示制御（data-parent でメーカーを識別）
    this.$$(this.selectors.spBrandModelBoxes).forEach((box) => {
      const parent = box.getAttribute("data-parent");
      box.style.display =
        this.state.brandParent && parent === this.state.brandParent
          ? "grid" // メーカー選択中 → 一致メーカーだけ表示
          : "none"; // 未選択 or 不一致 → 非表示
    });

    // 2) 型式（孫）グループの表示制御（data-model で車種を識別）
    this.$$(this.selectors.spBrandChassisBoxes).forEach((box) => {
      const model = box.getAttribute("data-model");
      box.style.display =
        this.state.brandModel && model === this.state.brandModel
          ? "grid" // 車種選択中 → 一致モデルだけ表示
          : "none"; // 未選択 or 不一致 → 非表示
    });

    // 3) 選択マーク（[data-selected]）の同期（SP側）
    this._selectChip(this.selectors.spBrandMaker, this.state.brandParent);
    this.$$(this.selectors.spBrandModelBoxes).forEach((box) => {
      this._selectChip("#" + box.id, this.state.brandModel);
    });
    this.$$(this.selectors.spBrandChassisBoxes).forEach((box) => {
      this._selectChip("#" + box.id, this.state.brandChassis);
    });

    // 4) 操作可否（クリック/フォーカス）を state に合わせて制御
    this._setBrandInteractivity();
  }

  // ブランド（モデル/型式）の操作可否をまとめて切り替える
  // - メーカー未選択: モデル/型式はクリック不可・フォーカス不可（aria-disabled="true"）
  // - 車種未選選択: 型式のみクリック不可・フォーカス不可
  _setBrandInteractivity() {
    // 車種（子）を有効化する条件：メーカーが選択されていること
    const modelEnabled = !!this.state.brandParent;
    this.$$(this.selectors.pcBrandModelBoxes + " .filterbar__chip, " + this.selectors.spBrandModelBoxes + " .filterbar__chip").forEach((chip) => {
      if (modelEnabled) {
        chip.style.pointerEvents = "auto";
        chip.tabIndex = 0;
        chip.setAttribute("aria-disabled", "false");
      } else {
        chip.style.pointerEvents = "none";
        chip.tabIndex = -1;
        chip.setAttribute("aria-disabled", "true");
      }
    });

    // 型式（孫）を有効化する条件：車種が選択されていること
    const chassisEnabled = !!this.state.brandModel;
    this.$$(this.selectors.pcBrandChassisBoxes + " .filterbar__chip, " + this.selectors.spBrandChassisBoxes + " .filterbar__chip").forEach((chip) => {
      if (chassisEnabled) {
        chip.style.pointerEvents = "auto";
        chip.tabIndex = 0;
        chip.setAttribute("aria-disabled", "false");
      } else {
        chip.style.pointerEvents = "none";
        chip.tabIndex = -1;
        chip.setAttribute("aria-disabled", "true");
      }
    });
  }

  // =====================
  // UI helpers
  // =====================

  // 単一要素取得ヘルパ
  // ---------------------------------
  // 目的: 指定セレクタに一致する最初の 1 要素のみを取得する（見つからなければ null）。
  // 引数:
  //  - sel: CSSセレクタ文字列（配列は不可）。
  //  - ctx: 検索コンテキスト（既定は this.root = document）。
  // 返り値: Element | null
  // 備考: 複数ヒットが想定される場合は $$ を使うこと（配列で返る）。
  $(sel, ctx = this.root) {
    return ctx.querySelector(sel);
  }

  // 複数要素取得ヘルパ
  // ---------------------------------
  // 目的: 指定セレクタに一致する全要素を配列として取得する（0 件なら空配列）。
  // 引数:
  //  - sel: CSSセレクタ文字列。
  //  - ctx: 検索コンテキスト（既定は this.root = document）。
  // 返り値: Element[]（Array.from で NodeList を配列化）
  // 備考:
  //  - Array.from により、forEach/map/filter など配列メソッドが利用可能。
  //  - `ctx.querySelectorAll(sel)` は 1 件も無ければ空の NodeList を返すため、
  //    そのまま配列化しても安全（forEach でエラーにならない）。
  $$(sel, ctx = this.root) {
    return Array.from(ctx.querySelectorAll(sel));
  }

  // ピル（上部の現在選択サマリ）を state に合わせて描画
  // ---------------------------------
  // ・カテゴリ: 子 > 親 の優先で 1 値に正規化し、未選択なら "All"
  // ・タグ: 人間可読ラベル（tagLabel）があればそれを表示。
  //         無ければ slug を decodeURIComponent して表示。未選択なら "All"
  // ・ブランド: _brandLabel()（型式>車種>メーカーの優先）で導出し表示
  // メモ: replaceChildren(Node) を使い、既存ノードを全差し替えして XSS を避けつつ安全にテキストを更新
  _renderPills() {
    // 1) 表示テキストの決定
    // decodeURIComponent は、URL の “クエリ値” などで使われるパーセントエンコード（%E3%81… 形式）を
    // 本来の文字列に戻すための関数（例）"t%e3%82%b7%e3%83%a3%e3%83%84" → "Tシャツ"）
    const catSlug = this.state.catChild || this.state.catParent || "";
    // this.catLabelBySlug?.[catSlug] で事前に作っておいた「slug → ラベル」辞書からラベルを引く
    // （例: "suspension" → "サスペンション"）
    const catText = catSlug
      ? this.catLabelBySlug?.[catSlug] || decodeURIComponent(catSlug)
      : "All";
    const tagText =
      this.state.tagLabel ||
      (this.state.tag ? decodeURIComponent(this.state.tag) : "All");
    const brandText = this._brandLabel();

    // 2) DOM描画（存在しない場合はオプショナルチェーンで無視）
    // replaceChildren() で要素の子ノードを全て削除し、渡したノードに丸ごと置き換え
    // createTextNode() でテキストノードを作成（HTMLとして解釈されない純テキスト）
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

  // 指定グループ内のチップの選択状態を一括同期
  // ---------------------------------
  // 第一引数 groupSel 配下の .filterbar__chip を走査し、第二引数のvalue（選ばれているslug）と一致する要素だけ
  // 三項演算子 条件 ? A : B で判定し、`data-selected="true"`、それ以外は `false` にする。
  // CSS 側では `[data-selected="true"]` をスタイルフックに利用できる。
  _selectChip(groupSel, value) {
    this.$$(groupSel + " .filterbar__chip").forEach((c) => {
      c.dataset.selected = c.dataset.value === value ? "true" : "false";
    });
  }

  // 子カテゴリ（PC）の見た目＆操作可否をまとめて切り替える
  // slug が空: 全子を不可（薄く＋クリック不可）
  // slug が文字列: その親の子だけ可（不透明＋クリック可）、他は不可
  _setCatChildrenInteractivityPC(slug = "") {
    // Object.keys() で引数に渡したオブジェクト自身が持つ（＝継承ではない）列挙可能なプロパティ名（キー）を
    // 配列で返すので、すべての親スラッグ（["parts","apparel"] など）を配列で取得
    Object.keys(this.childrenMap).forEach((parentSlug) => {
      const box = this.$(this.selectors.pcCatChildrenPrefix + parentSlug);
      if (!box) return;

      const isActive = slug && parentSlug === slug;

      // 見た目（不透明度）
      box.style.opacity = isActive ? 1 : 0.35;

      // 操作可否（クリック/フォーカスを止める）
      this.$$(".filterbar__chip", box).forEach((chip) => {
        if (isActive) {
          chip.style.pointerEvents = "auto";
          chip.tabIndex = 0;
          chip.setAttribute("aria-disabled", "false");
        } else {
          chip.style.pointerEvents = "none";
          chip.tabIndex = -1;
          chip.setAttribute("aria-disabled", "true");
        }
      });
    });
  }

  // すべてのドロップダウンを閉じる（PC版）
  // ---------------------------------
  // ・開いている .filterbar__dropdown から is-open を除去
  // ・トリガーボタン（.js-open-dropdown）の aria-expanded を "false" に戻す
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

  // PC版のイベントをまとめて初期化
  _bindPC() {
    // ---------------------------------
    // 1) ドロップダウン開閉（トグルボタン）
    // ---------------------------------
    this.$$(this.selectors.openDropdownBtn).forEach((btn) => {
      btn.addEventListener("click", () => {
        // どのドロップダウンを開くかは data-target="cat-dd" などで指定
        const id = btn.getAttribute("data-target");
        const dd = document.getElementById(id);
        if (!dd) return;

        // 開く予定かどうか（すでに開いていれば閉じる）
        const willOpen = !dd.classList.contains("is-open");

        // まず全ドロップダウンを閉じる（単一開閉に統一）
        this._closeAllDropdowns();

        // 開く予定ならこのドロップダウンだけ開く ＆ aria-expanded を true に
        if (willOpen) {
          dd.classList.add("is-open");
          btn.setAttribute("aria-expanded", "true");
        }
      });
    });

    // ---------------------------------
    // 2) 外側クリック / Esc でドロップダウンを閉じる
    // ---------------------------------
    // 外側クリック（ドロップダウン本体でもトグルでもない場所をクリック）
    document.addEventListener("click", (e) => {
      // どこかがクリックされたら e.target （実際にクリックされた“最も内側の要素”）から closest で親にさかのぼり、
      // ドロップダウン本体（.filterbar__dropdown）または開閉ボタン（.js-open-dropdown）**に当たるかチェック
      const hit = e.target.closest(
        ".filterbar__dropdown, " + this.selectors.openDropdownBtn
      );
      // 当たらなければ「外側クリック」とみなし、this._closeAllDropdowns() で全て閉じる。
      if (!hit) this._closeAllDropdowns();
    });
    // Esc キーで全ドロップダウンを閉じる
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") this._closeAllDropdowns();
    });

    // ---------------------------------
    // 3) カテゴリ選択（親→子）
    // ---------------------------------

    // 親カテゴリをクリックした時の処理
    this.$$(this.selectors.pcCatParent + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          // state 更新：親をセット、子はリセット
          this.state.catParent = chip.dataset.value;
          this.state.catChild = "";

          // 親カテゴリの選択表示を同期
          this._selectChip(this.selectors.pcCatParent, this.state.catParent);

          // 子カテゴリ群の見た目＆操作可否を切り替え（選択親のみ有効化）
          this._setCatChildrenInteractivityPC(this.state.catParent);

          // 上部ピル表示を更新
          this._renderPills();
        });
      }
    );

    // 子カテゴリをクリックした時の処理（全親の子を一括で拾ってハンドリング）
    // 子カテゴリのチップをクリックしたら state を更新し、全ての親グループに選択状態を同期し、上部ピルを更新する
    this.$$(
      this.selectors.dropdown + " .filterbar__options .filterbar__chip"
    ).forEach((chip) => {
      // 親の子エリアか判定（id="pc-cat-children-***" 配下だけ拾う）
      const inCatChildren =
        chip.parentElement?.id?.startsWith("pc-cat-children-");
      if (!inCatChildren) return;

      chip.addEventListener("click", () => {
        // state 更新：子をセット
        this.state.catChild = chip.dataset.value;

        // すべての親グループに対して、子の選択表示を同期（どの親の子DOMでも選択が一貫）
        // Object.keys() で引数に渡したオブジェクト自身が持つ（＝継承ではない）列挙可能なプロパティ名（キー）を
        // 配列で返すので、すべての親スラッグ（["parts","apparel"] など）を配列で取得
        Object.keys(this.childrenMap).forEach((parentSlug) => {
          this._selectChip(
            this.selectors.pcCatChildrenPrefix + parentSlug,
            this.state.catChild
          );
        });

        // 上部ピル表示を更新
        this._renderPills();
      });
    });

    // ---------------------------------
    // 4) タグ選択（人気タグ／検索）
    // ---------------------------------

    // 人気タグ：初期辞書にラベルを登録して、クリックで選択
    this.$$(this.selectors.pcTagPopular + " .filterbar__chip").forEach(
      (chip) => {
        const slug = chip.dataset.value; // 例: "tshirt"
        const label = chip.dataset.label || chip.textContent.trim(); // 例: "Tシャツ"
        this.tagLabelBySlug[slug] = label; // 逆引き辞書に登録

        chip.addEventListener("click", () => {
          this.state.tag = slug;
          // decodeURIComponent は、URL の “クエリ値” などで使われるパーセントエンコード（%E3%81… 形式）を
          // 本来の文字列に戻すための関数（例）"t%e3%82%b7%e3%83%a3%e3%83%84" → "Tシャツ"）
          this.state.tagLabel =
            this.tagLabelBySlug[slug] || decodeURIComponent(slug);

          // 人気タグ群の選択表示更新、選択済みタグ枠の描画、ピル更新
          this._selectChip(this.selectors.pcTagPopular, this.state.tag);
          this._renderTagSelected();
          this._renderPills();
        });
      }
    );

    // タグ検索（インクリメンタル：入力毎に絞り込みボタンを作る簡易版）
    const search = this.$(this.selectors.pcTagSearch);
    if (search) {
      search.addEventListener("input", (e) => {
        //  ユーザー入力の正規化（検索クエリを 小文字化 ＋ 前後空白除去）
        const q = e.target.value.toLowerCase().trim();
        // 候補母集団の用意
        const pool = Object.entries(this.tagLabelBySlug); // [[slug, label], ...] 形式の配列
        // フィルタリングしてラベル辞書（slug→label）から一致候補を最大10件まで作る
        const hit = pool
          .filter(
            ([slug, label]) =>
              slug.includes(q) || label.toLowerCase().includes(q)
          )
          .slice(0, 10);

        // 結果描画用ボックスにボタンを生成
        const res = this.$(this.selectors.pcTagResults);
        if (!res) return;
        res.innerHTML = "";
        hit.forEach(([slug, label]) => {
          const b = document.createElement("button");
          b.className = "filterbar__chip";
          b.dataset.value = slug;
          b.textContent = label;
          b.addEventListener("click", () => {
            this.state.tag = slug;
            this.state.tagLabel = label;
            this._renderTagSelected(); // 「選択中タグ」枠に反映
            this._renderPills();
          });
          res.appendChild(b);
        });
      });
    }

    // ---------------------------------
    // 5) ブランド（メーカー→車種→型式）
    // ---------------------------------

    // メーカー（親）クリックで、車種・型式をリセットし、可視領域を更新
    this.$$(this.selectors.pcBrandMaker + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          this.state.brandParent = chip.dataset.value; // 親メーカー slug
          this.state.brandModel = ""; // 車種リセット
          this.state.brandChassis = ""; // 型式リセット
          this._renderBrandVisibilityPC(); // PC側の表示更新（該当メーカーの車種だけ表示など）
          this._renderPills(); // ピル更新
        });
      }
    );

    // 車種（子）クリックで、型式をリセットし、親は逆引き辞書で自動補完
    this.$$(this.selectors.pcBrandModelBoxes + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          const model = chip.dataset.value;
          this.state.brandModel = model;
          this.state.brandChassis = "";
          // 逆引き：車種→親メーカー を辞書から導出（なければ現状維持）
          this.state.brandParent =
            this.brandIndex.parentByModel[model] || this.state.brandParent;
          this._renderBrandVisibilityPC();
          this._renderPills();
        });
      }
    );

    // 型式（孫）クリックで、車種・親メーカーを逆引きで自動補完
    this.$$(this.selectors.pcBrandChassisBoxes + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          const chassis = chip.dataset.value;
          this.state.brandChassis = chassis;
          // 逆引き：型式→車種
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

    // ---------------------------------
    // 6) リセット（カテゴリ／タグ／ブランド）
    // ---------------------------------
    // カテゴリを初期化（選択表示も解除）
    this.$$(this.selectors.resetCatBtn).forEach((b) =>
      b.addEventListener("click", () => {
        this.state.catParent = "";
        this.state.catChild = "";
        this._selectChip(this.selectors.pcCatParent, "");
        // Object.keys() で引数に渡したオブジェクト自身が持つ（＝継承ではない）列挙可能なプロパティ名（キー）を
        // 配列で返すので、すべての親スラッグ（["parts","apparel"] など）を配列で取得
        Object.keys(this.childrenMap).forEach((parentSlug) =>
          this._selectChip(this.selectors.pcCatChildrenPrefix + parentSlug, "")
        );
        // リセット後は全子カテゴリを無効化（薄く＋クリック不可）
        this._setCatChildrenInteractivityPC("");
        this._renderPills();
      })
    );

    // タグを初期化（PC/SPの人気タグの選択表示も解除）
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

    // ブランドを初期化（state/表示の両方をクリア）
    this.$$(this.selectors.resetBrandBtn).forEach((b) =>
      b.addEventListener("click", () => {
        this._resetBrand();
        this._renderPills();
      })
    );

    // ---------------------------------
    // 7) 「適用」ボタン：URLに反映して再読み込み
    // ---------------------------------
    this.$$(this.selectors.applyBtns).forEach((b) =>
      b.addEventListener("click", () => {
        this._closeAllDropdowns(); // 見た目を閉じてから
        this._renderPills(); // 最終表示を同期（任意）
        this._applyToURL(); // URLクエリへ反映してページ遷移
      })
    );
  }

  // タグの「選択中」表示を描画する
  // ---------------------------------
  // 役割:
  //  - PC側の選択結果枠（this.selectors.pcTagSelected）に、
  //    現在選ばれているタグを 1 つだけボタンとして描画する。
  //  - ボタンは "×" 付きで、クリックするとタグ選択を解除する。
  _renderTagSelected() {
    // 1) 挿入先コンテナを取得（存在しない場合は何もしない）
    const box = this.$(this.selectors.pcTagSelected);
    if (!box) return;

    // 2) いったん中身を空にしてから再描画（重複描画防止）
    box.innerHTML = "";

    // 3) state.tag が空でなければ、選択中タグを表示
    if (this.state.tag) {
      // 表示ラベルの決定順：
      //  - 優先1: state.tagLabel（人間可読ラベルが既に分かっている場合）
      //  - 優先2: tagLabelBySlug 辞書から逆引き
      //  - 優先3: slug を decode してそのまま表示（URLエンコード対策）

      // decodeURIComponent は、URL の “クエリ値” などで使われるパーセントエンコード（%E3%81… 形式）を
      // 本来の文字列に戻すための関数（例）"t%e3%82%b7%e3%83%a3%e3%83%84" → "Tシャツ"）
      const label =
        this.state.tagLabel ||
        this.tagLabelBySlug[this.state.tag] ||
        decodeURIComponent(this.state.tag);

      // 4) ボタン要素を生成して、選択中であることを data-selected="true" で明示
      const b = document.createElement("button");
      b.className = "filterbar__chip";
      b.dataset.selected = "true";
      // 視覚上 "×" を付けて、クリックで解除できることを示す
      b.textContent = `${label} ×`;

      // 5) クリックでタグ選択を解除 → 再描画
      b.addEventListener("click", () => {
        // state を初期化
        this.state.tag = "";
        this.state.tagLabel = "";
        // 自分自身（選択中表示）と上部ピルを再描画
        this._renderTagSelected();
        this._renderPills();
      });

      // 6) コンテナに追加
      box.appendChild(b);
    }
  }

  // =====================
  // SP bindings
  // =====================

  // SP（スマホ）向けのイベントをまとめて初期化
  _bindSP() {
    // --- モーダルの参照を取得 ---
    const modal = this.$(this.selectors.modal);
    const openBtn = document.querySelector(".filterbar__open");
    const closeBtns = this.$$(".js-close-modal");

    // --- モーダルの開閉 ---
    if (openBtn && modal) {
      // 開く：.filterbar__open をタップしたら is-open を付与
      openBtn.addEventListener("click", () => modal.classList.add("is-open"));
      // 閉じる：.js-close-modal（背景や×ボタン）をタップしたら is-open を除去
      closeBtns.forEach((b) =>
        b.addEventListener("click", () => modal.classList.remove("is-open"))
      );
    }

    // --- 親カテゴリ（SP） ---
    // 親チップをタップ → 親を state にセット、子はリセットし、子一覧を再描画
    // $$ は NodeList を配列化するヘルパなので、0件でも空配列→forEach安全
    this.$$(this.selectors.spCatParent + " .filterbar__chip").forEach(
      (chip) => {
        chip.addEventListener("click", () => {
          // 1) state 更新：親を選択、子は未選択へリセット
          this.state.catParent = chip.dataset.value;
          this.state.catChild = "";

          // 2) 視覚反映：親グループ内の [data-selected] を同期
          this._selectChip(this.selectors.spCatParent, this.state.catParent);

          // 3) 子リスト再描画：選んだ親に紐づく childrenMap からボタンを作り直し
          this._renderSpChildren();

          // 4) 上部のピル表示（現在の選択サマリ）を更新
          this._renderPills();
        });
      }
    );

    // --- タグ（SP：検索 + 人気） ---
    // 検索ボックスの入力に応じて、候補ボタン（最大10件）を作り直す
    // SP：タグ検索の入力ボックスを取得
    const spTagSearch = this.$(this.selectors.spTagSearch);
    if (spTagSearch) {
      // 入力のたびに走る（インクリメンタル検索）
      spTagSearch.addEventListener("input", (e) => {
        // ユーザー入力を正規化（小文字化・前後空白除去）
        const q = e.target.value.toLowerCase().trim();

        // 逆引き辞書 {slug: label} → [[slug, label], ...] に変換
        const pool = Object.entries(this.tagLabelBySlug);

        // slug / label のどちらかにクエリが含まれる候補を最大10件抽出
        const hit = pool
          .filter(
            ([slug, label]) =>
              slug.includes(q) || label.toLowerCase().includes(q)
          )
          .slice(0, 10);

        // 結果描画先（候補ボタンを並べるボックス）
        const box = this.$(this.selectors.spTagSelected);
        if (!box) return;

        // 前回の結果をクリアして差し替え
        box.innerHTML = "";

        // 候補ごとにボタンを生成 → クリックで state を更新
        hit.forEach(([slug, label]) => {
          const b = document.createElement("button");
          b.className = "filterbar__chip";
          b.dataset.value = slug; // 応用（選択同期など）用に slug を保持
          b.textContent = label; // 表示は人間可読ラベル

          // 候補ボタンをクリックしたら、タグの選択を確定
          b.addEventListener("click", () => {
            this.state.tag = slug; // URLに載せる値
            this.state.tagLabel = label; // ピルに出す表示用ラベル

            // 人気タグ側の見た目も同期（[data-selected] を更新）
            this._selectChip(this.selectors.spTagPopular, this.state.tag);

            // 上部ピル（現在の選択サマリ）を更新
            this._renderPills();
          });

          // ボックスに追加
          box.appendChild(b);
        });
      });
    }

    // 人気タグのクリック → state を更新し、ピルを更新
    this.$$(this.selectors.spTagPopular + " .filterbar__chip").forEach(
      (chip) => {
        // ボタン要素（.filterbar__chip）から、URL用の値（slug）を取得
        const slug = chip.dataset.value;

        // 表示用の人間可読ラベルを取得（data-label があれば優先、無ければボタンのテキスト）
        const label = chip.dataset.label || chip.textContent.trim();

        // 逆引き辞書 { slug: label } に登録（既に登録があれば上書きしない）
        // 例）{ "tshirt": "Tシャツ" } として後でピル表示などに流用
        this.tagLabelBySlug[slug] = this.tagLabelBySlug[slug] || label;

        // この人気タグチップがクリックされたときの処理
        chip.addEventListener("click", () => {
          // URL に載せる生の値（slug）を state に保持
          this.state.tag = slug;

          // ピル（上部の要約表示）向けの表示ラベルを state に保持
          // 既知であれば辞書から、無ければ decode して文字化け回避
          // decodeURIComponent は、URL の “クエリ値” などで使われるパーセントエンコード（%E3%81… 形式）を
          // 本来の文字列に戻すための関数（例）"t%e3%82%b7%e3%83%a3%e3%83%84" → "Tシャツ"）
          this.state.tagLabel =
            this.tagLabelBySlug[slug] || decodeURIComponent(slug);

          // 見た目の選択状態を同期（SPの人気タグ群内で、選ばれたチップだけ data-selected="true"）
          this._selectChip(this.selectors.spTagPopular, this.state.tag);

          // 上部ピルのテキストを最新 state に合わせて描画
          this._renderPills();
        });
      }
    );

    // --- ブランド（SP：メーカー→車種→型式） ---
    // メーカー（親）エリア内のすべてのチップを取得してループ
    this.$$(this.selectors.spBrandMaker + " .filterbar__chip").forEach((chip) =>
      // 各チップにクリック時の処理を付与
      chip.addEventListener("click", () => {
        // 1) state を更新
        //    - 親（メーカー）をいま押したチップの値にする
        //    - 下位の選択（車種・型式）はリセットし、整合性を保つ
        this.state.brandParent = chip.dataset.value;
        this.state.brandModel = "";
        this.state.brandChassis = "";

        // 2) 可視領域を更新（SP版）
        //    - 選んだメーカーに対応する「車種グループ」を表示し、それ以外は非表示
        //    - 車種が未選択なので「型式グループ」は全体表示（or 非表示）など、現Stateに合わせて切替
        this._renderBrandVisibilitySP();

        // 3) 選択表示の同期
        //    - 現在の選択（型式 > 車種 > メーカーの優先）を 1 値に正規化し、
        //      その値と一致するチップへ data-selected="true" を反映
        this._selectBrandChips(this._deriveBrandParam());

        // 4) 上部ピル（現在の選択サマリ）テキストを最新 state に合わせて更新
        this._renderPills();
      })
    );

    // 車種（子）クリック → 型式をリセットし、親は逆引き辞書で自動補完
    this.$$(this.selectors.spBrandModelBoxes + " .filterbar__chip").forEach(
      (chip) =>
        chip.addEventListener("click", () => {
          // 1) クリックされた車種チップの slug を取得（例: "silvia"）
          const model = chip.dataset.value;

          // 2) state を更新：車種=選択値、型式=リセット（車種変更で無効化）
          this.state.brandModel = model;
          this.state.brandChassis = "";

          // 3) 逆引き辞書で「車種 → 親メーカー」を補完（見つからなければ現状維持）
          this.state.brandParent =
            this.brandIndex.parentByModel[model] || this.state.brandParent;

          // 4) SPモーダル内の表示を最新 state に合わせて切替
          //    - 選んだメーカーの車種グループだけ表示
          //    - 選んだ車種の型式グループだけ表示 など
          this._renderBrandVisibilitySP();

          // 5) data-selected 同期（型式>車種>メーカーの優先で1値を導出し、その値と一致するチップに選択マーク）
          this._selectBrandChips(this._deriveBrandParam());

          // 6) 上部ピル（現在の選択サマリ）を最新化
          this._renderPills();
        })
    );

    // 型式（孫）クリック → 逆引きで車種・メーカーも自動補完
    this.$$(this.selectors.spBrandChassisBoxes + " .filterbar__chip").forEach(
      (chip) =>
        chip.addEventListener("click", () => {
          // 1) クリックされた型式チップの slug（例: "s15"）を取得し、state に保存
          const chassis = chip.dataset.value;
          this.state.brandChassis = chassis;

          // 2) 逆引き：型式 → 車種 を brandIndex から特定
          //    modelByChassis は { "s15": "silvia", ... } のような辞書
          const model = this.brandIndex.modelByChassis[chassis];

          // 3) 車種が分かったら state に反映し、さらに 車種 → メーカー も逆引きで補完
          if (model) {
            this.state.brandModel = model; // 例: "silvia"
            // parentByModel は { "silvia": "nissan", ... } の辞書
            // 見つからなかった場合は現状の brandParent を維持
            this.state.brandParent =
              this.brandIndex.parentByModel[model] || this.state.brandParent;
          }

          // 4) SPモーダル内の表示を最新 state に合わせて切替
          //    - 選んだメーカーの「車種グループ」だけを表示
          //    - 選んだ車種の「型式グループ」だけを表示
          this._renderBrandVisibilitySP();

          // 5) data-selected の同期（型式 > 車種 > メーカーの優先で 1 値に正規化し、その slug と一致するチップを選択表示）
          this._selectBrandChips(this._deriveBrandParam());

          // 6) 上部ピル（現在の選択サマリ）を最新 state で表示
          this._renderPills();
        })
    );

    // --- フッター（リセット／適用） ---
    // SPモーダルの「リセット」ボタン（例: #sp-reset）にクリック処理を付与
    this.$(this.selectors.spResetBtn)?.addEventListener("click", () => {
      // ① state を“新しいオブジェクト”で再構築し、主要な選択値を空文字にリセット
      //    ...this.state は既存プロパティを複製して展開 → 後続のキーで一部上書き（順序が重要）
      //    state の各プロパティは全てプリミティブ（文字列）であり、値のコピーになるので、オブジェクト/配列のように参照は共有されない
      //    既存オブジェクトを直接書き換えるより安全・意図が明瞭
      this.state = {
        ...this.state, // 既存の state を展開（未指定のキーは保持）
        catParent: "", // 親カテゴリ（未選択へ）
        catChild: "", // 子カテゴリ（未選択へ）
        tag: "", // タグ（未選択へ）
        tagLabel: "", // タグ表示用ラベル（消去）
        brandParent: "", // ブランド：メーカー（未選択へ）
        brandModel: "", // ブランド：車種（未選択へ）
        brandChassis: "", // ブランド：型式（未選択へ）
      };

      // ② 見た目の選択状態を同期（SP人気タグの [data-selected] を解除）
      this._selectChip(this.selectors.spTagPopular, "");

      // ③ 子カテゴリ一覧を作り直す（親が未選択になったため、空 or 全体など初期表示へ）
      this._renderSpChildren();

      // ④ ブランド（SP）の可視グループを state に合わせて切替
      this._renderBrandVisibilitySP();

      // ⑤ 上部のピル（現在選択サマリ）を再描画
      this._renderPills();
    });

    // 「適用」→ モーダルを閉じて URL へ反映（ページ遷移）
    this.$(this.selectors.spApplyBtn)?.addEventListener("click", () => {
      const modal = this.$(this.selectors.modal);
      modal?.classList.remove("is-open");
      this._renderPills();
      this._applyToURL();
    });

    // --- 初期レンダリング ---
    // 親カテゴリの現状（未選択 or 選択済み）に応じて、子カテゴリを描画
    this._renderSpChildren();
  }

  // SP（スマホ）用：選択中の親カテゴリに対応する「子カテゴリ」ボタン群を描画する
  _renderSpChildren() {
    // 1) モーダル内「子カテゴリ」ボックスを取得（無ければ何もしない）
    const box = this.$(this.selectors.spCatChildren);
    if (!box) return;

    // 2) 毎回クリーンに作り直したいので中身を空にする
    box.innerHTML = "";

    // 3) データ取得：
    //    - this.childrenMap は { parentSlug: [{value,label}, ...], ... } の辞書
    //    - 選択中の親（this.state.catParent）に対応する配列/オブジェクトを取得
    //    - _toArray() で「配列でもオブジェクトでも」forEach可能な配列に正規化
    const list = this._toArray(this.childrenMap?.[this.state.catParent]);

    // 4) 子カテゴリごとにボタンを生成して挿入
    list.forEach((item) => {
      // <button class="filterbar__chip" data-value="child-slug">子カテゴリ名</button>
      const b = document.createElement("button");
      b.className = "filterbar__chip";
      b.dataset.value = item.value; // URLに載せるslug相当
      b.textContent = item.label; // 人間可読ラベル

      // 5) すでにこの子カテゴリが選択されていれば data-selected="true" を付ける（見た目のハイライト用）
      if (this.state.catChild === item.value) b.dataset.selected = "true";

      // 6) クリック時の挙動：
      //    - state.catChild を選んだ値に更新
      //    - ピル（上部の要約表示）を更新
      //    - 自分自身（子リスト）を再描画して選択表示を反映
      b.addEventListener("click", () => {
        this.state.catChild = item.value;
        this._renderPills();
        this._renderSpChildren();
      });

      // 7) ボックスに追加
      box.appendChild(b);
    });
  }

  // =====================
  // common (初期選択の視覚反映など)
  // =====================

  _bindCommon() {
    // --- 初期選択（カテゴリ） ---
    // 親カテゴリが URL/state に入っていれば、親チップ側の選択表示を同期
    if (this.state.catParent) {
      this._selectChip(this.selectors.pcCatParent, this.state.catParent);
    }

    // 初期：親未選択なら全子を不可、親があれば該当親の子だけ可
    if (!this.state.catParent) {
      this._setCatChildrenInteractivityPC("");
    } else {
      this._setCatChildrenInteractivityPC(this.state.catParent);
    }

    // 子カテゴリが入っていれば、すべての「親ごとの子グループ」に対して
    // 選択表示（data-selected="true"）を同期する
    // 例）pc-cat-children-parts / pc-cat-children-apparel の両方で同じ子が選択状態になる
    if (this.state.catChild) {
      // Object.keys() で引数に渡したオブジェクト自身が持つ（＝継承ではない）列挙可能なプロパティ名（キー）を
      // 配列で返すので、すべての親スラッグ（["parts","apparel"] など）を配列で取得
      Object.keys(this.childrenMap).forEach((parentSlug) =>
        this._selectChip(
          this.selectors.pcCatChildrenPrefix + parentSlug,
          this.state.catChild
        )
      );
    }

    // --- 初期選択（タグ） ---
    // URL から tag=xxx が来ていれば、PC/ SP の人気タグ群で選択表示を同期
    if (this.state.tag) {
      this._selectChip(this.selectors.pcTagPopular, this.state.tag);
      this._selectChip(this.selectors.spTagPopular, this.state.tag); // SP側にも反映
    }
    // URL直叩きなどで label を持っていない場合、逆引き辞書 or decode で人間可読に補完
    this._inferTagLabelFromState();

    // --- 初期選択（ブランド） ---
    // state（メーカー/車種/型式）に合わせて、PCのドロップダン/SPのモーダルの
    // 表示・非表示を切替え、選択マークも同期する
    this._renderBrandVisibilityPC();
    this._renderBrandVisibilitySP();
  }

  // タグの「表示用ラベル」を補完する
  // URL直叩き時に slug→人間可読ラベル を辞書照合 or decodeURIComponent で補完
  // ---------------------------------
  // 条件: tag が選択済み かつ tagLabel が未設定のときだけ実行
  // 優先順位:
  //  1) tagLabelBySlug[slug] : 事前に辞書へ登録されていればそれを使う
  //  2) decodeURIComponent(slug): URLエンコードされた文字（日本語など）を人間可読に戻す
  _inferTagLabelFromState() {
    if (this.state.tag && !this.state.tagLabel) {
      this.state.tagLabel =
        this.tagLabelBySlug[this.state.tag] || // 逆引き辞書を最優先
        decodeURIComponent(this.state.tag); // 無ければ URL デコードでフォールバック
    }
  }
}
