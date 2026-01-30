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
use Rehike\Async\EventLoop\EventLoop;
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

    public readonly ConnectionManager $connections;

    public function __construct()
    {
        $this->connections = new ConnectionManager($this);
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

        EventLoop::addEvent(new SocketEvent($this));

        // Now that the server is up, let's tell the client if it wants to know.
        if (Arguments::$s_signalStartupCompletedInStdout)
        {
            echo "<retwitter-cache-server-up>";
        }

        $this->runInterpreterLoop();
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
        while (true)
        {
            EventLoop::run();
            usleep(500_000);
        }
    }

    public function handleInstruction(string $packet, string $peer): void
    {
        $opcode = ord($packet[0]);

        match ($opcode)
        {
            Opcode::GetServerVersion->value =>
                $this->handleGetServerVersion($packet, $peer),
            Opcode::ClientOpenConnection->value =>
                $this->handleClientOpenConnection($packet, $peer),
            Opcode::ClientCloseConnection->value =>
                $this->handleClientCloseConnection($packet, $peer),

            // Testing:
            Opcode::IdentifyWantString->value =>
                $this->handleIdentifyWantString($packet, $peer),
            Opcode::RetrieveWantString->value =>
                $this->handleRetrieveWantString($packet, $peer),
        };
    }

    private function handleGetServerVersion(string $packet, string $peer): void
    {
        echo "Received GetServerVersion from peer $peer.\n";
        stream_socket_sendto($this->socket, pack("V", 1), 0, $peer);
    }

    /**
     * Handles the ClientOpenConnection operation.
     */
    private function handleClientOpenConnection(string $packet, string $peer): void
    {
        echo "Received ClientOpenConnection from peer $peer.\n";

        try
        {
            $this->connections->createNew($peer);
            echo "Created new connection for peer $peer.\n";
        }
        catch (DomainException $e)
        {
            echo "A client ($peer) tried to open a connection multiple times.\n";
        }
    }

    private function handleClientCloseConnection(string $packet, string $peer): void
    {
        echo "Received ClientCloseConnection from peer $peer.\n";

        if ($connection = $this->connections->getConnection($peer))
        {
            $this->connections->removeConnection($connection);
            echo "Removed connection for peer $peer.\n";
        }
        else
        {
            echo "A client ($peer) without a connection sent ClientCloseConnection.\n";
        }
    }

    private function handleIdentifyWantString(string $packet, string $peer): void
    {
        echo "Received IdentifyWantString from peer $peer.\n";

        // Receive the character count:
        $cch = ord($packet[1]);

        echo " - The identified want string is $cch byte(s) long.\n";
        
        // Receive the string:
        $str = substr($packet, 2);

        echo " - The identified want string is \"$str\".\n";

        $connection = $this->connections->getConnection($peer);
        $connection->assignWantString($str);
    }

    private function handleRetrieveWantString(string $packet, string $peer): void
    {
        echo "Received RetrieveWantString from peer $peer.\n";

        $connection = $this->connections->getConnection($peer);
        if ($connection)
        {
            $connection->petWatchdog();
            $connection->send($connection->getWantString());
        }
    }
}