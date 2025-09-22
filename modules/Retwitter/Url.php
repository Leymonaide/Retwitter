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

namespace Retwitter;

use Stringable;

class Url implements Stringable
{
    private ?string $protocol = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $host = null;
    private ?int $port = null;
    
    /**
     * An array containing each part of the path, separated by the "/" character
     * in a URL string.
     */
    private array $path = [];

    /**
     * An associative array of URL parameters.
     * 
     * @var ?string[] If the value is an empty string in the associative array,
     *     then the value will be encoded as "&key=". If the value is null, then
     *     it will be encoded as "&key".
     */
    private array $params = [];

    private ?string $fragment = null;

    public function __construct(
        string|Url $source
    )
    {
        if (is_string($source))
        {
            $this->parseFromString($source);
        }
        else // Is another Url object.
        {
            $this->copy($source);
        }
    }

    public function __toString(): string
    {
        $origin = $this->getOrigin();
        $path = $this->getPathAsString();
        $params = $this->getParametersAsString();
        $hash = $this->getFragment();

        $result = "$origin$path$params";

        if (null !== $hash)
        {
            $result .= "#$hash";
        }

        return $result;
    }

    private function clearValues(): void
    {
        // This could also be implemented like $this->copyValues(new Url(""))
        // but this probably runs faster at the expense of repeating this
        // pattern a little bit more.
        $this->protocol = null;
        $this->username = null;
        $this->password = null;
        $this->host = null;
        $this->port = null;
        $this->fragment = null;
        $this->path = [];
        $this->params = [];
    }

    /**
     * Copies the values of another Url object into this one.
     */
    public function copy(Url $source): void
    {
        $this->protocol = $source->protocol;
        $this->username = $source->username;
        $this->password = $source->password;
        $this->host = $source->host;
        $this->port = $source->port;
        $this->path = $source->path;
        $this->params = $source->params;
        $this->fragment = $source->fragment;
    }

    /**
     * Parses a string URL and sets the value of this object to the result.
     */
    public function parseFromString(string $source): static
    {
        $result = parse_url($source);

        if (isset($result["host"]))
        {
            $this->host = $result["host"];
        }

        if (isset($result["port"]))
        {
            $this->port = $result["port"];
        }

        if (isset($result["user"]))
        {
            $this->username = $result["user"];
        }

        if (isset($result["pass"]))
        {
            $this->password = $result["pass"];
        }

        if (isset($result["fragment"]))
        {
            $this->fragment = $result["fragment"];
        }

        if (isset($result["path"]))
        {
            $this->path = explode("/", $result["path"]);
        }

        if (isset($result["query"]))
        {
            $this->params = [];

            foreach ($result["query"] as $query)
            {
                // In the case of malformed input, the current design drops
                // everything from the second = until the next &. This might be
                // a problem in the future.
                $parts = explode("=", $query);

                $name = "";
                $value = null;

                if (isset($parts[1]))
                {
                    $name = urldecode($parts[0]);
                    $value = urldecode($parts[1]);
                }
                else
                {
                    $name = urldecode($parts[0]);
                    $value = null;
                }

                // Does not preserve order, but oh well.
                $this->params += [ $name => $value ];
            }
        }

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Gets the whole origin of the URL, including all applicable parts:
     *  - Protocol
     *  - Username
     *  - Password
     *  - Host name
     *  - Port
     */
    public function getOrigin(): string
    {
        $result = "";

        if ($protocol = $this->getProtocol())
        {
            $result .= "$protocol:";
        }

        if ($username = $this->getUsername())
        {
            $result .= $username;

            if ($password = $this->getPassword())
            {
                $result .= ":$password";
            }

            $result .= "@";
        }

        if ($host = $this->getHost())
        {
            $result .= $host;
        }

        if ($port = $this->getPort())
        {
            $result .= ":$port";
        }

        return $result;
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function getPathAsString(): string
    {
        return implode("/", $this->path);
    }

    public function getParameters(): array
    {
        return $this->params;
    }

    public function getParametersAsString(): string
    {
        if (empty($this->params))
        {
            return "";
        }

        $result = "?";

        foreach ($this->params as $key => $value)
        {
            $result .= "&" . urlencode($key);

            if (null !== $value)
            {
                $result .= "=" . urlencode($value);
            }
        }

        return $result;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }
}