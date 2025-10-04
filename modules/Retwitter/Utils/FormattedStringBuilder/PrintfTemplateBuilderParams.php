<?php
// This file is licensed under the Mozilla Public License 2.0 by The Rehike Maintainers.
declare(strict_types=1);
namespace Retwitter\Utils\FormattedStringBuilder;

/**
 * Arguments for the printf template builder method.
 * 
 * @author Isabella Lulamoon <kawapure@gmail.com>
 * @author The Rehike Maintainers
 */
class PrintfTemplateBuilderParams
{
    public function __construct(
            public string $runText, 
            public int $runCreationFlags = 0, 
            public string $linkText = "", 
            public array $extraData = [] // reserved
    ) {}
}