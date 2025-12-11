<?php
// This file is licensed under the Mozilla Public License 2.0 by The Rehike Maintainers.
declare(strict_types=1);
namespace Retwitter\Utils\FormattedStringBuilder;

use Retwitter\ComplexLink;
use stdClass;

/**
 * A single run in a formatted string.
 * 
 * @author Isabella Lulamoon <kawapure@gmail.com>
 * @author The Rehike Maintainers
 */
class RunBuilder extends stdClass
{
    public string $text = "";
    public bool $bold = false;
    public bool $italic = false;
    public ?string $url = null;
    public ?ComplexLink $complexLink = null;
    
    public function build(): object
    {
        $out = (object)[];
        
        $out->text = $this->text;
        
        if ($this->bold)
        {
            $out->bold = true;
        }
        
        if ($this->italic)
        {
            $out->italic = true;
        }
        
        if (null != $this->url)
        {
            $out->url = $this->url;
        }

        if (null != $this->complexLink)
        {
            $out->complexLink = $this->complexLink;
        }

        // TODO(pumpkin): I'm pretty sure the whole reason this class extends
        // stdClass is for custom properties, but they're not considered in
        // Rehike in any case.
        foreach (get_object_vars($this) as $key => $value)
        {
            if (in_array($key, get_class_vars(static::class)))
            {
                continue;
            }

            $out->{$key} = $value;
        }
        
        return (object)$out;
    }
}