@extends('layouts.skeleton')
@section('title', __('GIS Map'))
@section('page-title', __('GIS Interactive Map'))

@push('styles')
<style>
body { overflow: hidden; }
.main-content { padding: 0 !important; height: calc(100vh - var(--topbar-height)); display: flex; flex-direction: column; }

#mapContainer { flex: 1; position: relative; }
#map { width: 100%; height: 100%; }

/* Map toolbar */
.map-toolbar {
    position: absolute; top: 12px;
    {{ app()->getLocale()==='ar' ? 'right' : 'left' }}: 12px;
    z-index: 1000; display: flex; flex-direction: column; gap: 6px;
}
.map-toolbar-group {
    background: #fff; border-radius: 10px; padding: 6px;
    box-shadow: 0 2px 12px rgba(0,0,0,.15);
    display: flex; flex-direction: column; gap: 4px;
}
.map-btn {
    width: 36px; height: 36px; border: none; border-radius: 8px;
    background: #f8fafc; color: #555; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; transition: all .2s;
}
.map-btn:hover, .map-btn.active { background: var(--primary); color: #fff; }

/* Layer panel */
.layer-panel {
    position: absolute; top: 12px;
    {{ app()->getLocale()==='ar' ? 'left' : 'right' }}: 12px;
    z-index: 1000; background: #fff; border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,.15);
    width: 240px; overflow: hidden;
}
.layer-panel-header {
    background: var(--primary); color: #fff;
    padding: .6rem 1rem; font-size: .85rem; font-weight: 700;
    display: flex; align-items: center; justify-content: space-between;
}
.layer-item {
    padding: .5rem 1rem; display: flex; align-items: center;
    gap: .6rem; border-bottom: 1px solid #f0f4f8; font-size: .82rem;
    cursor: pointer; transition: background .15s;
}
.layer-item:hover { background: #f8fbff; }
.layer-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }

/* Analysis panel */
.analysis-panel {
    position: absolute; bottom: 20px;
    {{ app()->getLocale()==='ar' ? 'right' : 'left' }}: 12px;
    z-index: 1000; background: #fff; border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,.15);
    width: 280px; max-height: 320px; overflow-y: auto; display: none;
}
.analysis-panel.open { display: block; }
.analysis-header {
    background: #1a6b9a; color: #fff;
    padding: .6rem 1rem; font-size: .82rem; font-weight: 700;
    border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: .5rem;
}

/* Status bar */
.map-statusbar {
    background: #1a2e1e; color: rgba(255,255,255,.8);
    padding: .3rem 1rem; font-size: .72rem;
    display: flex; gap: 1.5rem; align-items: center;
    flex-shrink: 0;
}
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: #2d8a4e; display: inline-block; margin-right: 5px; animation: blink 1.5s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

/* Popup custom */
.map-popup { min-width: 180px; font-size: .82rem; }
.map-popup h6 { font-size: .85rem; font-weight: 700; margin-bottom: .4rem; color: #1a2e1e; }
.popup-row { display: flex; justify-content: space-between; padding: .15rem 0; border-bottom: 1px solid #f0f4f8; }
.popup-actions { margin-top: .5rem; display: flex; gap: .4rem; }

/* Progress ring */
.progress-ring { width: 36px; height: 36px; }
.progress-ring circle { fill: none; stroke-width: 4; stroke-linecap: round; transition: stroke-dashoffset .5s; }
</style>
@endpush

@section('content')
<div id="mapContainer">
    <div id="map"></div>

    {{-- ── MAP TOOLBAR ── --}}
    <div class="map-toolbar">
        <div class="map-toolbar-group">
            <button class="map-btn" id="btnZoomIn"    title="{{ __('Zoom In') }}"><i class="fas fa-plus"></i></button>
            <button class="map-btn" id="btnZoomOut"   title="{{ __('Zoom Out') }}"><i class="fas fa-minus"></i></button>
            <button class="map-btn" id="btnCenter"    title="{{ __('Center Map') }}"><i class="fas fa-crosshairs"></i></button>
        </div>
        <div class="map-toolbar-group">
            <button class="map-btn" id="btnHeatmap"   title="{{ __('Toggle Heatmap') }}"><i class="fas fa-fire"></i></button>
            <button class="map-btn" id="btnClusters"  title="{{ __('Toggle Clusters') }}" onclick="toggleClusters()"><i class="fas fa-object-group"></i></button>
            <button class="map-btn" id="btnFullscreen" title="{{ __('Fullscreen') }}" onclick="toggleFullscreen()"><i class="fas fa-expand"></i></button>
        </div>
        <div class="map-toolbar-group">
            <button class="map-btn" id="btnAnalysis"  title="{{ __('Spatial Analysis') }}" onclick="toggleAnalysis()"><i class="fas fa-draw-polygon"></i></button>
            <button class="map-btn" id="btnRouteMode" title="{{ __('Route Planning') }}"   onclick="toggleRouteMode()"><i class="fas fa-route"></i></button>
            <button class="map-btn" id="btnMeasure"   title="{{ __('Measure Distance') }}" onclick="toggleMeasure()"><i class="fas fa-ruler"></i></button>
        </div>
        <div class="map-toolbar-group">
            <button class="map-btn" title="{{ __('Export GeoJSON') }}" onclick="exportLayer()"><i class="fas fa-download"></i></button>
            <button class="map-btn" title="{{ __('Upload GeoJSON') }}" onclick="document.getElementById('geojsonUpload').click()"><i class="fas fa-upload"></i></button>
            <input type="file" id="geojsonUpload" accept=".json,.geojson" style="display:none" onchange="uploadGeojson(this)">
        </div>
    </div>

    {{-- ── LAYER PANEL ── --}}
    <div class="layer-panel">
        <div class="layer-panel-header">
            <span><i class="fas fa-layer-group me-1"></i>{{ __('Map Layers') }}</span>
            <small id="featuresCount">0 {{ __('features') }}</small>
        </div>
        <div class="layer-item" onclick="toggleLayer('containers')">
            <input type="checkbox" id="chkContainers" checked class="form-check-input" style="pointer-events:none;">
            <div class="layer-dot" style="background:#2d8a4e;"></div>
            <span>{{ __('Containers') }}</span>
            <small class="ms-auto text-muted" id="cntContainers">{{ $containers }}</small>
        </div>
        <div class="layer-item" onclick="toggleLayer('vehicles')">
            <input type="checkbox" id="chkVehicles" checked class="form-check-input" style="pointer-events:none;">
            <div class="layer-dot" style="background:#1a6b9a;"></div>
            <span>{{ __('Vehicles') }}</span>
            <small class="ms-auto text-muted" id="cntVehicles">{{ $vehicles }}</small>
        </div>
        <div class="layer-item" onclick="toggleLayer('dumpsites')">
            <input type="checkbox" id="chkDumpsites" checked class="form-check-input" style="pointer-events:none;">
            <div class="layer-dot" style="background:#e07b39;"></div>
            <span>{{ __('Dumpsites') }}</span>
            <small class="ms-auto text-muted" id="cntDumpsites">{{ $dumpsites }}</small>
        </div>
        <div class="layer-item" onclick="toggleLayer('complaints')">
            <input type="checkbox" id="chkComplaints" checked class="form-check-input" style="pointer-events:none;">
            <div class="layer-dot" style="background:#dc3545;"></div>
            <span>{{ __('Complaints') }}</span>
            <small class="ms-auto text-muted" id="cntComplaints">—</small>
        </div>
        <div class="layer-item" onclick="toggleLayer('routes')">
            <input type="checkbox" id="chkRoutes" class="form-check-input" style="pointer-events:none;">
            <div class="layer-dot" style="background:#7b3fa0;border-radius:2px;"></div>
            <span>{{ __('Active Routes') }}</span>
        </div>
        <div style="padding:.5rem 1rem;border-top:1px solid #f0f4f8;">
            <div style="font-size:.72rem;color:#888;margin-bottom:.4rem;">{{ __('Base Map') }}</div>
            <select id="tileSelect" class="form-select form-select-sm" onchange="changeTile(this.value)">
                <option value="osm">OpenStreetMap</option>
                <option value="satellite">Satellite</option>
                <option value="topo">Topographic</option>
                <option value="dark">Dark</option>
            </select>
        </div>
        <div style="padding:.5rem 1rem;">
            <div style="font-size:.72rem;color:#888;margin-bottom:.4rem;">{{ __('Filter by Fill Level') }}</div>
            <input type="range" class="form-range" id="fillFilter" min="0" max="100" value="0" oninput="filterByFill(this.value)">
            <div style="font-size:.72rem;color:var(--primary);text-align:center;">≥ <span id="fillFilterVal">0</span>%</div>
        </div>
    </div>

    {{-- ── ANALYSIS PANEL ── --}}
    <div class="analysis-panel" id="analysisPanel">
        <div class="analysis-header">
            <i class="fas fa-draw-polygon"></i>{{ __('Spatial Analysis') }}
            <button style="background:none;border:none;color:#fff;margin-left:auto;cursor:pointer;" onclick="toggleAnalysis()">×</button>
        </div>
        <div style="padding:.75rem;">
            <div class="mb-2">
                <label class="form-label" style="font-size:.78rem;">{{ __('Analysis Type') }}</label>
                <select id="analysisType" class="form-select form-select-sm">
                    <option value="radius_search">{{ __('Radius Search') }}</option>
                    <option value="nearest_dumpsite">{{ __('Nearest Dumpsite') }}</option>
                    <option value="isochrone">{{ __('Isochrone (Drive Time)') }}</option>
                    <option value="coverage_analysis">{{ __('Coverage Analysis') }}</option>
                </select>
            </div>
            <div id="radiusOptions">
                <label class="form-label" style="font-size:.78rem;">{{ __('Radius (km)') }}</label>
                <input type="number" id="searchRadius" class="form-control form-control-sm" value="0.5" step="0.1" min="0.1" max="10">
            </div>
            <div class="mt-2">
                <small class="text-muted">{{ __('Click on map to run analysis at that point') }}</small>
            </div>
            <div id="analysisResults" class="mt-2" style="font-size:.78rem;"></div>
        </div>
    </div>

    {{-- Coordinate display --}}
    <div style="position:absolute;bottom:36px;right:12px;z-index:1000;background:rgba(0,0,0,.6);color:#fff;padding:.25rem .6rem;border-radius:6px;font-size:.72rem;font-family:monospace;" id="coordDisplay">
       {{ number_format(31.9522, 4) }}, {{ number_format(35.2332, 4) }}
    </div>
</div>

{{-- Status Bar --}}
<div class="map-statusbar">
    <span><span class="status-dot"></span>{{ __('Live') }}</span>
    <span id="sb-containers"><i class="fas fa-trash-alt me-1"></i>{{ $containers }} {{ __('containers') }}</span>
    <span id="sb-vehicles"><i class="fas fa-truck me-1"></i>{{ $vehicles }} {{ __('vehicles') }}</span>
    <span id="sb-dumpsites"><i class="fas fa-industry me-1"></i>{{ $dumpsites }} {{ __('dumpsites') }}</span>
    <span class="ms-auto" id="sb-mode">{{ __('View Mode') }}</span>
    <span id="sb-zoom">{{ __('Zoom') }}: {{ \App\Models\Setting::get('default_zoom',13) }}</span>
</div>

@endsection

@push('scripts')
<script>
// ── MAP INIT ──────────────────────────────────────────────────────────────
const map = L.map('map', {
    center: [DEFAULT_LAT, DEFAULT_LNG],
    zoom: DEFAULT_ZOOM,
    zoomControl: false,
});

const tileLayers = {
    osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '© Esri' }),
    topo: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { attribution: '© OpenTopoMap' }),
    dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '© CartoDB' }),
};
tileLayers.osm.addTo(map);

