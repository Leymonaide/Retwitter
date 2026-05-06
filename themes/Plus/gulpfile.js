const srcDir = "src";
const outDir = "s";

const gulp = require('gulp');
const fs = require('fs');
const fs2 = require('fs-extra');
const spritesmith = require('gulp.spritesmith');
const rename = require('gulp-rename');
const gulpTap = require('gulp-tap');
const print = require('gulp-print').default;
const sass = require('gulp-sass')(require('sass'));
const cssmin = require('gulp-cssmin');
const cssshorthand = require('gulp-shorthand');
const through2 = require('through2');
const babel = require('gulp-babel-minify');
const path = require('path');
const gulpReplace = require('gulp-replace');
const {join} = require('path');

function vflGenerateRid() {
   var result           = '';
   var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
   var charactersLength = characters.length;
   for ( var i = 0; i < 6; i++ ) {
      result += characters.charAt(
         Math.floor(
            Math.random() * 
            charactersLength
         )
      );
   }
   return result.substr(0, 6);
};

function generateVflFilename(dir) {
   var dirname = path.dirname(dir),
       basename = path.basename(dir),
       extname = path.extname(dir);
   // 2026(isabella): Would be fighting against this if it were kept, so it's going away.
   //return dirname + "/" + basename.replace(extname, "") + "-vfl" + vflGenerateRid() + extname;
   return dirname + "/" + basename.replace(extname, "") + extname;
}

function requestAnimationFrame(f){
  setImmediate(()=>f(Date.now()))
}
if (!String.prototype.replaceAll) {
	String.prototype.replaceAll = function(str, newStr){

		// If a regex pattern
		if (Object.prototype.toString.call(str).toLowerCase() === '[object regexp]') {
			return this.replace(str, newStr);
		}

		// If a string
		return this.replace(new RegExp(str, 'g'), newStr);

	};
}

async function waitForFile(path) {
   try {
      var file = fs.readFileSync(path, {encoding: "utf8"});
   } catch (Exception) {
      var file = "{}";
   }
   var to = 0;
   while (file == "") {
      try {
         var file = fs.readFileSync(path, {encoding: "utf8"});
      } catch (Exception) {
         var file = "{}";
      }
      to++;
      if (to > 16000) {
         return "{}";
      }
      await new Promise(r => requestAnimationFrame(r));
   }
   return file;
}

function repointSprites(css) {
   
   // SUCH A HUGE FUCKING MESS
   // REWRITE THIS LATER LOL
   
   var contents = css.contents.toString();
   
   //contents = contents.replace(/background/g, "loltest");
   
   var _backgrounds = [...contents.matchAll(/background:[ ]?url\(.*?\)/g)];
   var backgrounds = [];
   var replacement = [];
   //console.log(backgrounds[0]);
   for (var i = 0, j = _backgrounds.length; i < j; i++) {
      if (_backgrounds[i][0] !== undefined) {
         backgrounds[backgrounds.length] = _backgrounds[i][0];
      }
   }
   
   for (var i = 0, j = backgrounds.length; i < j; i++) {
      if (backgrounds[i].indexOf(".sprites") > -1) {
         var spriteSrcDir = "/src/img/";
         var spritesheet = backgrounds[i].replace(/(background:)|(url\()|(\))/g, "").replace(spriteSrcDir, "").split("/")[0];
         var sprite = backgrounds[i].split(spritesheet + "/")[1].replace(".png", "").replace(")", "");
         
         var spritesheetInfo = fs.readFileSync('s/tmp/' + spritesheet + ".json");
         //console.log(i);
         spritesheetInfo = JSON.parse(spritesheetInfo);
         //console.log(spritesheetInfo[sprite]);
         if (spritesheetInfo[sprite]) {
            var ssinfox = spritesheetInfo[sprite].x;
            var ssinfoy = spritesheetInfo[sprite].y;
            //console.log(ssinfox, ssinfoy);
         } else {
            var ssinfox = 0, ssinfoy = 0;
         }
         
         var spriteOffsetX = (ssinfox == 0) ? "0" : "-" + (ssinfox) + "px";
         var spriteOffsetY = (ssinfoy == 0) ? "0" : "-" + (ssinfoy) + "px";
         
         var output = "background:url(/rehike/static/theme/kawapure_plus/imgbin/" + spritesheet.replace(".sprites", "") + ".png) " + spriteOffsetX + " " + spriteOffsetY;
         
         //console.log(output);
         
         replacement[replacement.length] = output;
      } else {
         replacement[replacement.length] = backgrounds[i].replace("(/src/img/", "(/rehike/static/theme/kawapure_plus/imgbin/");
      }
   }
   
   for (var i = 0, j = backgrounds.length; i < j; i++) {
      contents = contents.replace(backgrounds[i], replacement[i]);
   }
   
   css.contents = Buffer.from(contents, 'utf8');
   return css;
}

