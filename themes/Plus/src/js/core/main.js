(function() {
   
window['sp'] = window['sp'] || {};
var state = {};
   
function addEvent(obj, type, fn) {
  if (obj.addEventListener) {
    obj.addEventListener(type, fn, false);
  }
  else if (obj.attachEvent) {
    obj["e"+type+fn] = fn;
    obj[type+fn] = function() {obj["e"+type+fn](window.event);}
    obj.attachEvent("on"+type, obj[type+fn]);
  }
  else {
    obj["on"+type] = obj["e"+type+fn];
  }
}
   
function addClass(elm, c) {
   try {
      if (!elm.hasAttribute('class')) {
         elm.setAttribute('class', c);
      } else {
         elm.setAttribute('class', 
            elm.getAttribute('class') + ' ' + c);
      }
   } catch(err) {
      reportError('Failed to add class ' + c);
   }
}

function removeClass(elm, c) {
   try {
      if (!elm.hasAttribute('class')) {
         reportError('Cannot remove a class from an element lacking one!');
      } else if (elm.getAttribute('class') == c) {
         elm.removeAttribute('class');
      } else {
         elm.setAttribute('class', 
            elm.getAttribute('class')
            .replaceAll(' ' + c, ''));
      }
   } catch(err) {
      reportError('Failed to remove class ' + c);
   }
}
   
function guideUpdateSelectedItem(itemid) {
   var guide = document.getElementById("guide");
   for (var i = 0, j = guide.children.length; i < j; i++) {
      removeClass(guide.children[i], "guide-item-selected");
   }
   if (!document.getElementById("guide-item-" + itemid)) {
      return;
   }
   addClass(document.getElementById("guide-item-" + itemid), "guide-item-selected");
}

window._ugi = guideUpdateSelectedItem;

/*
 * UIX components and shit
*/

var _uixRegistry = {};
var _uixIndex = 0;

function getUixIndex() {
   _uixIndex++;
   return _uixIndex;
}

function getUixRegistrations() {
   var response = [];
   $.each(_uixRegistry, (key, val) => response[response.length] = key);
   return response;
}

// $("html").on("click", "*", function() {
//    handleUixClick(this);
//    requestUixClickcardClose(this);
// });

function handleUixClick(e) {
   if (!e.classList) return;
   var reg = getUixRegistrations();
   for (var i = 0, j = e.classList.length; i < j; i++) {
      var curClass = e.classList[i];
      if (reg.includes(curClass)) {
         _uixRegistry[curClass]["click"](e);
      }
   }
}

// clickcard
var clickcardsRegistry = [];
function uixClickcardInitCard(e) {
   var content = e.querySelector(".uix-clickcard-content");
   content.style.display = "none";
   content.classList.remove("uix-clickcard-content");
   content.classList.add("uix-clickcard-card");
   content.id = "uix-clickcard-" + getUixIndex();
   document.body.appendChild(content);
   e._utgt = content;
   clickcardsRegistry.push(content);
}
function uixClickcardOnClick(e) {
   if (e._utgt === null || e._utgt === undefined) {
      uixClickcardInitCard(e);
   }
   var card = e._utgt;
   
   card.style.visibility = "hidden";
   var displayCache = card.style.display;
   card.style.display = "block";
   var tgt = e.querySelector(".uix-clickcard-target");
   var bdr = card.querySelector(".uix-clickcard-arrow");
   var x = (tgt.offsetLeft + tgt.offsetWidth - card.offsetWidth);
   var y = (tgt.offsetTop + tgt.offsetHeight + 16);
   card.style.visibility = "visible";
   card.style.display = displayCache;
   
   card.style.left = x + "px";
   card.style.top = y + "px";
   card.style.display = (card.style.display == "block") ? "none" : "block";
}
function requestUixClickcardClose(e) {
   if ((!e.offsetParent || e.offsetParent.id.indexOf("uix-clickcard") < 0) && e.className.indexOf("uix-clickcard") < 0) {
      clickcardsRegistry[i].style.display = "none";
   }
}
_uixRegistry['uix-clickcard'] = {
   "click": uixClickcardOnClick
};

/*
 * SPF PROGRESS
*/

var progress = null;

var position = -1;
var start = -1;
var timer = -1;

  // Animation states: start time, duration, progress complete, and css class.
var animation = {
  // Most progress waiting for response; duration is 3x expected to
  // accommodate slow networks and will be short-circuited by next step.
  REQUEST: [0, 300, '95%', 'waiting'],
  // Finish during short processing time.
  PROCESS: [100, 25, '101%', 'waiting'],
  // Fade it out slowly.
  DONE: [125, 150, '101%', 'done']
};



document.addEventListener('spfclick', initProgress);
document.addEventListener('spfrequest', handleRequest);
document.addEventListener('spfprocess', handleProcess);
document.addEventListener('spfdone', handleDone);


function setProgress(anim) {
  clearTimeout(timer);
  var elapsed = (new Date()).getTime() - start;
  var scheduled = anim[0];
  var duration = anim[1];
  var percentage = anim[2];
  var classes = anim[3];
  var wait = scheduled - elapsed;
  // Since navigation can often be faster than the animation,
  // wait for the last scheduled step of the progress bar to complete
  // before finishing.
  if (classes == 'done' && wait > 0) {
    timer = setTimeout(function() {
      setProgress(anim);
    }, wait);
    return;
  }
  progress.className = '';
  var ps = progress.style;
  ps.transitionDuration = ps.TransitionDuration = duration + 'ms';
  ps.width = percentage;
  if (classes == 'done') {
    // If done, set the class now to start the fade-out and wait until
    // the duration is over (i.e. the fade is complete) to reset the bar
    // to the beginning.
    progress.className = classes;
    timer = setTimeout(function() {
      ps.width = '0%';
    }, duration);
  } else {
    // If waiting, set the class after the duration is over (i.e. the
    // bar has finished moving) to set the class and start the pulse.
    timer = setTimeout(function() {
      progress.className = classes;
    }, duration);
  }
}

function initProgress(event) {
   progress = document.createElement("div");
   progress.setAttribute("id", "progress");
   progress.innerHTML = '<dt></dt><dd></dd>';
   document.body.appendChild(progress);
}


function handleRequest(event) {
  start = (new Date()).getTime();
  setProgress(animation.REQUEST);
}

function handleProcess(event) {
  setProgress(animation.PROCESS);
  //window.scroll(0,0);
}

function handleDone(event) {
  setProgress(animation.DONE);
  //handleScroll();
  clearProgress();
}

function clearProgress() {
  clearTimeout(timer);
  progress.className = '';
  var ps = progress.style;
  ps.transitionDuration = ps.TransitionDuration = '0ms';
  ps.width = '0%';
  progress = null;
  document.getElementById("progress").remove();
}
   
})();