// ── CUSTOM ICONS ──────────────────────────────────────────────────────────
function makeIcon(color, faIcon, size=10) {
    return L.divIcon({
        className: '',
        html: `<div style="width:${size+8}px;height:${size+8}px;background:${color};border-radius:50%;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:${size-2}px;"><i class="fas ${faIcon}"></i></div>`,
        iconSize: [size+8, size+8], iconAnchor: [(size+8)/2, (size+8)/2],
    });
}

function containerIcon(fillLevel) {
    const color = fillLevel >= 90 ? '#dc3545' : fillLevel >= 70 ? '#ffc107' : fillLevel >= 40 ? '#17a2b8' : '#2d8a4e';
    return L.divIcon({
        className: '',
        html: `<div style="width:28px;height:28px;background:${color};border-radius:50%;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;">${Math.round(fillLevel)}</div>`,
        iconSize: [28,28], iconAnchor: [14,14],
    });
}

// ── LAYERS ────────────────────────────────────────────────────────────────
const layers = {
    containers: L.markerClusterGroup({ maxClusterRadius: 50 }),
    vehicles:   L.layerGroup(),
    dumpsites:  L.layerGroup(),
    complaints: L.layerGroup(),
    routes:     L.layerGroup(),
    heatmap:    null,
    geojson:    L.layerGroup(),
};

