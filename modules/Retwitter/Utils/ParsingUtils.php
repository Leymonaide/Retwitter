<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 Leymonaide.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);
namespace Retwitter\Utils;

use Rehike\FormattedString;
use Retwitter\Utils\FormattedStringBuilder;

class ParsingUtils
{
    /**
     * Verifiably gets a username (i.e. jack) from either a handle (@jack) or a
     * username (jack).
     * 
     * If the input is already a username, then this function will do nothing.
     * 
     * If the input is a handle, then this function will drop the leading "@"
     * character to turn it into a username.
     */
    public static function getUsernameAsTextOnly(string $handle): string
    {
        if ("@" == substr($handle, 0, 1))
        {
            $handle = substr($handle, 1);
        }

        return $handle;
    }

    /**
     * Verifiably gets a user handle (i.e. @jack) from either a username (jack)
     * or a user handle (@jack).
     * 
     * If the input is already a handle, then this function will do nothing.
     * 
     * If the input is a username, then this function will prepend the "@"
     * character to turn it into a handle.
     */
    public static function getUsernameAsHandle(string $username): string
    {
        if ("@" != substr($username, 0, 1))
        {
            $username = "@$username";
        }

        return $username;
    }

    /**
     * Converts a formatted string to a plaintext string.
     * 
     * This is a minimal version of the Rehike ParsingUtils::getText() without
     * all the cruft to handle InnerTube's various cases. It only has what
     * Retwitter internally uses.
     */
    public static function getText(\stdClass|FormattedString $formattedString): string
    {
        $response = "";

        // Don't pass any old stdClass. It must be compatible with FormattedString.
        assert(isset($formattedString->runs));

        foreach ($formattedString->runs as $run)
        {
            $response .= $run->text;
        }

        return $response;
    }

    /**
     * Performs entity substitution on a Twitter string.
     * 
     * Substituted entities primarily include URLs. The Twitter API returns a
     * string with t.co short links in place alongside entities with proper
     * display URLs. The short links are substituted with the display URLs
     * during runtime.
     * 
     * Only URL entities use substitution.
     * 
     * @param string $string
     *        The source string on which to format entities.
     * @param ?object $entities
     *        The list of entities from the Twitter API.
     * @param int $clippedLeft
     *        The number of characters removed from the left of the source
     *        string during preprocessing.
     */
    public static function formatTwitterEntities(
        string $string,
        ?object $entities = null,
        int $clippedLeft = 0,
    ): FormattedString
    {
        $fsb = new FormattedStringBuilder();

        // If there are no URL entities, then there's nothing to do here.
        if (!isset($entities->urls) || !is_array($entities->urls))
        {
            $fsb->createAndAddRun($string);
            return $fsb->build();
        }

        $cursor = 0;

        foreach ($entities->urls as $urlEntity)
        {
            if (!isset($urlEntity->indices))
            {
                continue;
            }

            [$start, $end] = $urlEntity->indices;
            $start -= $clippedLeft;
            $end -= $clippedLeft;

            // Add text before the entity
            if ($cursor < $start)
            {
                $fsb->createAndAddRun(
                    substr($string, $cursor, $start - $cursor),
                );
            }

            // Add the substituted display URL as a link run
            $fsb->createAndAddRun(
                $urlEntity->display_url ?? $urlEntity->url,
                FormattedStringBuilder::RUN_AS_LINK,
                $urlEntity->expanded_url ?? $urlEntity->url,
            );

            $cursor = $end;
        }

        // Add trailing text after last entity
        if ($cursor < strlen($string))
        {
            $fsb->createAndAddRun(
                substr($string, $cursor),
            );
        }

        return $fsb->build();
    }

