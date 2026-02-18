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

function includeAllFiles(): void
{
    foreach (glob("../models/*") as $folder)
        includeAllFromTree($folder);
}

function includeAllFromTree(string $root): void
{
    if (str_ends_with($root, ".php"))
    {
        echo "Including \"$root\"..." . PHP_EOL;
        include_once $root;
    }
    else if (is_dir($root))
    {
        foreach (glob("$root/*") as $nextRoot)
            includeAllFromTree($nextRoot);
    }
}