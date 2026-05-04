<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientIndexController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $clients = Client::query()
            ->withCount('websites')
            ->orderBy('email')
            ->get();

        return ClientResource::collection($clients);
    }
}
