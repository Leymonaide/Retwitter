<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025-2026 Leymonaide.
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
namespace Retwitter\Theme\Plus\Components\ProfilePage;

use Rehike\Util\ParsingUtils;
use Retwitter\Page\Common\Profile\IProfileDataParser;
use Retwitter\Page\Common\Timeline\ITimelineDataParser;

class MProfileFeed
{
    /**
     * @var MProfileFeedPost[]
     */
    public array $posts = [];
    
    public function __construct(IProfileDataParser $profileParser, ITimelineDataParser $parser)
    {
        foreach ($parser->parseAll() as $timelineItem)
        {
            if (@$timelineItem->tweetUnion->tweet)
            {
                $sourceTweet = $timelineItem->tweetUnion->tweet;
                $post = new MProfileFeedPost();
                
                if ($sourceTweet->isRetweet)
                {
                    $post->authorName = $profileParser->getDisplayName();
                    $post->authorAvatarUrl = $profileParser->getAvatarUrl();
                    $post->authorUrl = "/" . $profileParser->getUsername();
                    
                    $post->originalShare = new MProfileFeedPost();
                    
                    $post->originalShare->postUrl = "$post->authorUrl/status/$sourceTweet->id";
                    
                    $post->originalShare->authorName = ParsingUtils::getText($sourceTweet->author->name);
                    $post->originalShare->authorAvatarUrl = $sourceTweet->author->avatarUrl;
                    $post->originalShare->authorUrl = "/" . $sourceTweet->author->screenName;
                    
                    $post->originalShare->postUrl = $post->originalShare->authorUrl . "/status/$sourceTweet->id";
                    $post->originalShare->postContent = ParsingUtils::getText($sourceTweet->fullText);
                    $post->originalShare->postDate = $sourceTweet->createdAt->format("M d, Y");
                    
                    $post->originalShare->media = $sourceTweet->media;
                }
                else
                {
                    if ($sourceTweet->quotedTweet != null)
                    {
                        $post->originalShare = new MProfileFeedPost();
                        $quotedTweet = $sourceTweet->quotedTweet->tweet;
                        
                        $post->originalShare->authorName = ParsingUtils::getText($quotedTweet->author->name);
                        $post->originalShare->authorAvatarUrl = $quotedTweet->author->avatarUrl;
                        $post->originalShare->authorUrl = "/" . $quotedTweet->author->screenName;
                        
                        $post->originalShare->postUrl = $post->originalShare->authorUrl . "/status/$quotedTweet->id";
                        $post->originalShare->postContent = ParsingUtils::getText($quotedTweet->fullText);
                        $post->originalShare->postDate = $quotedTweet->createdAt->format("M d, Y");
                        
                        $post->originalShare->media = $quotedTweet->media;
                    }
                    
                    $post->authorName = ParsingUtils::getText($sourceTweet->author->name);
                    $post->authorAvatarUrl = $sourceTweet->author->avatarUrl;
                    $post->authorUrl = "/" . $sourceTweet->author->screenName;
                    
                    $post->postUrl = "$post->authorUrl/status/$sourceTweet->id";
                    $post->postContent = ParsingUtils::getText($sourceTweet->fullText);
                    
                    $post->media = $sourceTweet->media;
                }
                
                $post->postDate = $sourceTweet->createdAt->format("M d, Y");
                
                $this->posts[] = $post;
            }
        }
    }
}