const layerState = { containers:true, vehicles:true, dumpsites:true, complaints:true, routes:false };

// ── LOAD ALL LAYERS ───────────────────────────────────────────────────────
async function loadAllLayers() {
    try {
        const res = await fetch('{{ route("map.layers.all") }}');
        const data = await res.json();

        // Containers
        layers.containers.clearLayers();
        if (data.containers?.features) {
            data.containers.features.forEach(f => {
                const p = f.properties;
                const m = L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]], {
                    icon: containerIcon(p.fill_level)
                });
                m.bindPopup(`
                    <div class="map-popup">
                        <h6><i class="fas fa-trash-alt me-1"></i>${p.code}</h6>
                        <div class="popup-row"><span>{{ __('Name') }}</span><span>${p.name}</span></div>
                        <div class="popup-row"><span>{{ __('Type') }}</span><span>${p.type}</span></div>
                        <div class="popup-row"><span>{{ __('Fill') }}</span>
                            <span style="color:${p.fill_color==='danger'?'#dc3545':p.fill_color==='warning'?'#ffc107':'#2d8a4e'};font-weight:700;">${p.fill_level}%</span></div>
                        <div class="popup-row"><span>{{ __('Zone') }}</span><span>${p.zone||'—'}</span></div>
                        <div class="popup-row"><span>{{ __('Status') }}</span><span>${p.status}</span></div>
                        <div class="popup-actions">
                            <a href="/containers" class="btn btn-sm btn-primary" style="font-size:.72rem;padding:.2rem .5rem;">{{ __('Manage') }}</a>
                        </div>
                    </div>
                `, { maxWidth: 220 });
                layers.containers.addLayer(m);
            });
            document.getElementById('cntContainers').textContent = data.containers.features.length;
        }

        // Vehicles
        layers.vehicles.clearLayers();
        if (data.vehicles?.features) {
            data.vehicles.features.forEach(f => {
                const p = f.properties;
                const m = L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]], {
                    icon: makeIcon('#1a6b9a','fa-truck',12)
                });
                m.bindPopup(`
                    <div class="map-popup">
                        <h6><i class="fas fa-truck me-1"></i>${p.plate_number}</h6>
                        <div class="popup-row"><span>{{ __('Driver') }}</span><span>${p.driver||'—'}</span></div>
                        <div class="popup-row"><span>{{ __('Status') }}</span><span>${p.status}</span></div>
                        <div class="popup-row"><span>{{ __('Fuel') }}</span><span>${p.fuel_level||'—'}%</span></div>
                    </div>
                `, { maxWidth: 200 });
                layers.vehicles.addLayer(m);
            });
            document.getElementById('cntVehicles').textContent = data.vehicles.features.length;
        }

        // Dumpsites
        layers.dumpsites.clearLayers();
        if (data.dumpsites?.features) {
            data.dumpsites.features.forEach(f => {
                const p = f.properties;
                const m = L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]], {
                    icon: makeIcon('#e07b39','fa-industry',12)
                });
                m.bindPopup(`
                    <div class="map-popup">
                        <h6><i class="fas fa-industry me-1"></i>${p.name}</h6>
                        <div class="popup-row"><span>{{ __('Type') }}</span><span>${p.type}</span></div>
                        <div class="popup-row"><span>{{ __('Fill') }}</span><span>${p.fill_percentage}%</span></div>
                        <div class="popup-row"><span>{{ __('Status') }}</span><span>${p.status}</span></div>
                    </div>
                `, { maxWidth: 200 });
                layers.dumpsites.addLayer(m);
            });
            document.getElementById('cntDumpsites').textContent = data.dumpsites.features.length;
        }

        // Complaints
        layers.complaints.clearLayers();
        if (data.complaints?.features) {
            data.complaints.features.forEach(f => {
                const p = f.properties;
                const m = L.circleMarker([f.geometry.coordinates[1], f.geometry.coordinates[0]], {
                    radius: 7, color: '#dc3545', fillColor: '#dc3545', fillOpacity: .7, weight: 2,
                });
                m.bindPopup(`
                    <div class="map-popup">
                        <h6><i class="fas fa-exclamation-circle me-1 text-danger"></i>${p.ticket}</h6>
                        <div class="popup-row"><span>{{ __('Subject') }}</span><span>${p.subject}</span></div>
                        <div class="popup-row"><span>{{ __('Priority') }}</span><span>${p.priority}</span></div>
                    </div>
                `, { maxWidth: 200 });
                layers.complaints.addLayer(m);
            });
            document.getElementById('cntComplaints').textContent = data.complaints.features.length;
        }

        // Heatmap
        if (data.heatmap?.length) {
            layers.heatmap = L.heatLayer(data.heatmap, {
                radius: 25, blur: 20, maxZoom: 17,
                gradient: { 0.4:'#2d8a4e', 0.65:'#ffc107', 1:'#dc3545' }
            });
        }

        // Add visible layers
        Object.keys(layerState).forEach(k => { if (layerState[k] && layers[k]) map.addLayer(layers[k]); });

        // Total count
        let total = (data.containers?.features?.length||0) + (data.vehicles?.features?.length||0) + (data.dumpsites?.features?.length||0);
        document.getElementById('featuresCount').textContent = total + ' {{ __("features") }}';

    } catch(e) {
        console.error('Layer load error:', e);
    }
}

