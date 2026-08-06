<?php

return [
    'ors_api_key'  => env('ORS_API_KEY', ''),
    'ors_base_url' => env('ORS_BASE_URL', 'https://api.openrouteservice.org'),
    'default_lat'  => env('DEFAULT_LAT', 31.9038),
    'default_lng'  => env('DEFAULT_LNG', 35.2034),
    'default_zoom' => env('DEFAULT_ZOOM', 13),
    'tile_url'     => env('MAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
];