function getsprites() {
   // wrapper for spritesheetiser
   return through2.obj(function(file, _, cb) {
      this.push(repointSprites(file));
      cb();
   });
}

function getSpritesheetName(a) {
   return a;
}

/*
function sprites2313() {
   var nameHack;
   var sData = src(srcDir + '/img/**.sprites/*.png')
      .pipe(gulpTap(function(f){
         nameHack = f.path;
         return nameHack;
      }))
      .pipe(spritesmith({
         imgName: nameHack,
         cssName: "sprite.css"
      }));
   var imgStream = sData.img
      .pipe(rename(p => {
         p.basename += "-vfl" + vflGenerateRid()
      }))
      .pipe(dest("s/imgbin/"));
   
   return imgStream;
}
// */

gulp.task('logRegistrations', function(cb) {
   cb();
});

gulp.task('vflise', function(cb) {
   var pre = [], post = [], read = [];
   
   const isDirectory = path => fs.statSync(path).isDirectory();
   const getDirectories = path =>
      fs.readdirSync(path).map(
         name => join(path, name)
      ).filter(isDirectory);
   const isFile = path => fs.statSync(path).isFile();
   const getFiles = path =>
      fs.readdirSync(path).map(
         name => join(path, name)
      ).filter(isFile);
   const getFilesRecursively = (path) => {
      let dirs = getDirectories(path);
      let files = dirs
         .map(dir => getFilesRecursively(dir))
         .reduce((a, b) => a.concat(b), []);
      return files.concat(getFiles(path));
   }
   
   var files = getFilesRecursively('s/tmpbin');
   var read = getFilesRecursively('compiledTemplates');
   var fileslen = files.length;
   for (var i = 0, j = fileslen; i < j; i++) {
      // windows fix
      files[i] = files[i].replace(/\\/g, "/");
      if (path.extname(files[i]) == '.js' || path.extname(files[i]) == '.css') {
         read[read.length] = files[i]
      }
   }
   pre = files;
   for (var i = 0, j = fileslen; i < j; i++) {
      post[i] = generateVflFilename(pre[i].replace("s/tmpbin", "s"));
   }
   
   for (var i = 0, j = read.length; i < j; i++) {
      var contents = fs.readFileSync(read[i], {encoding: "utf8"});
      for (var o = 0; o < fileslen; o++) {
         contents = contents.replaceAll(pre[o], post[o]);
      }
      fs.writeFileSync(read[i], contents);
   }
   
   function copyFileSyncRecursive(src, dest, mode) {
     const folders = dest.split('/').slice(0, -1)
     if (folders.length) {
       // create folder path if it doesn't exist
       folders.reduce((last, folder) => {
         const folderPath = last ? last + '/' + folder : folder
         if (!fs.existsSync(folderPath)) {
           fs.mkdirSync(folderPath)
         }
         return folderPath
       })
     }
     fs.copyFileSync(src, dest, mode)
   }
   
   for (var i = 0; i < fileslen; i++) {
      copyFileSyncRecursive(pre[i], post[i]);
   }
   
   cb();
});

