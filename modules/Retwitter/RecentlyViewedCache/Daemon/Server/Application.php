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
namespace Retwitter\RecentlyViewedCache\Daemon\Server;

use DomainException;
use Retwitter\RecentlyViewedCache\Daemon\Common\Opcode;

/**
 * Implements the cache server.
 *
 * The cache server functions as a state machine which receives instructions
 * over a local network socket as a means to facilitate interprocess
 * communication.
 */
class Application
{
    /**
     * The path to the root directory of the Retwitter application.
     */
    private string $rootDirectory;

    /**
     * The address and port of the server application.
     */
    private string $address;

    /**
     * Handle to the server socket.
     * 
     * @var resource
     */
    private $socket;

    private ConnectionManager $connectionManager;

    public function __construct()
    {
        $this->connectionManager = new ConnectionManager($this);
    }

    public function start(string $rootDirectory, string $address): never
    {
        $this->rootDirectory = $rootDirectory;
        $this->address = $address;

        $this->socket = stream_socket_server(
            $this->address, $errno, $errstr,
            STREAM_SERVER_BIND
        );
        
        if (!$this->socket)
        {
            die("Failed to open socket: $errstr ($errno)");
        }

        stream_set_blocking($this->socket, true);

        // Now that the server is up, let's tell the client if it wants to know.
        if (Arguments::$s_signalStartupCompletedInStdout)
        {
            echo "<retwitter-cache-server-up>";
        }

        self::runInterpreterLoop();
    }

    /**
     * Gets the address of the server.
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Gets the socket of the server.
     * 
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Runs the interpreter loop of the server.
     */
    public function runInterpreterLoop(): never
    {
        $readWatch = [$this->socket];
        $writeWatch = null;
        $exceptWatch = null;

        while (($numChanged = stream_select($readWatch, $writeWatch, $exceptWatch, null)) || true)
        {
            if (false === $numChanged)
            {
                // Error.
                echo "An error occurred when retrieving items from the stream.\n";
            }
            else if ($numChanged > 0)
            {
                // We take a peek at the opcode for the peer. The opcode is
                // currently always one byte long at the start of the packet.
                // Since this is only peeked at, it must be reread by the
                // interpreter after the fact.
                $data = stream_socket_recvfrom($this->socket, 1, STREAM_PEEK, $peer);
                
                if ($data)
                {
                    self::handleInstruction($data, peer: is_string($peer) ? $peer :  "");
                }
                else
                {
                    echo "Failed to get data from peer \"$peer\"." . PHP_EOL;

                    // In this case, even though if the peer is now invalid or
                    // whatever, we want to progress the buffer.
                    $data = stream_socket_recvfrom($this->socket, 1, 0, $peer);
                }
            }
            else if (0 === $numChanged)
            {
                // I guess this means we have downtime to check for things other
                // than processing instructions.
            }
        }
    }

    private function handleInstruction(string $instruction, string $peer): void
    {
        $opcode = ord($instruction[0]);

        match ($opcode)
        {
            Opcode::GetServerVersion->value =>
                self::handleGetServerVersion($peer),
            Opcode::ClientOpenConnection->value =>
                self::handleClientOpenConnection(),
            Opcode::ClientCloseConnection->value =>
                self::handleClientCloseConnection(),

            // Testing:
            Opcode::IdentifyWantString->value =>
                self::handleIdentifyWantString($peer),
            Opcode::RetrieveWantString->value =>
                self::handleRetrieveWantString($peer),
        };
    }

    private function handleGetServerVersion(string $peer): void
    {
        echo "Received GetServerVersion.\n";
        stream_socket_recvfrom($this->socket, 1, 0, $peer);
        stream_socket_sendto($this->socket, pack("V", 1), 0, $peer);
    }

    /**
     * Handles the ClientOpenConnection operation.
     */
    private function handleClientOpenConnection(): void
    {
        echo "Received ClientOpenConnection.\n";
        stream_socket_recvfrom($this->socket, 1, 0, $peer);

        try
        {
            $this->connectionManager->createNew($peer);
            echo "Created new connection for peer $peer.\n";
        }
        catch (DomainException $e)
        {
            echo "A client ($peer) tried to open a connection multiple times.\n";
        }
    }

    private function handleClientCloseConnection(): void
    {
        echo "Received ClientCloseConnection.\n";
        stream_socket_recvfrom($this->socket, 1, 0, $peer);

        if ($connection = $this->connectionManager->getConnection($peer))
        {
            $this->connectionManager->removeConnection($connection);
            echo "Removed connection for peer $peer.\n";
        }
        else
        {
            echo "A client ($peer) without a connection sent ClientCloseConnection.\n";
        }
    }

    private function handleIdentifyWantString(string $peer): void
    {
        echo "Received IdentifyWantString from peer $peer.\n";

        // Receive the character count:
        $cch = ord(stream_socket_recvfrom($this->socket, 2, STREAM_PEEK, $peer));

        echo " - The identified want string is $cch byte(s) long.\n";
        
        // Receive the string:
        $str = stream_socket_recvfrom($this->socket, $cch + 2, 0, $peer);
        $str = substr($str, 2);

        echo " - The identified want string is \"$str\".\n";

        $connection = $this->connectionManager->getConnection($peer);
        $connection->assignWantString($str);
    }

    private function handleRetrieveWantString(string $peer): void
    {
        echo "Received RetrieveWantString from peer $peer.\n";

        $connection = $this->connectionManager->getConnection($peer);

        stream_socket_recvfrom($this->socket, 1, 0, $peer);

        // if (isset($this->wantStrings[$peer]))
        // {
        //     stream_socket_sendto($this->socket, $this->wantStrings[$peer], 0, $peer);
        // }
        // else
        // {
        //     echo "No want string is available for peer.\n";
        //     stream_socket_sendto($this->socket, "", 0, $peer);
        // }

        $connection->send($connection->getWantString());
    }
}