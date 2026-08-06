<?php

namespace App\Services\GIS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Helpers\GISHelper;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->apiKey = config('gis.ors_api_key');
        $this->baseUrl = config('gis.ors_base_url');
    }

    /**
     * Get optimized route between waypoints using ORS
     */
    public function optimizeRoute(array $coordinates, string $profile = 'driving-hgv'): array
    {
        try {
            $response = $this->client->post(
                "{$this->baseUrl}/v2/directions/{$profile}/geojson",
                [
                    'headers' => [
                        'Authorization' => $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'coordinates' => $coordinates,
                        'instructions' => true,
                        'units' => 'km',
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            return $this->parseOrsResponse($data);
        } catch (GuzzleException $e) {
            Log::error('ORS API error: ' . $e->getMessage());
            return $this->fallbackRoute($coordinates);
        }
    }

    /**
     * Optimize collection order for containers
     */
    public function optimizeCollectionOrder(array $containers, array $startPoint, array $endPoint): array
    {
        try {
            $jobs = array_map(function ($container, $index) {
                return [
                    'id'       => $container['id'],
                    'location' => [$container['longitude'], $container['latitude']],
                ];
            }, $containers, array_keys($containers));

            $response = $this->client->post(
                "{$this->baseUrl}/optimization",
                [
                    'headers' => [
                        'Authorization' => $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'jobs' => $jobs,
                        'vehicles' => [
                            [
                                'id'    => 1,
                                'start' => [$startPoint['lng'], $startPoint['lat']],
                                'end'   => [$endPoint['lng'], $endPoint['lat']],
                            ],
                        ],
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            return $this->parseOptimizationResponse($data, $containers);
        } catch (GuzzleException $e) {
            Log::error('ORS Optimization error: ' . $e->getMessage());
            return $this->fallbackOptimization($containers, $startPoint);
        }
    }

    /**
     * Get isochrone (reachability area) for a location
     */
    public function getIsochrone(float $lat, float $lng, array $ranges = [300, 600, 900]): array
    {
        try {
            $response = $this->client->post(
                "{$this->baseUrl}/v2/isochrones/driving-car/geojson",
                [
                    'headers' => [
                        'Authorization' => $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'locations' => [[$lng, $lat]],
                        'range'     => $ranges,
                        'range_type' => 'time',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('ORS Isochrone error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get matrix of distances/durations between points
     */
    public function getMatrix(array $locations): array
    {
        try {
            $response = $this->client->post(
                "{$this->baseUrl}/v2/matrix/driving-car",
                [
                    'headers' => [
                        'Authorization' => $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'locations' => $locations,
                        'metrics'   => ['distance', 'duration'],
                        'units'     => 'km',
                    ],
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('ORS Matrix error: ' . $e->getMessage());
            return [];
        }
    }

    private function parseOrsResponse(array $data): array
    {
        if (!isset($data['features'][0])) {
            return ['error' => 'Invalid ORS response'];
        }

        $feature  = $data['features'][0];
        $summary  = $feature['properties']['summary'] ?? [];
        $segments = $feature['properties']['segments'] ?? [];

        return [
            'geojson'   => $feature,
            'distance'  => round(($summary['distance'] ?? 0), 2),
            'duration'  => round(($summary['duration'] ?? 0) / 60), // minutes
            'segments'  => $segments,
            'bbox'      => $data['bbox'] ?? [],
        ];
    }

    private function parseOptimizationResponse(array $data, array $containers): array
    {
        if (!isset($data['routes'][0]['steps'])) {
            return ['error' => 'Invalid optimization response'];
        }

        $steps = $data['routes'][0]['steps'];
        $orderedContainers = [];

        foreach ($steps as $step) {
            if ($step['type'] === 'job') {
                $containerId = $step['job'];
                $container = collect($containers)->firstWhere('id', $containerId);
                if ($container) {
                    $orderedContainers[] = $container;
                }
            }
        }

        return [
            'ordered_containers' => $orderedContainers,
            'total_distance'     => round(($data['routes'][0]['distance'] ?? 0) / 1000, 2),
            'total_duration'     => round(($data['routes'][0]['duration'] ?? 0) / 60),
        ];
    }

    private function fallbackRoute(array $coordinates): array
    {
        // Straight-line fallback when API is unavailable
        $totalDist = 0;
        for ($i = 1; $i < count($coordinates); $i++) {
            $totalDist += GISHelper::distance(
                $coordinates[$i-1][1], $coordinates[$i-1][0],
                $coordinates[$i][1], $coordinates[$i][0]
            );
        }

        return [
            'geojson'  => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => $coordinates,
                ],
            ],
            'distance' => round($totalDist, 2),
            'duration' => round($totalDist * 3), // rough estimate: 3min/km
            'segments' => [],
            'fallback' => true,
        ];
    }

    private function fallbackOptimization(array $containers, array $startPoint): array
    {
        $points = array_map(fn($c) => [
            'id'  => $c['id'],
            'lat' => $c['latitude'],
            'lng' => $c['longitude'],
        ], $containers);

        $ordered = GISHelper::nearestNeighborTSP($points, [$startPoint['lat'], $startPoint['lng']]);

        return [
            'ordered_containers' => $ordered,
            'total_distance'     => 0,
            'total_duration'     => 0,
            'fallback'           => true,
        ];
    }
}
