<?php
// This file is licensed under the Mozilla Public License 2.0 by The Rehike Maintainers.
declare(strict_types=1);
namespace Retwitter\DisableRetwitter;

use Rehike\{
    ConfigManager\Config,
    FileSystem,
    SimpleFunnel,
    i18n,
    SimpleFunnelResponse
};

/**
 * Allows to user to bypass Retwitter without disabling the Retwitter server
 * itself.
 * 
 * Based on Rehike\DisableRehike by:
 * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
 * @author The Rehike Maintainers
 */
class DisableRetwitter
{
    // Disable instantiation
    private function __construct() {}

    /**
     * Determines if Retwitter should be disabled or not.
     * 
     * Retwitter is disabled if any of the following conditions apply:
     *   - The configuration property "hidden.disableRetwitter" is set to true.
     *   - The URL contains a true ?disable_retwitter value.
     * 
     * Rehike is enabled if any of the following conditions apply:
     *   - The configuration property is false.
     *   - The URL contains a false ?disable_retwitter value.
     *   - The page is a Retwitter-specific page (/retwitter/ URL).
     */
    public static function shouldDisable(): bool
    {
        $cfg = Config::getConfigProp("hidden.disableRetwitter");
        $url = self::isRehikeUrl() ? false : self::getDisableRetwitterUrlState();

        // ?disable_retwitter=0, false with hidden.disableRehike should actually
        // enable Retwitter for that session only:
        if ($cfg == true && $url === false)
        {
            return false;
        }

        return $cfg == true || $url == true;
    }

    /**
     * Passes traffic for this session through to Polymer.
     */
    public static function disableForSession(): void
    {
        SimpleFunnel::funnelCurrentPage()->then(function (SimpleFunnelResponse $r)
        {
            $r->output();
        });
    }

    /**
     * Persistently enables Retwitter.
     */
    public static function enableRetwitter(): void
    {
        Config::setConfigProp("hidden.disableRetwitter", false);
        Config::dumpConfig();
    }

    /**
     * Persistently disables Retwitter.
     */
    public static function disableRetwitter(): void
    {
        Config::setConfigProp("hidden.disableRetwitter", true);
        Config::dumpConfig();
    }

    /**
     * Determines if the current URL requests to use Polymer instead of Rehike.
     */
    private static function isDisableRetwitterUrl(): bool
    {
        return isset($_GET["disable_retwitter"]);
    }

    /**
     * Determines if the current URL is a Rehike-specific page.
     */
    private static function isRehikeUrl(): bool
    {
        return strpos($_SERVER["REQUEST_URI"], "/retwitter/") === 0;
    }

    /**
     * Gets the ?enable_polymer statement.
     * 
     * If the value of the parameter is truthy (1 or true), then this will
     * return true. Else, it will return false.
     * 
     * If the current URL is not an ?enable_polymer URL, then this will return
     * null.
     */
    private static function getDisableRetwitterUrlState(): ?bool
    {
        if (self::isDisableRetwitterUrl())
        {
            $ep = $_GET["disable_retwitter"];
            return $ep == "1" || strtolower($ep) == "true";
        }

        return null;
    }
}