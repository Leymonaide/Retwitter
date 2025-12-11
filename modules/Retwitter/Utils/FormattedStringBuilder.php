<?php
// This file is licensed under the Mozilla Public License 2.0 by The Rehike Maintainers.
declare(strict_types=1);
namespace Retwitter\Utils;

use Rehike\FormattedString;

use Retwitter\Utils\FormattedStringBuilder\{
    PrintfTemplateBuilderParams,
    RunBuilder
};

use Rehike\Logging\DebugLogger;
use Retwitter\ComplexLink;

/**
 * Builder for formatted strings like InnerTube.
 * 
 * This class is modified in Retwitter for our own purposes.
 * 
 * @author Isabella Lulamoon <kawapure@gmail.com>
 * @author The Rehike Maintainers
 */
class FormattedStringBuilder
{    
    // Flags for run creation
    public const RUN_AS_LINK         = 0b0001;
    public const RUN_DISPLAY_BOLD    = 0b0010;
    public const RUN_DISPLAY_ITALIC  = 0b0100;
    public const RUN_AS_COMPLEX_LINK = 0b1000;
    
    /**
     * An array of all the runs in the formatted string.
     */
    public array $runs = [];

    /**
     * Creates a FormattedStringBuilder from an existing formatted string object.
     */
    public static function from(\stdClass|FormattedString $source): static
    {
        $out = new static();

        DebugLogger::print(__METHOD__.": Input type %s", get_class($source));
        DebugLogger::print(__METHOD__.": Input object %s", var_export($source, true));

        // To make this applicable to all objects, and not just stdClass and
        // FormattedString, reflection would need to be used here to check if
        // the property is public.
        if (isset($source->runs))
        {
            $out->runs = [];
            
            foreach ($source->runs as $run)
            {
                $out->runs[] = ObjectUtils::clone($run);
            }
        }

        return $out;
    }
    
    /**
     * Build the formatted string.
     */
    public function build(): FormattedString
    {
        $out = new FormattedString(FormattedString::FORMATTED_STRING_FORMATTED);
        $out->runs = $this->runs;
        
        return $out;
    }
    
    /**
     * Create a RunBuilder.
     */
    public function createRunBuilder(): RunBuilder
    {
        return new RunBuilder();
    }
    
    /**
     * Create a run and add it to our list.
     */
    public function createAndAddRun(
            string $runText, 
            int $runCreationFlags = 0, 
            string $linkText = "", 
            array $extraData = []
    ): static
    {
        $builder = $this->createRunBuilder();

        $builder->text = $runText;
        
        if ($runCreationFlags & self::RUN_AS_LINK)
        {
            $builder->url = $linkText;
        }
        else if ($runCreationFlags & self::RUN_AS_COMPLEX_LINK)
        {
            $builder->complexLink = new ComplexLink($runText, $linkText);
        }
        
        if ($runCreationFlags & self::RUN_DISPLAY_BOLD)
        {
            $builder->bold = true;
        }
        
        if ($runCreationFlags & self::RUN_DISPLAY_ITALIC)
        {
            $builder->italic = true;
        }

        if (!empty($extraData))
        {
            foreach ($extraData as $key => $value)
            {
                $builder->{$key} = $value;
            }
        }
        
        $this->addRunFromBuilder($builder);
        
        return $this;
    }
    
    /**
     * Add a run from a RunBuilder.
     */
    public function addRunFromBuilder(RunBuilder $builder): static
    {
        $run = $builder->build();
        $this->runs[] = $run;
        
        return $this;
    }
    
    /**
     * Parse from printf-style templates.
     * 
     * We use this for i18n typically.
     * 
     * @param string[]|array[] $templates
     */
    public function parseFromPrintfTemplates(
            PrintfTemplateBuilderParams $main,
            PrintfTemplateBuilderParams ...$others
    ): static
    {
        if (strpos($main->runText, "%") === false)
        {
            $this->createAndAddRun(
                $main->runText,
                $main->runCreationFlags,
                $main->linkText,
                $main->extraData
            );
            
            return $this;
        }
        
        // Parse all the other strings first:
        $childParser = new FormattedStringBuilder();
        
        foreach ($others as $other)
        {
            $childParser->createAndAddRun(
                $other->runText,
                $other->runCreationFlags,
                $other->linkText,
                $other->extraData
            );
        }
        
        $parsedOthers = $childParser->build()->runs;
        unset($childParser);
        
        // Explode while keeping %s delimiter for easy parsing.
        $PART_REGEX = "/(%(\d\$)?s)/";
        $parts = preg_split($PART_REGEX, $main->runText, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $curPart = 0;
        foreach ($parts as $i => $part)
        {
            if (empty($part))
            {
                continue;
            }
            else if ($part[0] == "%" && preg_match($PART_REGEX, $main->runText, $matches))
            {
                $otherIndex = $curPart++;
                
                if ($matches[2])
                {
                    $otherIndex = (int)$matches[2];
                    --$curPart;
                }
                
                $this->runs[] = $parsedOthers[$otherIndex];
            }
            else
            {
                $this->createAndAddRun(
                    $part,
                    $main->runCreationFlags,
                    $main->linkText,
                    $main->extraData
                );
            }
        }
        
        return $this;
    }
}