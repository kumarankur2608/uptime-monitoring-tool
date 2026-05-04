<?php

namespace App\Models;

use App\Enums\WebsiteStatus;
use Database\Factories\MonitoredWebsiteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoredWebsite extends Model
{
    /** @use HasFactory<MonitoredWebsiteFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'url',
        'status',
        'last_checked_at',
        'last_failed_at',
        'last_response_code',
        'last_error_message',
        'down_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WebsiteStatus::class,
            'last_checked_at' => 'datetime',
            'last_failed_at' => 'datetime',
            'down_notified_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function monitoringUrl(): string
    {
        if (preg_match('/^https?:\/\//i', $this->url) === 1) {
            return $this->url;
        }

        return 'https://'.$this->url;
    }
}
