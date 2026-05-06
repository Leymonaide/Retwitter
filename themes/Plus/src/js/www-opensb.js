(function(www) {
   /*
    * OpenSB configuration:
   */
   const SB_TARGET_ID = "sb-target";
   const SB_NOTIF_ID = "sb-button-notif";
   const SB_CLASS_NOTIF_OFF = "sb-notif-off";
   const SB_CLASS_NOTIF_ON = "sb-notif-on";
   const SB_USE_SERVER = true;
   const SB_SERVER_HOST = www.location.host;
   const SB_SERVER_API_PING = "/notif/ping";
   const SB_SERVER_API_GET = "/notif/get";
   const SB_SERVER_API_MARKREAD = "/notif/dismiss?all=true";
   const SB_SERVER_PING_ENABLED = true;
   const SB_SERVER_PING_INTERVAL = 60; // seconds
   /*
    * end configuration
   */
   var sbState = {};
   
   function createIframe(parent) {
      var iframe = document.createElement("iframe");
      parent.appendChild(iframe);
      iframe.contentWindow.document.open();
      iframe.contentWindow.document.write(
         "<html><head></head><body></body></html>"
      );
      iframe.contentWindow.document.close();
      return iframe;
   }
   
   async function waitForElementId(id) {
      while (document.getElementById(id) === null) {
         await new Promise(r => requestAnimationFrame(r));
      }
      return document.getElementById(id);
   }
   
   function instantiateSb(cfg) {
      
   }
   
   function initButtonNotif(elm) {
      
   }
   
   function initTarget(elm) {
      var targetFrame = createIframe(elm);
      sbState.targetFrame = targetFrame;
   }
   
   waitForElementId(SB_TARGET_ID)
      .then(elm => {
         initTarget(elm);
      });
   
   waitForElementId(SB_NOTIF_ID)
      .then(elm => {
         initButtonNotif(elm);
      });
})(window);