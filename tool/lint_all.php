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

require "tool_base.php";
require "include_all.php";
require "class/lint.php";

includeAllFiles();

foreach (get_declared_classes() as $class)
{
    Linter\Linter::lintClass($class);
}