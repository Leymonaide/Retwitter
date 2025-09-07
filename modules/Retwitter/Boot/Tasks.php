<?php
// This file is licensed under the Mozilla Public License 2.0 by The Rehike Maintainers.
namespace Retwitter\Boot;

use Rehike\{
    ContextManager,
    YtApp,
    Network,
    Network\NetworkCore,
    Async\Concurrency,
    Async\Promise,
    ControllerV2\Core as ControllerV2,
    Player\PlayerCore,
    TemplateUtilsDelegate\RehikeUtilsDelegate,
    ResourceConstantsStore,
    ConfigManager\Config,
    ConfigManager\LoadConfigException,
    Util\Nameserver\Nameserver,
    Util\Base64Url,
    i18n\BootServices as i18nBoot,
    i18n\i18n,
    ErrorHandler\ErrorHandler,
    InnertubeContext
};

use Rehike\Debugger\Debugger;
use Retwitter\{
    ConfigDefinitions,
    TemplateManager,
};

use Rehike\SignInV2\SignIn;
use Retwitter\Context\AppContext;
use Retwitter\Utils\RetwitterUtilsDelegate;

/**
 * Implements boot tasks for Retwitter.
 */
final class Tasks
{
    public static function initDebugger(): void
    {
        // The Debugger currently gets AppContext thunked to it via the Rehike
        // YtApp interface.
        Debugger::init(YtApp::getInstance());
    }

    public static function initNetwork(): void
    {
        NetworkCore::setResolve([
            Nameserver::get("x.com", "1.1.1.1", 443)->serialize()
        ]);
    }

    public static function initResourceConstants(): void
    {
        ResourceConstantsStore::init();
    }

    public static function initConfigManager(): void
    {
        Config::registerConfigDefinitions(
            ConfigDefinitions::getConfigDefinitions()
        );
        
        try
        {
            Config::loadConfig();
        }
        catch (LoadConfigException $e)
        {
            $reason = $e->getReason();
            
            if ($reason == LoadConfigException::REASON_COULD_NOT_OPEN_FILE_HANDLE)
            {
                // macOS and Linux may have default permissions on the htdocs
                // folder which don't permit the PHP script to write files. In
                // this case, we have no choice other than to display an error
                // message to the user telling them to change their permissions.
                ErrorHandler::reportFailedToWriteConfig();
            }
        }

        // Apply early configuration properties for other modules:
        if (Config::getConfigProp("advanced.developer.ignoreUnresolvedPromises"))
        {
            \Rehike\Async\Promise\PromiseResolutionTracker::disable();
        }
    }

    public static function setupTemplateManager(): void
    {
        TemplateManager::registerGlobalState(AppContext::getInstance());

        $utils = new RetwitterUtilsDelegate();
        TemplateManager::addGlobal("retwitter", $utils);
    }

    public static function setupI18n(): void
    {
        // i18n v2
        i18nBoot::boot();

        // Also expose common messages to the global variable.
        YtApp::getInstance()->msgs = 
            (array)i18n::getAllTemplates("global");
    }

    public static function setupControllerV2(): void
    {
        ControllerV2::setRedirectHandler(
            require "includes/spf_redirect_handler.php"
        );
    }
}