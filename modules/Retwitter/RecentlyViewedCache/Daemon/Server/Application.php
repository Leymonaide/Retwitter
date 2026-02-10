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
class Application implements ILogger
{
    use Logger;

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

    public readonly WatchdogTimer $idleWatchdog;

    public readonly ConnectionManager $connections;

    public function __construct()
    {
        $this->connections = new ConnectionManager($this);
        $this->initializeLogger();

        // We don't want the server to continue running literally forever. If it
        // hasn't been doing anything on a user's system for quite some time,
        // then it would probably good to shut it down. A future client can
        // restart the server when use resumes.
        // The watchdog is set to expire if an hour passes without the server
        // receiving a single message from any client.
        $this->idleWatchdog = WatchdogTimer::inSeconds(60 * 60);
    }

    public function start(string $rootDirectory, string $address): never
    {
        $this->rootDirectory = $rootDirectory;
        $this->address = $address;

        StartupLogger::log("Retwitter Recently Viewed Cache Service");
        StartupLogger::log("Version 1.0");

        $this->socket = stream_socket_server(
            $this->address, $errno, $errstr,
            STREAM_SERVER_BIND
        );
        
        if (!$this->socket)
        {
            die("Failed to open socket: $errstr ($errno)");
        }
        else
        {
            StartupLogger::log(
                "Successfully opened stream socket at address $this->address.");
        }

        stream_set_blocking($this->socket, true);

        EventLoop::addEvent(new SocketEvent($this));
        EventLoop::addEvent(new IdleWatchdogEvent($this));

        // Now that the server is up, let's tell the client if it wants to know.
        if (isset(Arguments::$s_startupLogFile)
            || Arguments::$s_signalStartupCompletedInStdout)
        {
            // The client only really needs to scan for the first message, but
            // in case it's expecting proper XML, the second message is also
            // sent to make it well formed.
            StartupLogger::log("<retwitter-cache-server-up>");
            StartupLogger::log("</retwitter-cache-server-up>");

            // At this point, it's totally fine for us to disconnect from the
            // logging file if we're using it, because the client who started us
            // up has received the notice that we're up and running. This
            // orphans the file, so it's expected that the client removes it
            // once it gets the message.
            StartupLogger::closeStartupLogFile();
        }

        $this->log("Listening for messages...");
        $this->runInterpreterLoop();
    }

    /**
     * Handles if the idle watchdog times out.
     * 
     * If this happens and there are no active connections, then the server will
     * be exited.
     */
    public function onIdleWatchdogBark(): void
    {
        if ($this->connections->getConnectionCount() > 0)
        {
            $this->idleWatchdog->pet();
            return;
        }

        $this->log("The idle watchdog timer expired, so the application will exit.");
        $this->log("Good night (zzz...)");

        exit(0);
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

    final protected function loggerGetBanner(): string
    {
        // Application global logs don't need a banner.
        return "";
    }

    final protected function loggerGetSize(): int { return 1000; }

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

    public function executeInstruction(string $packet, string $peer): void
    {
        $rawOpcode = ord($packet[0]);
        $opcode = Opcode::tryFrom($rawOpcode);
        
        // Retrieve the connection to which the instruction should be forwarded
        // (may be null)
        $connection = $this->connections->getConnection($peer);

        if (!$opcode)
        {
            ($connection ?? $this)->log("Invalid opcode 0x" . 
                dechex($rawOpcode));
            return;
        }

        $logCb = function() use ($opcode, $peer)
        {
            $this->log(
                "[Global] Received $opcode->name instruction from peer " .
                "$peer."
            );
        };

        $handled = match ($opcode)
        {
            // The following list of instructions are handled by the server
            // without connection state being required.
            Opcode::GetServerVersion =>
                $this->handleGetServerVersion($packet, $peer, $logCb),
            Opcode::ClientOpenConnection =>
                $this->handleClientOpenConnection($packet, $peer, $logCb),
            Opcode::ClientCloseConnection =>
                $this->handleClientCloseConnection($packet, $peer, $logCb),

            default => false
        };

        if (!$handled)
        {
            if (!$connection)
            {
                $this->log(
                    "No client available to handle instruction " .
                    $opcode->name . "."
                );
                return;
            }

            $handled = $connection->executeInstruction($opcode, $packet);
        }
    }

    private function handleGetServerVersion(string $packet, string $peer, callable $logCb): bool
    {
        $logCb();
        stream_socket_sendto($this->socket, pack("V", 1), 0, $peer);
        return true;
    }

    /**
     * Handles the ClientOpenConnection operation.
     */
    private function handleClientOpenConnection(string $packet, string $peer, callable $logCb): bool
    {
        $logCb();
        try
        {
            $this->connections->createNew($peer);
            $this->log("Created new connection for peer $peer.");
        }
        catch (DomainException $e)
        {
            $this->log("A client ($peer) tried to open a connection multiple times.");
        }

        return true;
    }

    private function handleClientCloseConnection(string $packet, string $peer, callable $logCb): bool
    {
        $logCb();
        if ($connection = $this->connections->getConnection($peer))
        {
            $this->connections->removeConnection($connection);
            $this->log("Removed connection for peer $peer.");
        }
        else
        {
            $this->log("A client ($peer) without a connection sent ClientCloseConnection.");
        }

        return true;
    }
}