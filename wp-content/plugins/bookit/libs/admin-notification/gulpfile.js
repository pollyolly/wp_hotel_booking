let gulp = require('gulp');
let sass = require('gulp-sass')(require('sass'));
const browserSync = require('browser-sync').create();
const watch = require('gulp-watch');
let livereload = require('gulp-livereload');

function style_css() {
	return gulp.src('./assets/scss/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(gulp.dest('./assets/css'))
		.pipe(browserSync.stream());
}

gulp.task('watch', function () {

	livereload.listen();

	watch('./assets/scss/*.scss').on('change', (e) => {
		livereload.listen();
		style_css().pipe(livereload());
	});

});

gulp.task('build', style_css);
gulp.task('default', gulp.series(gulp.parallel('watch')));