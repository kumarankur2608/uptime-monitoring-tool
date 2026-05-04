<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonitoredWebsiteResource;
use App\Models\Client;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientWebsiteIndexController extends Controller
{
    public function __invoke(Client $client): AnonymousResourceCollection
    {
        $client->load([
            'websites' => fn ($query) => $query->orderBy('url'),
        ]);

        return MonitoredWebsiteResource::collection($client->websites);
    }
}