gulp.task('templates', function(cb) {
   cb(); // 2026(isabella): Not necessary.
   return;
   
   function copyFolderSync(from, to) {
      if (!fs.existsSync(to)) {
         fs.mkdirSync(to);
      }
      fs.readdirSync(from).forEach(element => {
          if (fs.lstatSync(path.join(from, element)).isFile()) {
             fs.copyFileSync(path.join(from, element), path.join(to, element));
          } else {
             copyFolderSync(path.join(from, element), path.join(to, element));
          }
      });
   }
   copyFolderSync('template', 'compiledTemplates');
   
   gulp.src('compiledTemplates/**.twig', { base: "./" })
      .pipe(gulpReplace(/\"\/src\/(css|js|img)\/.*?\"/g, function(match) {
         match = match.replace('/src/', '/s/tmpbin/');
         match = match.split('/');
         match[3] = match[3] + "bin";
         match[match.length - 1] = (function(a) {
            var b = path.extname(a);
            if (b.indexOf('scss') > -1) {
               return a.replace(".scss", ".css");
            } else {
               return a;
            }
         })(match[match.length - 1]);
         match = match.join("/");
         return match;
      }))
      .pipe(gulpReplace(/\{#vfldate#\}.*?\{#\/vfldate#\}/g, function() {
         return (new Date).toISOString().slice(0,10).replace(/-/g,"");
      }))
      .pipe(gulp.dest('.'));
   
   setTimeout(function() {cb();}, 1500)
   
})

gulp.task('js', function(cb) {
   return gulp.src('src/js/*.js')
      .pipe(through2.obj(function(file, _, cb) {
         console.log("[js] " + file.path);
         this.push(file);
         cb();
      }))
      .pipe(babel())
      .pipe(gulp.dest("s/jsbin"));
      //.end(cb);
   //setTimeout(function() {cb();}, 500)
});

gulp.task('css', function(cb) {
   return gulp.src('src/css/*.scss')
      .pipe(through2.obj(function(file, _, cb) {
         console.log("[css] " + file.path);
         this.push(file);
         cb();
      }))
      .pipe(sass())
      .pipe(cssshorthand())
      .pipe(cssmin())
      .pipe(getsprites())
      .pipe(gulp.dest('s/cssbin'))
      //.end(cb);
});

gulp.task('sprites', function(cb) {

    // Multiple folders solution taken from here
    // https://github.com/gulpjs/gulp/blob/master/docs/recipes/running-task-steps-per-folder.md

    var sprite_path = {
        src: 'src/img/',
        dest: 's/imgbin/'
    }

    var folders = (function(s){
       return fs.readdirSync(s).filter(function(f) {
          return fs.statSync(s+"/"+f).isDirectory();
       }).filter(function(a) {
          return a.indexOf(".sprites") > -1;
       })
    })(sprite_path.src);

    var tasks = folders.map(function(folder) {
        makeSprites(folder);
    });

    function makeSprites(folder) {
        var spriteData = gulp.src(sprite_path.src + folder + '/**/*.png')
            .pipe(through2.obj(function(file, _, cb) {
               console.log("[sprites] " + file.path);
               this.push(file);
               cb();
            }))
            .pipe(spritesmith({
                padding: 2,
                algorithm: 'binary-tree',
                imgName: folder.replace(".sprites", "") + '.png',
                cssName: folder + '.json'
            }))
            .on('error', function(err) {
                console.log(err)
            });
        var imgStream = spriteData.img
            .pipe(gulp.dest("s/imgbin"));
        var cssStream = spriteData.css
            .pipe(gulp.dest("s/tmp/"));
            
      var completed = 0;
        
      function signalComplete()
      {
         if (++completed == 2)
         {
            // Does not work.
            //cb();
         }   
      }
       // fucking async hacks
       waitForFile("s/tmp/" + folder + ".json").then(function() {
          setTimeout(function() {cb();}, 5000)
       })
    }
});

function promisifyStream(stream) {
    return new Promise( res => stream.on('end',res));
}

function defaultTask(cb) {
   cb();
}

exports.default = gulp.series('sprites', 'css', 'js'/*, 'templates', 'vflise'*/);