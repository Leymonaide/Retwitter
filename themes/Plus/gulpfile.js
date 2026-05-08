const gulp = require('gulp');
const fs = require('fs');
const spritesmith = require('gulp.spritesmith');
const through2 = require('through2');

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
   
   var _backgrounds = [...contents.matchAll(/background(-image)?:\s*url\(.*?\)/g)];
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
         var spritesheet = backgrounds[i].replace(/(background(-image)?:)|(url\()|(\))/g, "").replace(spriteSrcDir, "").split("/")[0];
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

gulp.task('css', function(cb) {
   return gulp.src('s/cssbin/*.css')
      .pipe(through2.obj(function(file, _, cb) {
         console.log("[css] " + file.path);
         this.push(file);
         cb();
      }))
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
          setTimeout(function() {cb();}, 500)
       })
    }
});

exports.default = gulp.series('sprites', 'css');