# PROSHOPWAVE - WordPress Theme

自動車カスタムパーツ店のECサイト専用に開発されたオリジナル WordPress テーマです。チューニングプロショップを表現したビジュアルと、WooCommerce対応の機能性を両立しています。

## 特長

- Swiper.js ＆ GSAP アニメーション
- WooCommerce 対応
- モバイルファースト設計
- パフォーマンス重視の軽量コード

## 開発環境

- WordPress 6.x 以上
- PHP 7.4 以上
- Local by Flywheel 推奨

## ディレクトリ構成（抜粋）

proshopwave/
├── css/
├── js/
├── scss/
├── template-parts/
├── woocommerce/
├── functions.php
└── style.css

## 推奨プラグイン

- WooCommerce

## 簡易スラッグ命名ルール（URL 用）

スラッグは URL に使用される識別名です。SEO・管理性のため、以下の簡易ルールを採用します。

### 共通ルール
- 英小文字のみ  
- 単語区切りはハイフン（-）  
- 日本語・全角・記号は使用しない  
- 意味が分かる最小構成でOK

### 商品スラッグ（WooCommerce）

```
[brand]-[product-name]
```

**例**
- `mow-jdm-tee`
- `wave-drift-wing`
- `nissan-silvia-turbo-kit`

※ SKU・色・サイズはスラッグに含めません。

※ 複数の型式に対応する商品でも、スラッグには型式をすべて含めません。
対応車種・型式はブランド階層や商品説明で管理します。

**複数型式対応の例**
- ✅ `nissan-silvia-turbo-kit`（推奨）
- ❌ `nissan-silvia-s13-s14-s15-turbo-kit`（長くなり管理が大変）

### NG例（商品スラッグ）

以下は、管理性・SEO・将来拡張の観点から避けるべきスラッグ例です。

- `mow_jdm_tee`（アンダースコア使用）
- `MOW-JDM-Tee`（大文字混在）
- `mow-jdm-tee-black-m`（色・サイズを含めている）
- `mow-velocity-tee-001312`（SKU・型番を含めている）
- `nissan-silvia-s13-s14-s15-turbo-kit`（型式を列挙して長すぎる）
- `商品-ドリフト-tee`（日本語・全角文字）
- `mow--jdm--tee`（不要な連続ハイフン）

### ブログ記事スラッグ

```
yyyy-mm-topic
```

**例**
- `2025-07-turbo-setup`
- `2025-06-new-arrivals`

### NG例（ブログ記事スラッグ）

以下は、管理性・SEO・将来拡張の観点から避けるべきスラッグ例です。

- `turbo-setup`（年月がなく時系列管理できない）
- `2025_07_turbo_setup`（アンダースコア使用）
- `2025-7-turbo-setup`（月が2桁でない）
- `2025-07-Turbo-Setup`（大文字混在）
- `2025-07-turbo-setup-v1-final`（用途不明な語が多く冗長）
- `2025-07-ターボ-セットアップ`（日本語・全角文字）
- `2025-07--turbo--setup`（不要な連続ハイフン）

## 画像ファイル命名規則（WAVE 共通）

本テーマでは、SEO・可読性・保守性を考慮し、**すべての画像ファイル名はハイフン（-）区切り**で統一します。  
アンダースコア（_）は使用しません。

### 命名規則の基本ルール

- すべて **半角英数字 + ハイフン**
- **小文字のみ**
- 単語の区切りは `-`
- 日本語・全角文字は禁止

### NG例（画像ファイル命名規則）

- `mow_tee_black.jpg`（アンダースコア）  
- `MOW-Tee.JPG`（大文字・拡張子）  
- `商品画像.jpg`（日本語）

### 商品画像（WooCommerce）

本プロジェクトでは、**パーツ商品** と **アパレル商品** で
画像の役割・管理方法が異なるため、命名ルールを明確に切り分けて運用します。

---

### パーツ商品用 画像命名ルール

パーツ商品は **形状・仕様差が少なく、順序管理が重要** なため、
「連番管理」を基本とします。

```
product-[brand]-[product-slug]-[seq].jpg
```

**要素の意味**
- `brand`：ブランド名（例：nissan / toyota / wave）
- `product-slug`：商品スラッグ（英小文字・ハイフン区切り）
- `seq`：表示順の2桁連番  
  - `01`：商品メイン画像  
  - `02`以降：商品ギャラリー画像

