'use strict';

import plugins  from 'gulp-load-plugins';
import yargs    from 'yargs';
import browser  from 'browser-sync';
import gulp     from 'gulp';
import panini   from 'panini';
import rimraf   from 'rimraf';
import sherpa   from 'style-sherpa';
import yaml     from 'js-yaml';
import fs       from 'fs';
import ghPages  from 'gulp-gh-pages';
import rename   from 'gulp-rename';
import svgstore from 'gulp-svgstore';
import svgmin   from 'gulp-svgmin';
import inject   from 'gulp-inject';

// Load all Gulp plugins into one variable
const $ = plugins();

// Check for --production flag
const PRODUCTION = !!(yargs.argv.production);

// Load settings from settings.yml
const { COMPATIBILITY, PORT, UNCSS_OPTIONS, PATHS } = loadConfig();

function loadConfig() {
  let ymlFile = fs.readFileSync('config.yml', 'utf8');
  return yaml.load(ymlFile);
}


//SVG
gulp.task('svgicons', function () {
  var svgs = gulp
        .src('src/assets/icons/*.svg')
        .pipe(rename({prefix: 'icon-'}))
        //.pipe(svgmin())
        .pipe(svgstore({ inlineSvg: true }));

  function fileContents (filePath, file) {
      return file.contents.toString();
  }

  return gulp
      .src('src/partials/svgicons.html')
      .pipe(inject(svgs, { transform: fileContents }))
      .pipe(gulp.dest('src/partials'));
});


// Build the "dist" folder by running all of the below tasks
gulp.task('build',
 gulp.series(clean, 'svgicons', gulp.parallel(pages, sass, javascript, images, copy), styleGuide));

// Build the site, run the server, and watch for file changes
gulp.task('default',
  gulp.series('build', server, watch));


// Build & Deploy the "dist" folder by running all of the below tasks
gulp.task('deploy',
 gulp.series('build', copyCname, githubDeploy ));


// Deploy site
function githubDeploy() {
  return gulp.src('./dist/**/*')
    .pipe(ghPages());
};


// Copy CNAME
function copyCname() {
  return gulp.src('CNAME')
    .pipe(gulp.dest(PATHS.dist));
}


// Delete the "dist" folder
// This happens every time a build starts
function clean(done) {
  rimraf(PATHS.dist, done);
}

// Copy files out of the assets folder
// This task skips over the "img", "js", and "scss" folders, which are parsed separately
function copy() {
  return gulp.src(PATHS.assets)
    .pipe(gulp.dest(PATHS.dist + '/assets'));
}

// Copy page templates into finished HTML files
function pages() {
  return gulp.src('src/pages/**/*.{html,hbs,handlebars}')
    .pipe(panini({
      root: 'src/pages/',
      layouts: 'src/layouts/',
      partials: 'src/partials/',
      data: 'src/data/',
      helpers: 'src/helpers/'
    }))
    .pipe(gulp.dest(PATHS.dist));
}

// Load updated HTML templates and partials into Panini
function resetPages(done) {
  panini.refresh();
  done();
}

// Generate a style guide from the Markdown content and HTML template in styleguide/
function styleGuide(done) {
  sherpa('src/styleguide/index.md', {
    output: PATHS.dist + '/styleguide.html',
    template: 'src/styleguide/template.html'
  }, done);
}

// Compile Sass into CSS
// In production, the CSS is compressed
function sass() {
  return gulp.src('src/assets/scss/app.scss')
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      includePaths: PATHS.sass
    })
      .on('error', $.sass.logError))
    .pipe($.autoprefixer({
      browsers: COMPATIBILITY
    }))
    // Comment in the pipe below to run UnCSS in production
    //.pipe($.if(PRODUCTION, $.uncss(UNCSS_OPTIONS)))
    .pipe($.if(PRODUCTION, $.cssnano()))
    .pipe($.if(!PRODUCTION, $.sourcemaps.write()))
    .pipe(gulp.dest(PATHS.dist + '/assets/css'))
    .pipe(browser.reload({ stream: true }));
}

// Combine JavaScript into one file
// In production, the file is minified
function javascript() {
  return gulp.src(PATHS.javascript)
    .pipe($.sourcemaps.init())
    .pipe($.babel())
    .pipe($.concat('app.js'))
    .pipe($.if(PRODUCTION, $.uglify()
      .on('error', e => { console.log(e); })
    ))
    .pipe($.if(!PRODUCTION, $.sourcemaps.write()))
    .pipe(gulp.dest(PATHS.dist + '/assets/js'));
}

// Copy images to the "dist" folder
// In production, the images are compressed
function images() {
  return gulp.src('src/assets/img/**/*')
    .pipe($.if(PRODUCTION, $.imagemin({
      progressive: true
    })))
    .pipe(gulp.dest(PATHS.dist + '/assets/img'));
}

// Start a server with BrowserSync to preview the site in
function server(done) {
  browser.init({
    server: PATHS.dist, port: PORT,
    notify: {
      styles: {
          top: 'auto',
          bottom: '0',
          margin: '0px',
          padding: '5px',
          position: 'fixed',
          fontSize: '10px',
          zIndex: '9999',
          borderRadius: '5px 0px 0px',
          color: 'white',
          textAlign: 'center',
          display: 'block',
          backgroundColor: 'rgba(57, 97, 118, 0.498039)'
      }
    }
  });
  done();
}

// Reload the browser with BrowserSync
function reload(done) {
  browser.reload();
  done();
}

// Watch for changes to static assets, pages, Sass, and JavaScript
function watch() {
  gulp.watch(PATHS.assets, copy);
  gulp.watch('src/pages/**/*.html').on('change', gulp.series(pages, browser.reload));
  gulp.watch('src/{layouts,partials}/**/*.html').on('change', gulp.series(resetPages, pages, browser.reload));
  gulp.watch('src/assets/scss/**/*.scss', sass);
  gulp.watch('src/assets/js/**/*.js').on('change', gulp.series(javascript, browser.reload));
  gulp.watch('src/assets/img/**/*').on('change', gulp.series(images, browser.reload));
  gulp.watch('src/styleguide/**').on('change', gulp.series(styleGuide, browser.reload));
  gulp.watch('src/assets/icons/**/*.svg').on('change', gulp.series('svgicons', resetPages, pages, browser.reload));
}
