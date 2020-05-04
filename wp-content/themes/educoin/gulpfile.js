'use strict';

var gulp        = require('gulp' );
var sass        = require('gulp-sass');
var del         = require('del');
var minifycss   = require('gulp-minify-css');
var rename      = require('gulp-rename');

gulp.task('default', ['clean', 'styles', 'watch']);



// ------------------------------------------------
// make front-end styles
gulp.task('styles', function () {
    'use strict';
    gulp.src('./css/sass/**/*.sass')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('css/'))
        .pipe(rename({suffix: '.min'}))
        .pipe(minifycss())
        .pipe(gulp.dest('css/'));
});

// clean the directory
gulp.task('clean', function(cb) {
    'use strict';
    del([
        '.sass-cache',
        'css/main.css',
        'css/main.min.css',
    ], cb);
});

// watch changes
gulp.task('watch', function () {
    'use strict';
    gulp.watch('css/sass/**/*.sass', ['styles']);
});