/**
 * @fileoverview Fix for canopy layout reflow not acknowledging CSS animation
 *               events.
 * 
 * @see https://github.com/Leymonaide/Retwitter/issues/2
 */

(function(){
    var g_canopyElement;
    var g_canopyFixAnimationId = 0;

    function forceCanopyPositionRefresh()
    {
        var event = new CustomEvent("viewport-update", {
            detail: {},
            bubbles: true,
            cancelable: true
        });

        g_canopyElement.dispatchEvent(event);
        g_canopyFixAnimationId = requestAnimationFrame(
            forceCanopyPositionRefresh
        );
    }

    function hookProfileCanopyRenderer()
    {
        var canopy = document.querySelector(".ProfileCanopy");

        if (!canopy)
        {
            return;
        }

        g_canopyElement = canopy;

        canopy.addEventListener("transitionstart", function(e)
        {
            forceCanopyPositionRefresh();
        });

        canopy.addEventListener("transitionend", function(e)
        {
            cancelAnimationFrame(g_canopyFixAnimationId);
        });
    }

    // Fix if the page changes:
    document.documentElement.addEventListener(
        "uiPageChanged",
        function(e)
        {
            hookProfileCanopyRenderer()
        }
    );

    // Fix if the initial page is affected:
    hookProfileCanopyRenderer();
})();