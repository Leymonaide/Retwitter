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

declare(strict_types=1);
namespace Retwitter\RequestEngine;

use Rehike\Async\Promise;
use Rehike\Network\Internal\Request;
use Rehike\Network\Internal\Response;
use Rehike\Network\IResponse;
use function Rehike\Async\async;

trait TestRequestTrait
{
    private string $testFilePath;
    private string $content = "";

    protected function setupTest(
        string $testFilePath,
    ): void
    {
        $this->testFilePath = $testFilePath;
    }

    /**
     * Try sending out this request.
     * 
     * @return Promise<bool>
     *      True if the request should be retried, false otherwise.
     */
    public function tryTest(): Promise/*<bool>*/
    {
        return async(function () {
            if (!isset($this->testFilePath))
            {
                throw new \Exception("Test file path is not set.");
            }

            $content = file_get_contents($this->testFilePath);
            if ($content === false)
            {
                trigger_error(
                    "Test file at path \"" . $this->testFilePath . "\" does not exist.",
                    E_USER_WARNING);
            }
            else
            {
                $this->content = $content;
            }
        });
    }

    /**
     * Get the success status of the response.
     */
    public function succeededTest(): bool
    {
        return true;
    }

    /**
     * Get the response contents.
     */
    public function getResponseTest(): ?IResponse
    {
        return new Response(
            source: new Request($this->testFilePath, []),
            status: 200,
            content: $this->content,
            headers: [],
        );
    }
}