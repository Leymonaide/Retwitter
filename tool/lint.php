<?php
/*
 * This file is part of the Retwitter project.
 * Shared from the Rehike project.
 * Copyright (c) 2025-2026 Leymonaide, The Rehike Maintainers.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at https://mozilla.org/MPL/2.0/.
 */

declare(strict_types=1);
namespace RehikeTool;

use RehikeTool\Linter\Linter;
use Retwitter\TemplateManager;

require "tool_base.php";
require "class/lint.php";

// Currently we need to do this in order to lint templates. This is kinda
// indicative of bad code, but it's fine...
TemplateManager::addFilter(
    "safeHtml",
    function() {},
);

// Currently we'll just test the linter with these parameters:
Linter::lintTwigTemplate("profile.twig");
Linter::lintTwigTemplate("common/timeline.twig");
Linter::lintTwigTemplate("common/grid_timeline.twig");
Linter::lintTwigTemplate("common/sidebar_modules.twig");
Linter::lintTwigTemplate("common/user_actions.twig");
Linter::lintTwigTemplate("common/user_small_list.twig");
Linter::lintTwigTemplate("common/pageframe/topbar.twig");
Linter::lintTwigTemplate("core/macros.twig");
Linter::lintTwigTemplate("home.twig");
Linter::lintTwigTemplate("static_logged_out_home.twig");