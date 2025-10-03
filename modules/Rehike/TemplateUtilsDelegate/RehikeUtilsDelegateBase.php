<?php
namespace Rehike\TemplateUtilsDelegate;

use Rehike\ConfigManager\Config;
use Retwitter\Version\VersionController;
use Rehike\i18n\i18n;

use Rehike\Util\ParsingUtils;
use Rehike\Util\Base64Url;

use stdClass;

/**
 * Defines the `rehike` variable exposed to Twig-land.
 * 
 * This implements unique properties directly on the class. The child
 * class will implement all alias properties.
 * 
 * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
 * @author Isabella Lulamoon <kawapure@gmail.com>
 * @author The Rehike Maintainers
 */
#[\RetwitterKeepValue("Removed a lot of Rehike-specific code.")]
abstract class RehikeUtilsDelegateBase extends stdClass
{
    /**
     * Stores the current Rehike configuration.
     */
    public object $config;

    /**
     * Provides version information about the current Rehike setup.
     */
    public object $version;

    /**
     * Initialise all utilities.
     */
    public function __construct()
    {
        $this->config = Config::loadConfig();
        $this->version = VersionController::$versionInfo;
        $this->version->semanticVersion = VersionController::getVersion();
    }

    /**
     * Alias for ParsingUtils::getText() for templating use.
     */
    public static function getText(mixed $source): string
    {
        return ParsingUtils::getText($source) ?? "";
    }

    /**
     * Alias for ParsingUtils::getUrl() for templating use.
     */
    public static function getUrl(mixed $source): string
    {
        return ParsingUtils::getUrl($source) ?? "";
    }

    /**
     * Alias for ParsingUtils::getThumb() for templating use.
     */
    public static function getThumb(?object $obj, int $height = 0, bool $correctForShorts = false): string
    {
        if (null == $obj) return "//i.ytimg.com";
        
        return ParsingUtils::getThumb($obj, $height, $correctForShorts) ?? "//i.ytimg.com/";
    }

    /**
     * Alias for ParsingUtils::getThumbnailOverlay() for templating use.
     */
    public static function getThumbnailOverlay(object $array, string $name): ?object
    {
        return ParsingUtils::getThumbnailOverlay($array, $name);
    }

    /**
     * Convert an object to an associative array.
     * 
     * This is needed in order to iterate the keys of an object
     * in Twig. Twig only supports iterating associative arrays, not
     * objects.
     * 
     * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
     * @author The Rehike Maintainers
     * 
     * @param string $obj to cast
     * @return array of the casted object
     */
    public static function obj2arr($obj): array
    {
        return (array)$obj;
    }

    /**
     * Generate a template level RID.
     * 
     * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
     */
    public static function generateRid(): int
    {
        return rand(100000, 999999);
    }

    /**
     * Converts a string to a SafeHtml object.
     */
    public static function toSafeHtml(string $str): SafeHtml
    {
        return new SafeHtml($str);
    }
}