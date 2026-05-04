<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoredWebsiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'visit_url' => $this->monitoringUrl(),
            'status' => $this->status?->value,
            'last_checked_at' => $this->last_checked_at?->toIso8601String(),
            'last_response_code' => $this->last_response_code,
            'last_error_message' => $this->last_error_message,
        ];
    }
}
