// ** 【WordPress用】 distへ出力せずにテーマ直下に出力、browserSyncのproxyにLocalのサイトドメインを設定
const { src, dest, watch, series, parallel, lastRun } = require("gulp");
const loadPlugins = require("gulp-load-plugins");
const $ = loadPlugins();
const pkg = require("./package.json");
const del = require("del");
const webp = require("gulp-webp");
const sass = require("sass");
const browserSync = require("browser-sync").create();
const isProd = process.env.NODE_ENV === "production";

// **ファイルのパス設定**
const paths = {
  styles: {
    src: "src/sass/**/*.scss",
    dest: "css"
  },
  scripts: {
    src: "src/js/**/*.js",
    dest: "js"
  },
  images: {
    src: "src/images/**/*.{jpg,jpeg,png,svg,gif,webp}",
    dest: "images"
  },
  php: {
    src: "src/**/*.php",
    dest: "./"
  }
};

// **Sassのコンパイル**
function styles() {
  return src(paths.styles.src)
    .pipe($.plumber({ errorHandler: $.notify.onError("Sass Error: <%= error.message %>") }))
    .pipe($.if(!isProd, $.sourcemaps.init()))
    .pipe($.dartSass({ 
      outputStyle: isProd ? "compressed" : "expanded", 
      logger: sass.logger, // Loggerを適用してSass のエラーをカスタマイズ
      silenceDeprecations: ["legacy-js-api"] // 警告を非表示
    }))
    .pipe($.autoprefixer({ cascade: true }))
    .pipe($.if(!isProd, $.sourcemaps.write(".")))
    .pipe(dest(paths.styles.dest))
    .pipe(browserSync.stream());
}

// **JSの処理**
function scripts() {
  return src(paths.scripts.src)
    .pipe($.if(!isProd, $.sourcemaps.init()))
    .pipe($.if(isProd, $.uglify()))
    .pipe($.if(!isProd, $.sourcemaps.write(".")))
    .pipe(dest(paths.scripts.dest))
    .pipe(browserSync.stream());
}

// **画像圧縮 + WebP変換**
function images() {
  return src(paths.images.src, { since: lastRun(images) })
    .pipe($.imagemin([
      $.imagemin.gifsicle({ interlaced: true }),
      $.imagemin.mozjpeg({ quality: 85, progressive: true }),
      $.imagemin.optipng({ optimizationLevel: 3 }),
      $.imagemin.svgo({ plugins: [{ removeViewBox: true }, { cleanupIDs: false }] })
    ]))
    .pipe(dest(paths.images.dest)) // 通常の画像を保存
    .pipe(webp()) // WebP 変換
    .pipe(dest(paths.images.dest)); // WebP 画像を保存
}

// **PHPのコピー処理**
function php() {
  return src(paths.php.src).pipe(dest(paths.php.dest)).pipe(browserSync.stream());
}

// **ファイルの変更監視**
function startAppServer() {
  browserSync.init({
    proxy: "pro-shop-wave.local"
  });

  watch(paths.styles.src, styles);
  watch(paths.scripts.src, scripts);
  watch(paths.images.src, images);
  watch(paths.php.src, php);
  watch(["**/*.php", "**/*.js", "**/*.css"]).on("change", browserSync.reload);
}

// **ビルドのクリーンアップ**
function clean() {
  return del(["css", "js", "images"]);
}

// **タスクの登録**
const build = series(clean, parallel(images, php, styles, scripts));
const serve = series(build, startAppServer);

exports.clean = clean;
exports.styles = styles;
exports.scripts = scripts;
exports.images = images;
exports.php = php;
exports.build = build;
exports.serve = serve;
exports.default = serve;
