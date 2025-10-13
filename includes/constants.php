<?php

/*
 * DEVELOPER NOTICE:
 * 
 * Be careful modifying this file. Especially in the case of
 * changing or removing an option.
 * 
 * Make sure to check if these changes will not break anything.
 * 
 * Thank you.
 */

namespace Retwitter\Constants
{
    /**
     * Enables GitHub integration with some Retwitter features.
     * 
     * @var bool
     */
    const GH_ENABLED = true;

    /**
     * Specifies the GitHub repository to link to.
     * 
     * @var string
     */
    const GH_REPO = "lemon-pumpkin-pie/Retwitter";

    /** 
     * The current version of Retwitter.
     * 
     * @var string
     */
    const VERSION = "0.0.1";
    const VERSION_MAJOR_INT = 0;
    const VERSION_MINOR_INT = 0;
    const VERSION_SUB_INT   = 1;

    // Temporary testing constant:
    const CLIENT_TRANSACTION_TEST_STATIC = false;
    
    // Temporary testing constant:
    const TEST_SIGNIN = true;

    const TWITTER_HOST = "x.com";
    const API_HOST = "https://api.x.com";
    const API_VERSION = "1.1";

    /**
     * Specifies the default Nitter host.
     */
    const DEFAULT_NITTER_HOST = "nitter.net";
}

// DO NOT EDIT BELOW THIS LINE!!!
// For Rehike codebase compatibility:
namespace Rehike\Constants
{
    /**
     * Enables GitHub integration with some Rehike features.
     * 
     * @var bool
     */
    const GH_ENABLED = false;

    /**
     * Specifies the GitHub repository to link to.
     * 
     * @var string
     */
    const GH_REPO = "Rehike/Rehike";

    /** 
     * The current version of Rehike.
     * 
     * @var string
     */
    const VERSION = "0.8.3";
    const VERSION_MAJOR_INT = 0;
    const VERSION_MINOR_INT = 8;
    const VERSION_SUB_INT   = 3;

    /**
     * Is the current Rehike build a release build?
     * 
     * @var bool
     */
    const IS_RELEASE = false;

    /** 
     * The location of views (templates) relative to the root.
     * 
     * @deprecated Any attempt to use this constant in Retwitter is illegal.
     * @var string
     */
    const VIEWS_DIR = "NOT APPLICABLE";
}