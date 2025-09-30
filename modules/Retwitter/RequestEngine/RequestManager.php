<?php
/* 
 * This file is part of the Retwitter project.
 * Copyright (c) 2025 lemon-pumpkin-pie.
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

namespace Retwitter\RequestEngine;

use Rehike\Async\Promise;
use Retwitter\GraphQlRequestParams;
use function Rehike\Async\async;

/**
 * A basic request manager.
 */
class RequestManager
{
    /**
     * Stores all requests.
     * 
     * @var IRequestManagerRequest[]
     */
    private array $requests = [];

    public function add(IRequestManagerRequest $request): void
    {
        $this->requests[] = $request;
    }

    public function runAll(): Promise
    {
        return async(function() {
            $promises = [];
            
            foreach ($this->requests as $request)
            {
                // Not yielded on purpose. A wrapper promise is made for each
                // request so that they can be iterated asynchronously.
                $promises[] = async(function() use ($request) {
                    do
                    {
                        $shouldRetry = yield $request->try();
                    }
                    while ($shouldRetry);
                });
            }

            yield Promise::all(...$promises);
        });
    } 
}