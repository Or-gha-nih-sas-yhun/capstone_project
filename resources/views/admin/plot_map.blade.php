@extends('layouts.app')

@section('title', 'Plot Mapping & Spot Map')

@section('styles')
<!-- Leaflet & MarkerCluster CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<style>
  /* ── Layout & Container ─────────────────────────────────── */
  .plot-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    height: calc(100vh - 84px);
    max-height: calc(100vh - 84px);
    overflow: hidden;
  }

  /* ── KPI Header Bar ─────────────────────────────────────── */
  .plot-kpi-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding: 2px 2px 6px 2px;
    scrollbar-width: thin;
  }
  .plot-kpi-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 12px;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    font-size: 12.5px;
    font-weight: 500;
    color: #475569;
  }
  .plot-kpi-pill strong {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
  }
  .plot-kpi-pill i {
    font-size: 13px;
  }
  .plot-kpi-pill.accent-pwd i { color: #7c3aed; }
  .plot-kpi-pill.accent-senior i { color: #d97706; }
  .plot-kpi-pill.accent-solo i { color: #059669; }
  .plot-kpi-pill.accent-voter i { color: #2563eb; }

  /* ── Controls Toolbar ───────────────────────────────────── */
  .plot-toolbar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 14px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
  }
  .plot-toolbar-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .plot-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  /* Sector Filter Buttons */
  .filter-btn-group {
    display: inline-flex;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 3px;
    gap: 2px;
  }
  .filter-btn {
    border: none;
    background: transparent;
    padding: 5px 11px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  .filter-btn:hover {
    color: #0f172a;
    background: rgba(255,255,255,0.6);
  }
  .filter-btn.active {
    background: #ffffff;
    color: #1e293b;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  }
  .filter-btn.active[data-filter="pwd"] { color: #7c3aed; font-weight: 700; }
  .filter-btn.active[data-filter="senior"] { color: #d97706; font-weight: 700; }
  .filter-btn.active[data-filter="solo_parent"] { color: #059669; font-weight: 700; }
  .filter-btn.active[data-filter="voter"] { color: #2563eb; font-weight: 700; }

  .plot-select, .plot-search-input {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 12.5px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s;
  }
  .plot-select:focus, .plot-search-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
  }

  /* ── Map Canvas Wrapper ─────────────────────────────────── */
  .plot-map-wrapper {
    position: relative;
    flex: 1;
    min-height: 400px;
    background: #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  #map {
    width: 100%;
    height: 100%;
    z-index: 1;
  }

  /* ── Custom Marker Icons ────────────────────────────────── */
  .custom-pin-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-43deg);
    box-shadow: 0 3px 8px rgba(0,0,0,0.28);
    border: 2px solid #ffffff;
    cursor: pointer;
    transition: transform 0.15s ease, filter 0.15s;
  }
  .custom-pin-marker:hover {
    transform: rotate(-43deg) scale(1.15);
    filter: brightness(1.1);
    z-index: 1000 !important;
  }
  .custom-pin-marker i {
    transform: rotate(43deg);
    color: #ffffff;
    font-size: 13px;
  }
  .custom-pin-marker.estimated {
    border-style: dashed;
    border-color: rgba(255,255,255,0.85);
    opacity: 0.88;
  }

  .landmark-pin-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    border: 2px solid #ffffff;
    color: #ffffff;
    cursor: pointer;
    transition: transform 0.15s;
  }
  .landmark-pin-marker:hover {
    transform: scale(1.18);
    z-index: 1000 !important;
  }

  /* ── Map Popups ─────────────────────────────────────────── */
  .leaflet-popup-content-wrapper {
    border-radius: 12px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2), 0 8px 10px -6px rgba(0,0,0,0.1);
  }
  .leaflet-popup-content {
    margin: 0;
    line-height: 1.4;
    font-family: inherit;
  }
  .map-card-popup {
    padding: 14px 16px;
    min-width: 250px;
    max-width: 290px;
  }
  .map-card-popup .popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 8px;
  }
  .map-card-popup .resident-name {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
  }
  .map-card-popup .tag-badge {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .map-card-popup .info-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #475569;
    margin-bottom: 4px;
  }
  .map-card-popup .info-row i {
    width: 14px;
    color: #94a3b8;
    text-align: center;
  }
  .map-card-popup .actions-row {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    gap: 6px;
  }
  .map-card-popup .btn-popup-action {
    flex: 1;
    padding: 6px 8px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: background 0.15s;
  }
  .btn-popup-action.primary {
    background: #2563eb;
    color: #ffffff;
  }
  .btn-popup-action.primary:hover {
    background: #1d4ed8;
  }
  .btn-popup-action.secondary {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
  }
  .btn-popup-action.secondary:hover {
    background: #e2e8f0;
  }

  /* ── Interactive Pinning Alert Banner ───────────────────── */
  .pinning-mode-banner {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 999px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    z-index: 1000;
    font-size: 12.5px;
    font-weight: 600;
    display: none;
    align-items: center;
    gap: 10px;
    animation: fadeInDown 0.2s ease;
  }
  .pinning-mode-banner .cancel-pin-btn {
    background: #ef4444;
    color: #ffffff;
    border: none;
    padding: 2px 8px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 700;
  }

  /* ── Unmapped Residents Slide-out Drawer ─────────────────── */
  .unmapped-drawer {
    position: absolute;
    top: 0;
    right: -360px;
    width: 340px;
    height: 100%;
    background: #ffffff;
    z-index: 1001;
    border-left: 1px solid #cbd5e1;
    box-shadow: -4px 0 20px rgba(0,0,0,0.12);
    display: flex;
    flex-direction: column;
    transition: right 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .unmapped-drawer.open {
    right: 0;
  }
  .unmapped-drawer-header {
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .unmapped-drawer-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .unmapped-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .unmapped-item-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: border-color 0.15s, background 0.15s;
  }
  .unmapped-item-card:hover {
    border-color: #94a3b8;
    background: #ffffff;
  }
  .unmapped-item-info {
    font-size: 12px;
  }
  .unmapped-item-info strong {
    font-size: 13px;
    color: #0f172a;
    display: block;
  }
  .unmapped-item-info span {
    color: #64748b;
  }
  .btn-pin-this {
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .btn-pin-this:hover {
    background: #1d4ed8;
  }

  /* ── Legend Overlay ─────────────────────────────────────── */
  .map-legend {
    position: absolute;
    bottom: 18px;
    left: 14px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(4px);
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 8px 12px;
    z-index: 999;
    font-size: 11.5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  .map-legend-item {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #334155;
    font-weight: 500;
  }
  .legend-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    border: 1.5px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  }

  @keyframes fadeInDown {
    from { opacity: 0; transform: translate(-50%, -10px); }
    to { opacity: 1; transform: translate(-50%, 0); }
  }

  @media (max-width: 900px) {
    .plot-container {
      height: auto;
      max-height: none;
    }
    .plot-map-wrapper {
      min-height: 520px;
    }
  }
</style>
@endsection

@section('content')
<div class="page-content" style="padding-top: 12px;">
  <div class="plot-container">

    <!-- ── 1. KPI Stats Summary Bar ── -->
    <div class="plot-kpi-bar">
      <div class="plot-kpi-pill">
        <i class="fas fa-users" style="color:#2563eb;"></i>
        <span>Total Residents: <strong>{{ number_format($stats['total']) }}</strong></span>
      </div>
      <div class="plot-kpi-pill">
        <i class="fas fa-location-dot" style="color:#16a34a;"></i>
        <span>GPS Verified Pins: <strong id="kpi-mapped-count">{{ number_format($stats['mapped']) }}</strong></span>
      </div>
      <div class="plot-kpi-pill">
        <i class="fas fa-circle-nodes" style="color:#64748b;"></i>
        <span>Purok Area Estimates: <strong id="kpi-unmapped-count">{{ number_format($stats['unmapped']) }}</strong></span>
      </div>
      <div class="plot-kpi-pill accent-pwd">
        <i class="fas fa-wheelchair"></i>
        <span>PWDs: <strong>{{ number_format($stats['pwd']) }}</strong></span>
      </div>
      <div class="plot-kpi-pill accent-senior">
        <i class="fas fa-person-cane"></i>
        <span>Seniors (60+): <strong>{{ number_format($stats['senior']) }}</strong></span>
      </div>
      <div class="plot-kpi-pill accent-solo">
        <i class="fas fa-children"></i>
        <span>Solo Parents: <strong>{{ number_format($stats['solo_parent']) }}</strong></span>
      </div>
      <div class="plot-kpi-pill accent-voter">
        <i class="fas fa-check-to-slot"></i>
        <span>Voters: <strong>{{ number_format($stats['voter']) }}</strong></span>
      </div>
    </div>

    <!-- ── 2. Interactive Map Toolbar ── -->
    <div class="plot-toolbar">
      <div class="plot-toolbar-left">
        <!-- Sector Filter Pills -->
        <div class="filter-btn-group" id="sectorFilterGroup">
          <button class="filter-btn active" data-filter="all">
            <i class="fas fa-border-all"></i> All
          </button>
          <button class="filter-btn" data-filter="pwd">
            <i class="fas fa-wheelchair" style="color:#7c3aed;"></i> PWD
          </button>
          <button class="filter-btn" data-filter="senior">
            <i class="fas fa-person-cane" style="color:#d97706;"></i> Seniors (60+)
          </button>
          <button class="filter-btn" data-filter="solo_parent">
            <i class="fas fa-children" style="color:#059669;"></i> Solo Parents
          </button>
          <button class="filter-btn" data-filter="voter">
            <i class="fas fa-check-to-slot" style="color:#2563eb;"></i> Voters
          </button>
        </div>

        <!-- Purok Select -->
        <select class="plot-select" id="purokSelect">
          <option value="all">📍 All Puroks</option>
          @foreach($puroks as $purokName)
            <option value="{{ $purokName }}">{{ $purokName }}</option>
          @endforeach
        </select>

        <!-- Search Input -->
        <input type="text" class="plot-search-input" id="searchInput" placeholder="🔍 Search resident name / address..." style="width: 220px;">
      </div>

      <div class="plot-toolbar-right">
        <!-- Basemap Toggle -->
        <select class="plot-select" id="basemapSelect" title="Switch Map Background">
          <option value="streets">🗺️ Street Map (OSM)</option>
          <option value="satellite">🛰️ Satellite View (Esri)</option>
          <option value="hybrid">🧭 Topo Map</option>
        </select>

        <!-- Estimated / Area toggle -->
        <label style="display:flex; align-items:center; gap:5px; font-size:12px; color:#475569; cursor:pointer;" title="Include residents placed at their Purok center zone until exact GPS pin is marked">
          <input type="checkbox" id="showEstimatedCheck" checked>
          <span>Show Purok estimates</span>
        </label>

        <!-- Pin Unmapped Button -->
        <button class="btn btn-primary" id="btnOpenUnmappedDrawer" style="padding:6px 12px; font-size:12.5px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
          <i class="fas fa-map-pin"></i> Pin Unmapped Residents
        </button>

        <!-- Re-center button -->
        <button class="btn btn-secondary" id="btnRecenterMap" style="padding:6px 10px; font-size:12.5px; border-radius:8px;" title="Reset Map View">
          <i class="fas fa-crosshairs"></i>
        </button>
      </div>
    </div>

    <!-- ── 3. Map Viewport & Overlays ── -->
    <div class="plot-map-wrapper">
      <!-- Interactive Banner during Pinning Mode -->
      <div class="pinning-mode-banner" id="pinningBanner">
        <span><i class="fas fa-crosshairs"></i> Click on the map to pin: <strong id="pinningResidentName">Resident</strong></span>
        <button class="cancel-pin-btn" id="btnCancelPinning">Cancel</button>
      </div>

      <!-- Leaflet Map Canvas -->
      <div id="map"></div>

      <!-- Map Legend Overlay -->
      <div class="map-legend">
        <div style="font-weight:700; font-size:11px; text-transform:uppercase; color:#64748b; margin-bottom:2px;">Legend</div>
        <div class="map-legend-item"><span class="legend-dot" style="background:#2563eb;"></span> General Resident</div>
        <div class="map-legend-item"><span class="legend-dot" style="background:#7c3aed;"></span> Person with Disability (PWD)</div>
        <div class="map-legend-item"><span class="legend-dot" style="background:#d97706;"></span> Senior Citizen (60+)</div>
        <div class="map-legend-item"><span class="legend-dot" style="background:#059669;"></span> Solo Parent</div>
        <div class="map-legend-item"><span class="legend-dot" style="background:#dc2626;"></span> Barangay Hall & Facilities</div>
        <div class="map-legend-item" style="font-size:10.5px; color:#64748b; margin-top:2px;">
          <i class="fas fa-circle-dot" style="margin-right:2px;"></i> Solid border = Exact GPS | Dashed = Purok zone
        </div>
      </div>

      <!-- Slide-out Drawer: Unmapped Residents -->
      <div class="unmapped-drawer" id="unmappedDrawer">
        <div class="unmapped-drawer-header">
          <h4><i class="fas fa-user-tag" style="color:#2563eb;"></i> Unmapped Residents</h4>
          <button id="btnCloseDrawer" style="background:none; border:none; font-size:16px; color:#64748b; cursor:pointer;">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div style="padding:8px 12px; border-bottom:1px solid #f1f5f9;">
          <input type="text" id="drawerSearchInput" class="plot-search-input" placeholder="Search unmapped resident..." style="width:100%;">
        </div>
        <div class="unmapped-drawer-body" id="unmappedDrawerList">
          <div style="text-align:center; padding:20px; color:#94a3b8; font-size:13px;">
            <i class="fas fa-spinner fa-spin"></i> Loading residents...
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet & MarkerCluster JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const defaultCenter = [{{ $center['lat'] }}, {{ $center['lng'] }}];
  const csrfToken = '{{ csrf_token() }}';

  // ── 1. Initialize Leaflet Map ─────────────────────────────
  const map = L.map('map', {
    center: defaultCenter,
    zoom: 16,
    zoomControl: false,
    minZoom: 13,
    maxZoom: 19
  });

  L.control.zoom({ position: 'topright' }).addTo(map);

  // Tile Layers
  const tileLayers = {
    streets: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri, Earthstar Geographics',
      maxZoom: 19
    }),
    hybrid: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; OpenTopoMap contributors',
      maxZoom: 17
    })
  };

  let currentTileLayer = tileLayers.streets.addTo(map);

  document.getElementById('basemapSelect').addEventListener('change', function(e) {
    const selected = e.target.value;
    map.removeLayer(currentTileLayer);
    currentTileLayer = tileLayers[selected] || tileLayers.streets;
    currentTileLayer.addTo(map);
  });

  // ── 2. Marker Clusters & Layer Groups ─────────────────────
  const clusterGroup = L.markerClusterGroup({
    showCoverageOnHover: false,
    maxClusterRadius: 40,
    spiderfyOnMaxZoom: true,
    disableClusteringAtZoom: 18
  });
  map.addLayer(clusterGroup);

  const landmarksGroup = L.layerGroup().addTo(map);

  // State
  let currentFilter = 'all';
  let currentPurok = 'all';
  let currentSearch = '';
  let showEstimated = true;
  let activePinningResident = null;
  let markersData = [];

  // ── 3. Icon Generators ────────────────────────────────────
  function createResidentIcon(marker) {
    const isEst = marker.is_estimated;
    const estClass = isEst ? 'estimated' : '';
    let iconClass = 'fa-house-user';

    if (marker.is_pwd) iconClass = 'fa-wheelchair';
    else if (marker.is_senior) iconClass = 'fa-person-cane';
    else if (marker.is_solo) iconClass = 'fa-children';

    const html = `
      <div class="custom-pin-marker ${estClass}" style="background-color: ${marker.color};">
        <i class="fas ${iconClass}"></i>
      </div>
    `;

    return L.divIcon({
      className: 'custom-leaflet-div-icon',
      html: html,
      iconSize: [32, 32],
      iconAnchor: [16, 32],
      popupAnchor: [0, -30]
    });
  }

  function createLandmarkIcon(lm) {
    const html = `
      <div class="landmark-pin-marker" style="background-color: ${lm.color};">
        <i class="fas fa-${lm.icon}"></i>
      </div>
    `;
    return L.divIcon({
      className: 'custom-landmark-div-icon',
      html: html,
      iconSize: [34, 34],
      iconAnchor: [17, 17],
      popupAnchor: [0, -18]
    });
  }

  // ── 4. Popup Content Generator ────────────────────────────
  function buildPopupContent(m) {
    const statusBadge = m.is_estimated
      ? `<span style="background:#f1f5f9; color:#64748b; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; border:1px dashed #cbd5e1;">Purok Zone Estimate</span>`
      : `<span style="background:#dcfce7; color:#15803d; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px;"><i class="fas fa-check-circle"></i> Verified GPS Pin</span>`;

    const tagColorMap = {
      'PWD': '#7c3aed',
      'Senior Citizen': '#d97706',
      'Solo Parent': '#059669',
      'General': '#2563eb'
    };
    const tagBg = tagColorMap[m.primary_tag] || '#2563eb';

    return `
      <div class="map-card-popup">
        <div class="popup-header">
          <h4 class="resident-name">${escapeHtml(m.name)}</h4>
          <span class="tag-badge" style="background:${tagBg}20; color:${tagBg};">${m.primary_tag}</span>
        </div>
        <div style="margin-bottom: 8px;">
          ${statusBadge}
        </div>
        <div class="info-row">
          <i class="fas fa-map-marker-alt"></i>
          <span><strong>${escapeHtml(m.purok)}</strong> — ${escapeHtml(m.address)}</span>
        </div>
        <div class="info-row">
          <i class="fas fa-user"></i>
          <span>${m.gender || 'N/A'}, ${m.age ? m.age + ' yrs old' : 'Age N/A'} (${escapeHtml(m.civil_status || 'Single')})</span>
        </div>
        <div class="info-row">
          <i class="fas fa-phone"></i>
          <span>${escapeHtml(m.contact)}</span>
        </div>
        <div class="info-row">
          <i class="fas fa-check-to-slot"></i>
          <span>Voter Status: <strong>${escapeHtml(m.voter_status || 'Not Registered')}</strong></span>
        </div>

        <div class="actions-row">
          <button class="btn-popup-action secondary" onclick="enablePinReposition(${m.id}, '${escapeHtml(m.name)}', ${m.lat}, ${m.lng})">
            <i class="fas fa-arrows-up-down-left-right"></i> Move Pin
          </button>
          <a href="{{ route('admin.residents') }}?search=${encodeURIComponent(m.last_name)}" class="btn-popup-action primary" target="_blank">
            <i class="fas fa-id-card"></i> View Profile
          </a>
        </div>
      </div>
    `;
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // ── 5. Fetch & Render Data ────────────────────────────────
  function loadMapData() {
    const params = new URLSearchParams({
      filter: currentFilter,
      purok: currentPurok,
      search: currentSearch,
      estimated: showEstimated ? '1' : '0'
    });

    fetch(`{{ route('admin.plot_map.data') }}?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        markersData = data.markers || [];
        clusterGroup.clearLayers();
        landmarksGroup.clearLayers();

        // Render Resident Pins
        const bounds = [];

        markersData.forEach(m => {
          const latLng = [m.lat, m.lng];
          bounds.push(latLng);

          const icon = createResidentIcon(m);
          const marker = L.marker(latLng, { icon: icon, residentId: m.id });
          marker.bindPopup(buildPopupContent(m));
          clusterGroup.addLayer(marker);
        });

        // Render Landmarks
        (data.landmarks || []).forEach(lm => {
          const icon = createLandmarkIcon(lm);
          const lmMarker = L.marker([lm.lat, lm.lng], { icon: icon });
          lmMarker.bindPopup(`
            <div class="map-card-popup">
              <div class="popup-header">
                <h4 class="resident-name" style="color:${lm.color};">${escapeHtml(lm.name)}</h4>
              </div>
              <div class="info-row">
                <i class="fas fa-tag"></i>
                <span><strong>${escapeHtml(lm.type)}</strong></span>
              </div>
              <p style="font-size:12px; color:#64748b; margin:6px 0 0;">${escapeHtml(lm.description)}</p>
            </div>
          `);
          landmarksGroup.addLayer(lmMarker);
        });

        // Update counts
        const exactCount = markersData.filter(m => !m.is_estimated).length;
        const estCount = markersData.filter(m => m.is_estimated).length;
        document.getElementById('kpi-mapped-count').innerText = exactCount.toLocaleString();
        document.getElementById('kpi-unmapped-count').innerText = estCount.toLocaleString();
      })
      .catch(err => console.error('Error loading map data:', err));
  }

  loadMapData();

  // ── 6. Filter & Search Handlers ───────────────────────────
  const sectorBtns = document.querySelectorAll('#sectorFilterGroup .filter-btn');
  sectorBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      sectorBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentFilter = this.getAttribute('data-filter');
      loadMapData();
    });
  });

  document.getElementById('purokSelect').addEventListener('change', function(e) {
    currentPurok = e.target.value;
    loadMapData();
  });

  let searchTimeout = null;
  document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      currentSearch = e.target.value.trim();
      loadMapData();
    }, 350);
  });

  document.getElementById('showEstimatedCheck').addEventListener('change', function(e) {
    showEstimated = e.target.checked;
    loadMapData();
  });

  document.getElementById('btnRecenterMap').addEventListener('click', function() {
    map.setView(defaultCenter, 16);
  });

  // ── 7. Drag-and-Drop Repositioning Pin ─────────────────────
  window.enablePinReposition = function(residentId, residentName, currentLat, currentLng) {
    map.closePopup();

    let targetMarker = null;
    clusterGroup.eachLayer(layer => {
      if (layer.options && layer.options.residentId === residentId) {
        targetMarker = layer;
      }
    });

    if (!targetMarker) {
      alert('Marker not found in current cluster.');
      return;
    }

    // Temporarily take marker out of cluster and make it draggable
    clusterGroup.removeLayer(targetMarker);
    const dragMarker = L.marker([currentLat, currentLng], {
      draggable: true,
      icon: targetMarker.options.icon
    }).addTo(map);

    const banner = document.getElementById('pinningBanner');
    document.getElementById('pinningResidentName').innerText = `${residentName} (Drag pin to new location)`;
    banner.style.display = 'flex';

    function finishReposition(newLatLng) {
      if (!confirm(`Save new coordinates for ${residentName} at [${newLatLng.lat.toFixed(6)}, ${newLatLng.lng.toFixed(6)}]?`)) {
        map.removeLayer(dragMarker);
        banner.style.display = 'none';
        loadMapData();
        return;
      }

      saveResidentCoordinates(residentId, newLatLng.lat, newLatLng.lng, () => {
        map.removeLayer(dragMarker);
        banner.style.display = 'none';
        loadMapData();
      });
    }

    dragMarker.on('dragend', function (event) {
      const position = dragMarker.getLatLng();
      finishReposition(position);
    });

    document.getElementById('btnCancelPinning').onclick = function() {
      map.removeLayer(dragMarker);
      banner.style.display = 'none';
      loadMapData();
    };
  };

  // ── 8. Pinning Unmapped Resident Flow ──────────────────────
  const drawer = document.getElementById('unmappedDrawer');
  const btnOpenDrawer = document.getElementById('btnOpenUnmappedDrawer');
  const btnCloseDrawer = document.getElementById('btnCloseDrawer');

  btnOpenDrawer.addEventListener('click', function() {
    drawer.classList.add('open');
    loadUnmappedList();
  });

  btnCloseDrawer.addEventListener('click', function() {
    drawer.classList.remove('open');
  });

  function loadUnmappedList(searchQuery = '') {
    const listEl = document.getElementById('unmappedDrawerList');
    listEl.innerHTML = `<div style="text-align:center; padding:20px; color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>`;

    fetch(`{{ route('admin.plot_map.unmapped') }}?search=${encodeURIComponent(searchQuery)}`)
      .then(res => res.json())
      .then(data => {
        if (!data.residents || data.residents.length === 0) {
          listEl.innerHTML = `<div style="text-align:center; padding:30px 10px; color:#64748b; font-size:13px;"><i class="fas fa-circle-check" style="color:#16a34a; font-size:24px; margin-bottom:8px; display:block;"></i> All active residents are pinned!</div>`;
          return;
        }

        let html = '';
        data.residents.forEach(r => {
          const name = `${r.last_name}, ${r.first_name}`;
          html += `
            <div class="unmapped-item-card">
              <div class="unmapped-item-info">
                <strong>${escapeHtml(name)}</strong>
                <span>${escapeHtml(r.purok || 'No Purok')} • ${escapeHtml(r.address || '')}</span>
              </div>
              <button class="btn-pin-this" onclick="startPinningResident(${r.id}, '${escapeHtml(name)}')">
                <i class="fas fa-map-pin"></i> Pin
              </button>
            </div>
          `;
        });
        listEl.innerHTML = html;
      })
      .catch(err => {
        listEl.innerHTML = `<div style="color:#ef4444; font-size:12px; padding:10px;">Failed to load residents.</div>`;
      });
  }

  document.getElementById('drawerSearchInput').addEventListener('input', function(e) {
    loadUnmappedList(e.target.value.trim());
  });

  window.startPinningResident = function(id, name) {
    drawer.classList.remove('open');
    activePinningResident = { id: id, name: name };

    const banner = document.getElementById('pinningBanner');
    document.getElementById('pinningResidentName').innerText = name;
    banner.style.display = 'flex';

    map.getContainer().style.cursor = 'crosshair';
  };

  document.getElementById('btnCancelPinning').addEventListener('click', function() {
    activePinningResident = null;
    document.getElementById('pinningBanner').style.display = 'none';
    map.getContainer().style.cursor = '';
  });

  map.on('click', function(e) {
    if (!activePinningResident) return;

    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    const res = activePinningResident;

    if (confirm(`Pin ${res.name}'s house at coordinates [${lat.toFixed(6)}, ${lng.toFixed(6)}]?`)) {
      saveResidentCoordinates(res.id, lat, lng, () => {
        activePinningResident = null;
        document.getElementById('pinningBanner').style.display = 'none';
        map.getContainer().style.cursor = '';
        loadMapData();
      });
    }
  });

  function saveResidentCoordinates(residentId, lat, lng, callback) {
    fetch('{{ route('admin.plot_map.update_resident') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        resident_id: residentId,
        residentId: residentId,
        latitude: lat,
        longitude: lng
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.message || 'Location saved successfully!');
        if (callback) callback();
      } else {
        alert(data.message || 'Error updating location.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Network error saving coordinates.');
    });
  }
});
</script>
@endsection
