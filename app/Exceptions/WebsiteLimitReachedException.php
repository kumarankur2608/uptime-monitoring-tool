<?php

namespace App\Exceptions;

use RuntimeException;

class WebsiteLimitReachedException extends RuntimeException
{
    public static function forClient(string $email, int $limit): self
    {
        return new self("Client {$email} can only monitor {$limit} websites.");
    }
}
