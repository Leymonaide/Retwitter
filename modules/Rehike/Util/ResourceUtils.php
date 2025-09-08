<?php
namespace Rehike\Util;

use Rehike\FileSystem;
use Rehike\ResourceConstantsStore;
use Rehike\UserPrefs\{
    UserPrefs,
    UserPrefFlags,
};

use Rehike\Util\Exception\ResourceUtils\BadResourceException;

/**
 * Provides helper functions for getting resource locations.
 * 
 * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
 * @author Isabella Lulamoon <kawapure@gmail.com>
 * @author The Rehike Maintainers
 */
class ResourceUtils
{
    /**
     * Get the CSS path of a resource from its name.
     */
    #[\RetwitterKeepValue("Removed YouTube Hi-DPI preference code.")]
    public static function cssPath(string $name): string
    {
        $constants = ResourceConstantsStore::get();

        if (isset($constants->css->{$name}))
        {
            return $constants->css->{$name};
        }
        else
        {
            throw new BadResourceException(
                "Unknown CSS resource name \"$name\""
            );
        }
    }

    /**
     * Get the JS path of a resource from its name.
     */
    public static function jsPath(string $name): string
    {
        $constants = ResourceConstantsStore::get();

        if (isset($constants->js->{$name}))
        {
            return $constants->js->{$name};
        }
        else
        {
            throw new BadResourceException(
                "Unknown JS resource name \"$name\""
            );
        }
    }

    /**
     * Get the path of a static image resource from its name.
     */
    public static function imgPath(string $name): string
    {
        $constants = ResourceConstantsStore::get();

        if (isset($constants->img->{$name}))
        {
            return $constants->img->{$name};
        }
        else
        {
            throw new BadResourceException(
                "Unknown image resource name \"$name\""
            );
        }
    }
    
    /**
     * Get the path of any custom Rehike resource which is versioned.
     */
    #[\RetwitterKeepValue("Removed YouTube Hi-DPI preference code.")]
    public static function resolveVersioned(string $name, bool $prefixUri = true): string
    {
        $constants = ResourceConstantsStore::getVersionMap();
        
        if ($prefixUri)
        {
            $lookupUri = "static/$name";
        }
        else
        {
            $lookupUri = $name;
        }
        
        $resultName = $lookupUri;
        
        if (isset($constants->{$lookupUri}))
        {
            $resultName = $constants->{$lookupUri};
        }
        
        return $prefixUri ? "/rehike/$resultName" : $resultName;
    }
}