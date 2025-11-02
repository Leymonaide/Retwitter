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
namespace Retwitter\SignIn;

/**
 * Parses information from the __INITIAL_STATE__ object from a Twitter
 * response.
 */
class InitialStateParser
{
    public function __construct(private object $data)
    {
    }
    
    public function getActiveUserId(): ?string
    {
        if (isset($this->data->session->user_id))
        {
            return $this->data->session->user_id;
        }
        
        return null;
    }
    
    public function getActiveUserParser(): ?InitialStateProfileParser
    {
        $userId = $this->getActiveUserId();
        
        if (isset($this->data->entities->users->entities->{$userId}))
        {
            $root = $this->data->entities->users->entities->{$userId};
            return new InitialStateProfileParser($root);
        }
        
        return null;
    }
}