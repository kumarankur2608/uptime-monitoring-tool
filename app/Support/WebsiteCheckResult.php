<?php

namespace App\Support;

final readonly class WebsiteCheckResult
{
    private function __construct(
        public bool $reachable,
        public ?int $statusCode,
        public ?string $errorMessage,
    ) {
    }

    public static function up(int $statusCode): self
    {
        return new self(true, $statusCode, null);
    }

    public static function down(?int $statusCode, string $errorMessage): self
    {
        return new self(false, $statusCode, $errorMessage);
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }
}