loadAllLayers();
setInterval(loadAllLayers, 30000); // Refresh every 30s

// ── TOGGLE LAYER ──────────────────────────────────────────────────────────
function toggleLayer(name) {
    layerState[name] = !layerState[name];
    const chk = document.getElementById('chk' + name.charAt(0).toUpperCase() + name.slice(1));
    if (chk) chk.checked = layerState[name];
    if (layers[name]) {
        if (layerState[name]) map.addLayer(layers[name]);
        else map.removeLayer(layers[name]);
    }
}

// ── HEATMAP ───────────────────────────────────────────────────────────────
let heatmapOn = false;
document.getElementById('btnHeatmap').addEventListener('click', () => {
    heatmapOn = !heatmapOn;
    document.getElementById('btnHeatmap').classList.toggle('active', heatmapOn);
    if (heatmapOn && layers.heatmap) {
        map.addLayer(layers.heatmap);
        // Hide container markers for cleaner view
        if (map.hasLayer(layers.containers)) map.removeLayer(layers.containers);
    } else {
        if (layers.heatmap) map.removeLayer(layers.heatmap);
        if (layerState.containers) map.addLayer(layers.containers);
    }
});

// ── FILL FILTER ───────────────────────────────────────────────────────────
function filterByFill(val) {
    document.getElementById('fillFilterVal').textContent = val;
    layers.containers.clearLayers();
    // Re-fetch with filter
    fetch(`{{ route("map.layers.containers") }}?min_fill=${val}`)
        .then(r => r.json())
        .then(data => {
            if (!data.features) return;
            data.features.forEach(f => {
                const p = f.properties;
                const m = L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]], {
                    icon: containerIcon(p.fill_level)
                }).bindPopup(`<div class="map-popup"><h6>${p.code}</h6><div>Fill: ${p.fill_level}%</div></div>`);
                layers.containers.addLayer(m);
            });
        });
}