**連番ルール**
- メイン画像は必ず `01`
- ギャラリーは `02 / 03 / 04 …` と増やす

**例**
- `product-nissan-silvia-suspension-kit-01.jpg`
- `product-nissan-silvia-suspension-kit-02.jpg`
- `product-wave-drift-wing-01.jpg`

> パーツ商品は  
> **「何番目の画像か」＝意味が明確** なため、  
> 連番管理が最も保守性に優れます。

---

### アパレル商品用 画像命名ルール

アパレル商品は **カラー展開・着用画像・ディテール画像** が重要なため、
「連番」ではなく **役割ベース管理** を採用します。

---

#### ① 商品メイン画像（カラー別・バリエーション）

カラー差分がある画像は、**必ずカラー名を含める**。

```
product-[brand]-[product-slug]-main-[color].jpg
```

**例**
- `product-mow-jdm-tee-main-black.jpg`
- `product-mow-jdm-tee-main-white.jpg`
- `product-mow-velocity-hoodie-main-charcoal.jpg`

---

#### ② ギャラリー共通画像（アパレルのみ）

ギャラリー共通画像は **役割名で管理することを必須** とします。

**特徴**
- カラーやサイズなどの **バリエーションに紐づかない**
- 全カラー共通で使用される
- 画像の役割が明確

```
product-[brand]-[product-slug]-[role].jpg
```

**使用可能な role 例**
- `wear`：着用イメージ
- `detail`：生地・プリント・縫製などのディテール
- `back`：背面デザイン（必要な場合のみ）

**例**
- `product-mow-jdm-tee-wear.jpg`
- `product-mow-jdm-tee-detail.jpg`
- `product-mow-jdm-tee-back.jpg`

> ❌ `02 / 03` のような連番は使用しない  
> ✅ **「画像を見なくても役割が分かる」ことを最優先**

---

### ルールまとめ（重要）

| 区分 | 管理方法 | 理由 |
| パーツ | 連番管理（01 / 02 / 03） | 形状差が少なく、順序が重要 |
| アパレル（メイン） | カラー名 + main | バリエーション管理が必須 |
| アパレル（ギャラリー共通） | 役割名（wear / detail / back） | 意味ベース管理が最適 |

### ブログ用画像

ブログでは、画像の役割ごとに名前を分けて管理します。  
- アイキャッチ画像：記事の表紙になる画像。ファイル名の末尾に -cover を付けます。  
- 本文内の画像：記事の中で使う画像。表示順に 01 / 02 / 03… の番号を付けます。

#### アイキャッチ画像（固定 suffix）

```
blog-{yyyy}-{mm}-{slug}-cover.jpg
```

**要素の意味**
- `yyyy`：公開年（4桁）
- `mm`：公開月（2桁）
- `slug`：記事スラッグ（英小文字・ハイフン区切り）
- `cover`：アイキャッチ（固定）

**例**
- `blog-2025-07-parallax-setup-cover.jpg`

#### 本文画像（連番）

```
blog-{yyyy}-{mm}-{slug}-01.jpg
blog-{yyyy}-{mm}-{slug}-02.jpg
```

**要素の意味**
- `01 / 02 ...`：本文内の表示順（2桁連番）

**例**
- `blog-2025-07-turbo-setup-01.jpg`
- `blog-2025-07-turbo-setup-02.jpg`

### OGP / SNS 用画像（ブログ・商品 共通方針）

ブログ記事・商品ページともに、**OGP / SNS 共有画像はメイン画像を兼用**します。

- ブログ記事：アイキャッチ画像  
- 商品ページ：商品画像の **01（メイン画像）**

そのため、通常運用では OGP 専用の画像ファイルは作成しません。

#### 例外

キャンペーンページや特別な告知などで、  
意図的に OGP を分けたい場合のみ、専用画像を用意しても構いません。

```
ogp-{page-or-post-slug}.jpg
```

※ 通常の商品・ブログ運用では使用しません。

## 開発コマンド（Gulp使用時）

```bash
yarn install
yarn run dev    # 開発用
yarn run build  # 本番用
```

## ライセンス
本テーマは専用案件向けに開発されています。無断転載・再配布は禁止です。