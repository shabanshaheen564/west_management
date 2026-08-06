<?php

namespace App\Helpers;

class GISHelper
{
    /**
     * Calculate distance between two coordinates using Haversine formula (km)
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Find containers within radius (km)
     */
    public static function containersWithinRadius(float $lat, float $lng, float $radius, $containers): array
    {
        return $containers->filter(function ($container) use ($lat, $lng, $radius) {
            $dist = self::distance($lat, $lng, $container->latitude, $container->longitude);
            return $dist <= $radius;
        })->values()->toArray();
    }

    /**
     * Convert containers collection to GeoJSON FeatureCollection
     */
    public static function toGeoJsonFeatureCollection($features): array
    {
        return [
            'type'     => 'FeatureCollection',
            'features' => $features->map(fn($item) => $item->toGeoJson())->values()->toArray(),
        ];
    }

    /**
     * Calculate bounding box for a set of coordinates
     */
    public static function boundingBox(array $coordinates): array
    {
        $lats = array_column($coordinates, 0);
        $lngs = array_column($coordinates, 1);
        return [
            'min_lat' => min($lats),
            'max_lat' => max($lats),
            'min_lng' => min($lngs),
            'max_lng' => max($lngs),
        ];
    }

    /**
     * Generate heatmap data from containers
     */
    public static function generateHeatmapData($containers): array
    {
        return $containers->map(function ($c) {
            return [
                $c->latitude,
                $c->longitude,
                $c->fill_level / 100, // intensity
            ];
        })->toArray();
    }

    /**
     * Parse GeoJSON and return coordinates
     */
    public static function parseGeoJson(string $geojson): array
    {
        $data = json_decode($geojson, true);
        if (!$data || !isset($data['type'])) return [];

        if ($data['type'] === 'Feature') {
            return $data['geometry']['coordinates'] ?? [];
        }
        if ($data['type'] === 'FeatureCollection') {
            return array_map(fn($f) => $f['geometry']['coordinates'], $data['features']);
        }
        return $data['coordinates'] ?? [];
    }

    /**
     * Optimize waypoints order using nearest neighbor algorithm
     */
    public static function nearestNeighborTSP(array $points, array $start): array
    {
        if (empty($points)) return [];
        $unvisited = $points;
        $route = [];
        $current = $start;

        while (!empty($unvisited)) {
            $nearest = null;
            $minDist = PHP_FLOAT_MAX;

            foreach ($unvisited as $key => $point) {
                $dist = self::distance($current[0], $current[1], $point['lat'], $point['lng']);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearest = $key;
                }
            }

            $route[] = $unvisited[$nearest];
            $current = [$unvisited[$nearest]['lat'], $unvisited[$nearest]['lng']];
            unset($unvisited[$nearest]);
        }

        return $route;
    }

    /**
     * Calculate centroid of a polygon
     */
    public static function polygonCentroid(array $coordinates): array
    {
        $lats = array_column($coordinates, 1);
        $lngs = array_column($coordinates, 0);
        return [
            'lat' => array_sum($lats) / count($lats),
            'lng' => array_sum($lngs) / count($lngs),
        ];
    }
}