// ── TILE SWITCHER ─────────────────────────────────────────────────────────
function changeTile(val) {
    Object.values(tileLayers).forEach(t => map.removeLayer(t));
    tileLayers[val].addTo(map);
}

// ── SPATIAL ANALYSIS ─────────────────────────────────────────────────────
let analysisOpen = false, analysisMarker = null, analysisResultLayer = L.layerGroup().addTo(map);
function toggleAnalysis() {
    analysisOpen = !analysisOpen;
    if (analysisOpen) { routeMode = false; measureMode = false; }
    document.getElementById('analysisPanel').classList.toggle('open', analysisOpen);
    document.getElementById('btnAnalysis').classList.toggle('active', analysisOpen);
    document.getElementById('btnRouteMode').classList.toggle('active', routeMode);
    document.getElementById('btnMeasure').classList.toggle('active', measureMode);
    document.getElementById('sb-mode').textContent = analysisOpen ? '{{ __("Analysis Mode") }}' : '{{ __("View Mode") }}';
}

map.on('click', async e => {
    if (routeMode) {
        routeWaypoints.push(e.latlng);
        L.circleMarker(e.latlng, {
            radius: 7, color: '#7b3fa0', fillColor: '#7b3fa0', fillOpacity: .8
        }).addTo(routeLayer);
        if (routeWaypoints.length > 1) {
            routeLayer.eachLayer(l => { if (l instanceof L.Polyline) routeLayer.removeLayer(l); });
            L.polyline(routeWaypoints, { color: '#7b3fa0', weight: 3, dashArray: '6,4' }).addTo(routeLayer);
        }
        return;
    }

    if (measureMode) {
        measurePoints.push(e.latlng);
        L.circleMarker(e.latlng, {
            radius: 5, color: '#1a2e1e', fillColor: '#1a2e1e', fillOpacity: .8
        }).addTo(measureLayer);
        if (measurePoints.length > 1) {
            measureLayer.eachLayer(l => { if (l instanceof L.Polyline) measureLayer.removeLayer(l); });
            L.polyline(measurePoints, { color: '#1a2e1e', weight: 2 }).addTo(measureLayer);
            let totalKm = 0;
            for (let i = 1; i < measurePoints.length; i++) {
                totalKm += measurePoints[i - 1].distanceTo(measurePoints[i]) / 1000;
            }
            const last = measurePoints[measurePoints.length - 1];
            L.popup({ closeButton: false })
                .setLatLng(last)
                .setContent(`${totalKm.toFixed(2)} km`)
                .openOn(map);
        }
        return;
    }

    if (!analysisOpen) return;
    const type   = document.getElementById('analysisType').value;
    const radius = document.getElementById('searchRadius').value;

    if (analysisMarker) analysisMarker.remove();
    analysisMarker = L.circleMarker(e.latlng, {
        radius: 10, color: '#1a6b9a', fillColor: '#1a6b9a', fillOpacity: .5
    }).addTo(map);

    try {
        const res = await fetch('{{ route("map.analysis") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ type, latitude: e.latlng.lat, longitude: e.latlng.lng, radius }),
        });
        const data = await res.json();
        analysisResultLayer.clearLayers();

        // Show results
        let html = '';
        if (type === 'radius_search') {
            const cnt = data.stats?.containers_found || 0;
            html = `<div style="background:#f0f4f8;border-radius:8px;padding:.5rem;">
                <strong>{{ __('Found') }}:</strong> ${cnt} {{ __('containers') }}, ${data.stats?.dumpsites_found||0} {{ __('dumpsites') }}
                <br><small class="text-muted">{{ __('Radius') }}: ${radius} km</small>
            </div>`;
            // Draw radius circle
            L.circle(e.latlng, { radius: radius*1000, color:'#1a6b9a', fillColor:'#1a6b9a', fillOpacity:.08 }).addTo(analysisResultLayer);
            // Mark found containers
            if (data.containers?.features) {
                data.containers.features.forEach(f => {
                    L.circleMarker([f.geometry.coordinates[1], f.geometry.coordinates[0]], {
                        radius:6, color:'#1a6b9a', fillColor:'#1a6b9a', fillOpacity:.8
                    }).addTo(analysisResultLayer);
                });
            }
        } else if (type === 'nearest_dumpsite') {
            const d = data.dumpsite?.properties;
            html = `<div style="background:#f0f4f8;border-radius:8px;padding:.5rem;">
                <strong>${d?.name||'—'}</strong><br>
                <small>{{ __('Distance') }}: ${data.distance_km} km</small>
            </div>`;
            if (data.dumpsite) {
                const coords = data.dumpsite.geometry.coordinates;
                L.polyline([e.latlng, [coords[1],coords[0]]], { color:'#e07b39', weight:2, dashArray:'6,4' }).addTo(analysisResultLayer);
                L.marker([coords[1],coords[0]], { icon: makeIcon('#e07b39','fa-industry',10) }).addTo(analysisResultLayer);
            }
        } else if (type === 'isochrone') {
            html = `<div style="background:#f0f4f8;border-radius:8px;padding:.5rem;">{{ __('Isochrone generated') }}</div>`;
            if (data.features) {
                L.geoJSON(data, { style: { color:'#7b3fa0', fillColor:'#7b3fa0', fillOpacity:.1, weight:1 } }).addTo(analysisResultLayer);
            }
        } else if (type === 'coverage_analysis') {
            html = `<div style="background:#f0f4f8;border-radius:8px;padding:.5rem;">
                <strong>{{ __('Coverage') }}: ${data.total_coverage_km2} km²</strong><br>
                <small>{{ __('Containers') }}: ${data.total_containers} | {{ __('Zones') }}: ${data.zones_covered}</small>
            </div>`;
        }
        document.getElementById('analysisResults').innerHTML = html;
    } catch(err) {
        document.getElementById('analysisResults').innerHTML = `<div class="text-danger">{{ __('Analysis failed') }}</div>`;
    }
});