    /**
     * Formats Twitter's inline links, such as mentions and hashtags, on a
     * formatted string.
     *
     * Not every such case has corresponding entities. For example, profile
     * descriptions lack entities for mentioned profiles.
     */
    public static function formatTwitterLinksInFormattedString(
        \stdClass|FormattedString $formattedString,
    ): FormattedString
    {
        $fsbOut = FormattedStringBuilder::from($formattedString);

        for ($i = count($fsbOut->runs) - 1; $i >= 0; $i--)
        {
            $srcRun = $fsbOut->runs[$i];

            // Skip link runs:
            if (isset($srcRun->url) && null != $srcRun->url)
            {
                continue;
            }

            $pattern = '/(?P<mention>@[A-Za-z0-9_]{1,15})' .
                       '|(?P<hashtag>#[\p{L}0-9_]+)' .
                       '|(?P<cashtag>\$[A-Za-z]{1,6})/u';
            
            if (!preg_match_all(
                pattern: $pattern,
                subject: $srcRun->text,
                matches: $matches,
                flags: PREG_OFFSET_CAPTURE)
            )
            {
                // No links in this run.
                continue;
            }

            // Run attributes should propagate from the source run.
            $flags = 0;
            if ($srcRun->bold)
                $flags |= FormattedStringBuilder::RUN_DISPLAY_BOLD;
            if ($srcRun->italic)
                $flags |= FormattedStringBuilder::RUN_DISPLAY_ITALIC;

            $cursor = 0;
            $fsbNew = new FormattedStringBuilder();

            foreach ($matches[0] as $matchIndex => [$matchText, $offset])
            {
                // Put out the text before the match:
                if ($offset > $cursor)
                {
                    $fsbNew->createAndAddRun(
                        substr($srcRun->text, $cursor, $offset - $cursor),
                        $flags,
                    );
                }

                // Now put the text for the current match:
                $url = "";
                if (!empty($matches["mention"][$matchIndex][0]))
                {
                    // Matched a mention "@username", which is a link to the
                    // profile of that user.
                    $handle = self::getUsernameAsTextOnly($matchText);
                    $url = "/$handle";
                }
                else if (!empty($matches["hashtag"][$matchIndex][0]))
                {
                    // Matched a hashtag "#hashtag", which links to the search
                    // page for that hashtag:
                    $hashtag = substr($matchText, 1);
                    $url = "/hashtag/$hashtag";
                }
                else if (!empty($matches["cashtag"][$matchIndex][0]))
                {
                    $cashtag = substr($matchText, 1);

                    // URL encoded search for the cashtag:
                    $url = "/search?q=%24$cashtag";
                }

                $fsbNew->createAndAddRun(
                    runText: $matchText,
                    runCreationFlags: $flags | FormattedStringBuilder::RUN_AS_LINK,
                    linkText: $url,
                );

                $cursor = $offset + strlen($matchText);
            }

            // Trailing text after last match:
            if ($cursor < strlen($srcRun->text))
            {
                $fsbNew->createAndAddRun(
                    runText: substr($srcRun->text, $cursor),
                    runCreationFlags: $flags,
                );
            }
        
            array_splice($fsbOut->runs, $i, 1, $fsbNew->runs);
        }

        return $fsbOut->build();
    }

    /**
     * Formats a string containing emojis into a formatted string containing
     * links to those emojis' Twemoji variants.
     */
    public static function formatEmojis(string $sourceStr): FormattedString
    {
        $builder = new FormattedStringBuilder();

        $emojis = \Emoji\detect_emoji($sourceStr);
        if (0 == count($emojis))
        {
            $builder->createAndAddRun($sourceStr);
            return $builder->build();
        }

        $start = 0;
        foreach ($emojis as $emoji)
        {
            $beforeText = substr(
                $sourceStr,
                $start,
                $emoji["byte_offset"] - $start
            );

            if (!empty($beforeText))
            {
                $builder->createAndAddRun($beforeText);
            }

            // Twemoji URLs omit the variation selector character (U+FE0F)
            // UNLESS the emoji has a zero-width joiner (U+200D).
            // https://github.com/twitter/twemoji/blob/36bac6943fb39df00c9ba221263ea73b9445fa23/scripts/build.js#L337-L350
            $code = strtolower($emoji["hex_str"]);
            if (!preg_match("/-200d-/", $code))
            {
                $code = preg_replace(
                    "/(^|-)fe0f($|-)/", "", 
                    $code
                );
            }
            
            $runBuilder = $builder->createRunBuilder();
            $runBuilder->emoji = (object)[
                "url" => "https://twemoji.maxcdn.com/v/latest/72x72/$code.png",
                "label" => $emoji["short_name"],
                "alt" => $emoji["emoji"],
            ];
            $builder->addRunFromBuilder($runBuilder);

            $start = $emoji["byte_offset"] + strlen($emoji["emoji"]);
        }

        $lastText = substr($sourceStr, $start, null);
        if (!empty($lastText))
        {
            $builder->createAndAddRun($lastText);
        }

        return $builder->build();
    }

    /**
     * Formats a string containing emojis into a formatted string containing
     * links to those emojis' Twemoji variants.
     * 
     * This is a variant of formatEmojis that works on existing formatted string
     * objects and merges the new emoji formatted strings into the output.
     */
    public static function formatEmojisInFormattedString(
        \stdClass|FormattedString $formattedString
    ): FormattedString
    {
        $fsbOut = FormattedStringBuilder::from($formattedString);

        for ($i = count($fsbOut->runs) - 1; $i >= 0; $i--)
        {
            $outerRun = $fsbOut->runs[$i];
            $fsbIn = FormattedStringBuilder::from(
                self::formatEmojis($outerRun?->text ?? "")
            );
        
            foreach ($fsbIn->runs as $innerRun)
            {
                // Merge all the original properties of the outer run into the
                // split inner runs.
                foreach ($outerRun as $key => $value)
                {
                    if ($key !== "text")
                    {
                        $innerRun->{$key} = $value;
                    }
                }
            }
        
            array_splice($fsbOut->runs, $i, 1, $fsbIn->runs);
        }

        return $fsbOut->build();
    }
}