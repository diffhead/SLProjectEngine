var gulp = require("gulp");
var browserify = require("browserify");
var source = require("vinyl-source-stream");
var buffer = require('vinyl-buffer');
var tsify = require("tsify");
var sass = require('gulp-sass')(require('sass'));
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var cleanCSS = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');

function bundleTs(dev = true) {
    let pipeline = browserify({
        basedir: ".",
        debug: dev,
        entries: ["src/ts/index.ts"],
        cache: {},
        packageCache: {},
    })
    .plugin(tsify)
    .bundle()
    .pipe(source("index.js"));

    if ( dev === false ) {
        pipeline = pipeline
            .pipe(buffer())
            .pipe(uglify());
    }

    return pipeline.pipe(gulp.dest('bundle'));
}

function bundleScss(dev = true) {
    let pipeline = gulp.src('src/scss/index.scss');

    if ( dev ) {
        pipeline = pipeline.pipe(sourcemaps.init());
    }

    pipeline = pipeline
        .pipe(concat('index.css'))
        .pipe(sass().on('error', sass.logError));

    if ( dev === false ) {
        pipeline = pipeline
            .pipe(cleanCSS());
    }

    if ( dev ) {
        pipeline = pipeline.pipe(sourcemaps.write());
    }

    return pipeline.pipe(gulp.dest('bundle'));
}

//gulp.task("copy-html", copyHtml);
gulp.task(
    "build-dev",
    gulp.series(
        gulp.parallel(
            () => { return bundleTs(true) },
            () => { return bundleScss(true) }
        )
    )
);

gulp.task("default", gulp.series("build-dev"));

gulp.task("build-ts-dev", () => { return bundleTs(true) });
gulp.task("build-ts-prod", () => { return bundleTs(false) });
gulp.task("build-scss-dev", () => { return bundleScss(true) });
gulp.task("build-scss-prod", () => { return bundleScss(false) });

gulp.task("build-prod", gulp.parallel(
    () => { return bundleTs(false) },
    () => { return bundleScss(false) }
));

gulp.task("watch", function() {
    gulp.watch('src/scss/**/*.scss', gulp.series('build-scss-dev'));
    gulp.watch('src/ts/**/*.ts', gulp.series('build-ts-dev'));
});