// ── ROUTE MODE ────────────────────────────────────────────────────────────
let routeMode = false, routeWaypoints = [], routeLayer = L.layerGroup().addTo(map);
function toggleRouteMode() {
    routeMode = !routeMode;
    if (routeMode) {
        measureMode = false;
        analysisOpen = false;
        document.getElementById('analysisPanel').classList.remove('open');
        document.getElementById('btnAnalysis').classList.remove('active');
        document.getElementById('btnMeasure').classList.remove('active');
    }
    document.getElementById('btnRouteMode').classList.toggle('active', routeMode);
    document.getElementById('sb-mode').textContent = routeMode ? '{{ __("Route Mode — Click to add waypoints") }}' : '{{ __("View Mode") }}';
    if (!routeMode) { routeWaypoints = []; routeLayer.clearLayers(); }
}

// ── MEASURE ───────────────────────────────────────────────────────────────
let measureMode = false, measurePoints = [], measureLayer = L.layerGroup().addTo(map);
function toggleMeasure() {
    measureMode = !measureMode;
    if (measureMode) {
        routeMode = false;
        analysisOpen = false;
        document.getElementById('analysisPanel').classList.remove('open');
        document.getElementById('btnAnalysis').classList.remove('active');
        document.getElementById('btnRouteMode').classList.remove('active');
    }
    document.getElementById('btnMeasure').classList.toggle('active', measureMode);
    document.getElementById('sb-mode').textContent = measureMode ? '{{ __("Measure Mode — Click points") }}' : '{{ __("View Mode") }}';
    if (!measureMode) { measurePoints = []; measureLayer.clearLayers(); }
}

