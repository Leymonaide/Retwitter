<?php
// This file defines attributes used by Retwitter source control tools.
// These attributes do not affect runtime.

/**
 * The value of this property was changed in Retwitter, and should be maintained
 * when merging upstream Rehike code.
 */
#[\Attribute]
class RetwitterKeepValue
{
    public function __construct(string $reason = "") {}
}

/**
 * A property on this class has been changed to a different value.
 * 
 * The "from" parameter specifies the old name from the Rehike codebase, and the
 * "to" parameter specifies the new name for Retwitter. If the type changes
 * without modification to the variable name, then specify the same value for
 * both.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class RetwitterChangedProperty
{
    public function __construct(string $from, string $to, string $reason = "") {}
}