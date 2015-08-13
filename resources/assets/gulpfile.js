var gulp = require('gulp');
var less = require('gulp-less');
var path = require('path');
var browserify = require('browserify');
var watchify = require('watchify');
var coffee_reactify = require('coffee-reactify');

var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var gutil = require('gulp-util');
var sourcemaps = require('gulp-sourcemaps');
var assign = require('lodash.assign');


// ---------------------------------------------------------------
// browserify

// add custom browserify options here
var customOpts = {
  entries: ['./coffee/index.coffee'],
  debug: true,
  extensions: ['.coffee','.cjsx']
};
var opts = assign({}, watchify.args, customOpts);

function browserifyBundle(b) {
  return b.bundle()
    // log errors if they happen
    .on('error', gutil.log.bind(gutil, 'Browserify Error'))
    .pipe(source('app.js'))
    .pipe(buffer())
    .pipe(sourcemaps.init({loadMaps: true})) // loads map from browserify file
    // Add transformation tasks to the pipeline here.
    .pipe(sourcemaps.write('./')) // writes .map file
    .pipe(gulp.dest('../../public/js'));
}


// ---------------------------------------------------------------
// tasks

gulp.task('default', ['js','less']);

gulp.task('js', function() {
  // run the watchify bundler
  var b = browserify(opts); 

  // add transformations
  b.transform(coffee_reactify);

  // browserify
  browserifyBundle(b);
});

gulp.task('less', function () {
  return gulp.src('./less/*.less')
    .pipe(less({
      paths: [ path.join(__dirname, 'less', 'includes') ]
    }))
    .pipe(gulp.dest('../../public/css'));
});


gulp.task('watch', function() {
    // Watch .less files
    gulp.watch('./less/**/*.less', ['less']);

    // decorate browserify with watchify
    var b = watchify(browserify(opts)); 

    // add transformations
    b.transform(coffee_reactify);

    b.on('update', function() { browserifyBundle(b); }); // on any dep update, runs the bundler
    b.on('log', gutil.log); // output build logs to terminal

    browserifyBundle(b);
});