// ── MAP EVENTS ────────────────────────────────────────────────────────────
map.on('mousemove', e => {
    document.getElementById('coordDisplay').textContent =
        e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
});
map.on('zoomend', () => {
    document.getElementById('sb-zoom').textContent = '{{ __("Zoom") }}: ' + map.getZoom();
});

// ── TOOLBAR BUTTONS ───────────────────────────────────────────────────────
document.getElementById('btnZoomIn').addEventListener('click',  () => map.zoomIn());
document.getElementById('btnZoomOut').addEventListener('click', () => map.zoomOut());
document.getElementById('btnCenter').addEventListener('click',  () => map.setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM));

function toggleFullscreen() {
    const el = document.getElementById('mapContainer');
    if (!document.fullscreenElement) el.requestFullscreen();
    else document.exitFullscreen();
}

function toggleClusters() {
    const btn = document.getElementById('btnClusters');
    const clustersOn = !btn.classList.contains('active');
    btn.classList.toggle('active', clustersOn);

    // Swap the containers layer between a clustered group and a flat layer
    // group, carrying its current markers over so toggling doesn't lose them.
    const currentMarkers = layers.containers.getLayers();
    const wasOnMap = map.hasLayer(layers.containers);
    if (wasOnMap) map.removeLayer(layers.containers);

    layers.containers = clustersOn
        ? L.markerClusterGroup({ maxClusterRadius: 50 })
        : L.layerGroup();
    currentMarkers.forEach(m => layers.containers.addLayer(m));

    if (wasOnMap) map.addLayer(layers.containers);
}

