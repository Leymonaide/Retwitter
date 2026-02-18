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

$g_toolFilePath = __FILE__;
$g_rehikeBaseFolder = dirname(dirname($g_toolFilePath));

$_SERVER["DOCUMENT_ROOT"] = $g_rehikeBaseFolder;

set_include_path($g_rehikeBaseFolder);
require_once "includes/rehike_autoloader.php";
require_once "vendor/autoload.php";