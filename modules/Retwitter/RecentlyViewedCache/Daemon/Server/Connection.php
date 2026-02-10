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

use Rehike\Async\EventLoop\EventLoop;
use Retwitter\RecentlyViewedCache\Daemon\Common\Opcode;
use RuntimeException;

/**
 * Stores server-side state for connected clients.
 */
class Connection implements ILogger
{
    use Logger;

    private string $wantString = "";
    private WatchdogTimer $watchdog;
    private ConnectionWatchdogEvent $watchdogEvent;

    private int $clientVersion = 0;

    // Virtual machine registers:
    private string $stringBuffer = "";

    public function __construct(
        public readonly Application $application,
        public readonly string $address,
    )
    {
        $this->initializeLogger();
        
        // We expect a response from all clients within 30 seconds from the last
        // instruction sent. If a client hangs, then it will be forcefully
        // disconnected.
        $this->watchdog = WatchdogTimer::inSeconds(30);
        $this->watchdogEvent = new ConnectionWatchdogEvent($this);
        EventLoop::addEvent($this->watchdogEvent);
    }

    public function __destruct()
    {
        EventLoop::removeEvent($this->watchdogEvent);
    }

    protected function loggerGetBanner(): string
    {
        return "[$this->address]";
    }

    protected function loggerGetSize(): int { return 500; }

    public function send(string $data): void
    {
        $status = stream_socket_sendto(
            $this->application->getSocket(),
            $data,
            0,
            $this->address
        );

        if (false === $status)
        {
            throw new RuntimeException("Failed to send data.");
        }
    }

    public function assignWantString(string $wantString): void
    {
        $this->wantString = $wantString;
    }

    public function getWantString(): string
    {
        return $this->wantString;
    }

    public function petWatchdog(): void
    {
        $this->watchdog->pet();
    }

    /**
     * @internal
     */
    public function internalGetWatchdog(): WatchdogTimer
    {
        return $this->watchdog;
    }

    //
    // -------------------- Virtual machine implementation --------------------
    //

    public function executeInstruction(Opcode $opcode, string $packet): bool
    {
        // Any instruction forwarded to the connection should pet the watchdog.
        $this->petWatchdog();

        $this->log("Received $opcode->name instruction.");

        $executed = match ($opcode)
        {
            // Ping is a no-op which just pets the watchdog.
            Opcode::Ping => true,
            Opcode::ClientReportVersion =>
                $this->handleClientReportVersion($packet),
            Opcode::GetFromCache => true, // TODO

            // Testing:
            Opcode::IdentifyWantString =>
                $this->handleIdentifyWantString($packet),
            Opcode::RetrieveWantString =>
                $this->handleRetrieveWantString($packet),

            default => false
        };

        if (!$executed)
        {
            $this->log("Failed to execute instruction $opcode->name.");
        }

        return $executed;
    }

    private function handleClientReportVersion(string $packet): bool
    {
        $version = (int)unpack("V", substr($packet, 1, 4))[1];
        $this->clientVersion = $version;
        return true;
    }

    private function handleIdentifyWantString(string $packet): bool
    {
        // Read the character count:
        $cch = ord($packet[1]);

        $this->log(" - The identified want string is $cch byte(s) long.");
        
        // Read the string:
        $str = substr($packet, 2);

        $this->log(" - The identified want string is \"$str\".");

        $this->assignWantString($str);
        
        return true;
    }

    private function handleRetrieveWantString(string $packet): bool
    {
        $this->send($this->getWantString());
        return true;
    }
}