// ── GEOJSON UPLOAD ────────────────────────────────────────────────────────
async function uploadGeojson(input) {
    const formData = new FormData();
    formData.append('file', input.files[0]);
    const res = await fetch('{{ route("map.geojson.upload") }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }, body: formData
    });
    const data = await res.json();
    if (data.geojson) {
        const gjLayer = L.geoJSON(data.geojson, {
            style: { color: '#7b3fa0', weight: 2, fillOpacity: .1 },
            onEachFeature: (f, l) => {
                if (f.properties) l.bindPopup(Object.entries(f.properties).map(([k,v]) => `${k}: ${v}`).join('<br>'));
            }
        });
        layers.geojson.clearLayers();
        layers.geojson.addLayer(gjLayer);
        map.addLayer(layers.geojson);
        map.fitBounds(gjLayer.getBounds());
        toastr.success(`{{ __('GeoJSON loaded:') }} ${data.feature_count} {{ __('features') }}`);
    }
    input.value = '';
}

// ── EXPORT GEOJSON ────────────────────────────────────────────────────────
function exportLayer() {
    const layer = prompt('{{ __("Export layer") }} (containers / vehicles / dumpsites):', 'containers');
    if (layer) window.open(`{{ route("map.geojson.export") }}?layer=${layer}`);
}
</script>
@endpush
