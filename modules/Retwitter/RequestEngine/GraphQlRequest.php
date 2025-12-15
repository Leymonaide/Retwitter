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
namespace Retwitter\RequestEngine;

use Rehike\Async\Promise;
use Rehike\Network\IResponse;
use function Rehike\Async\async;

use Retwitter\Network;
use Retwitter\GraphQlRequestParams;
use Retwitter\SignIn\SignIn;
use Retwitter\TwitterGuestToken;

class GraphQlRequest implements IRequestManagerRequest
{
    private GraphQlRequestParams $requestParams;
    private int $tryPaths = GraphQlRequestTryPaths::NoRetryAttempt->value;
    private ?IResponse $response = null;

    public function __construct(
        GraphQlRequestParams|string $paramsOrAction,
        array $variables = [],
        array $features = [],
    )
    {
        $paramsOrAction instanceof GraphQlRequestParams
            ? $this->requestParams = $paramsOrAction
            : $this->requestParams = new GraphQlRequestParams(
                $paramsOrAction, $variables, $features
            );
    }

    public function try(): Promise/*<bool>*/
    {
        return async(function() {
            $this->response =
                yield Network::graphqlRequestParam($this->requestParams);

            if (
                !($this->tryPaths & GraphQlRequestTryPaths::RegeneratedGuestToken->value)
                && !SignIn::isSignedIn()
            )
            {
                TwitterGuestToken::getNewGuestToken();

                $this->tryPaths |= GraphQlRequestTryPaths::RegeneratedGuestToken->value;
                return true;
            }

            // TODO: Brute force feature flags.
            
            return false;
        });
    }

    public function succeeded(): bool
    {
        return $this->response->status == 200;
    }

    public function getResponse(): ?IResponse
    {
        return $this->response;
    }
}