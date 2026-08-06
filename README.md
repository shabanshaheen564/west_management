# 🗑 Smart Waste Management GIS System

نظام إدارة النفايات الذكي المبني على Laravel 12 مع دعم كامل للخرائط التفاعلية GIS

---

## 📋 Requirements

- PHP 8.2+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (optional, for asset compilation)

---

## 🚀 Installation

### 1. Clone & Install Dependencies
```bash
git clone <repo-url> waste-management
cd waste-management
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:
```env
DB_DATABASE=waste_management
DB_USERNAME=root
DB_PASSWORD=your_password

# OpenRouteService API (free at openrouteservice.org)
ORS_API_KEY=your_ors_api_key

# Default map center (Nablus, West Bank)
DEFAULT_LAT=31.9038
DEFAULT_LNG=35.2034
DEFAULT_ZOOM=13
```

### 3. Database Setup
```bash
# Create database first
mysql -u root -p -e "CREATE DATABASE waste_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed with sample data
php artisan db:seed
```

### 4. Storage Link
```bash
php artisan storage:link
```

### 5. Publish Spatie Permission Config
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 6. Run the Application
```bash
php artisan serve
# Visit: http://localhost:8000
```

---

## 🔐 Default Credentials

| Role       | Email                    | Password      |
|------------|--------------------------|---------------|
| Admin      | admin@waste.local        | Admin@123456  |
| Supervisor | supervisor@waste.local   | Super@123     |
| User       | user@waste.local         | User@123      |

---

## 📁 Project Structure

```
waste-management/
├── app/
│   ├── Http/Controllers/          # All controllers
│   │   ├── Auth/LoginController   # Authentication
│   │   ├── DashboardController    # Dashboard + charts
│   │   ├── ContainerController    # CRUD + GeoJSON + heatmap
│   │   ├── VehicleController      # Fleet management
│   │   ├── DriverController       # Driver management
│   │   ├── RouteController        # Route planning + ORS optimization
│   │   ├── DumpsiteController     # Waste sites
│   │   ├── ComplaintController    # Citizen complaints
│   │   ├── GISController          # Spatial analysis + map layers
│   │   ├── ReportController       # PDF report generation
│   │   ├── UserController         # User management
│   │   ├── RoleController         # Role & permission management
│   │   ├── SettingsController     # System settings
│   │   └── API/                   # REST API controllers
│   ├── Models/                    # Eloquent models with GeoJSON support
│   ├── Services/
│   │   ├── GIS/RouteOptimizationService  # OpenRouteService integration
│   │   └── Reports/PdfReportService      # DomPDF report generation
│   ├── Exports/                   # Maatwebsite Excel exports
│   ├── Imports/                   # Excel imports
│   └── Helpers/GISHelper.php      # Haversine, TSP, bounding box
│
├── resources/views/
│   ├── layouts/skeleton.blade.php # Main layout (RTL/LTR, sidebar, topbar)
│   ├── auth/login.blade.php       # Login page
│   ├── waste_management/          # All module views
│   └── reports/                   # PDF report templates
│
├── database/
│   ├── migrations/                # Full schema migrations
│   └── seeders/                   # Admin users + sample data
│
└── routes/
    ├── web.php                    # Web routes + locale switcher
    └── api.php                    # REST API routes
```

---

## ✨ Features

### 🗺 GIS Mapping (Leaflet)
- Interactive map with multiple layers (containers, vehicles, dumpsites, complaints)
- **Heatmap** of container fill levels
- **Marker clustering** for dense container areas
- **Spatial analysis**: radius search, nearest dumpsite, isochrone, coverage analysis
- **GeoJSON** import/export
- Multiple base maps: OSM, Satellite, Topographic, Dark
- Real-time vehicle tracking on map
- Fill-level filter slider
- Route path visualization

### 🚛 Route Optimization (OpenRouteService)
- Automatic **TSP (Traveling Salesman)** optimization
- Integration with **OpenRouteService API** for real directions
- Fallback nearest-neighbor algorithm when API unavailable
- Visual route preview on map with ordered waypoints
- Distance and duration estimation
- One-click "Select Full Containers" optimization

### 📊 Analytics & Charts
- Collection trend (7-day bar + line chart)
- Container fill distribution (donut chart)
- Monthly complaint trend (30-day area chart)
- Vehicle status breakdown
- Real-time KPI cards

### 🌐 Bilingual (Arabic / English)
- Full RTL support for Arabic
- Language switcher in navbar + user preference saved
- All UI labels and messages translated
- Bootstrap 5 RTL stylesheet auto-loaded for Arabic

### 📥📤 Excel Import / Export
- Import containers, vehicles, drivers from Excel/CSV
- Export all modules to styled Excel with color-coded headers
- Column validation and error skipping

### 📄 PDF Reports
- Containers, Vehicles, Routes, Complaints, Dashboard Summary
- Arabic-compatible DejaVu Sans font
- Styled with statistics header and KPI boxes

### 🔔 Notifications
- Laravel notification system
- Mark all read
- Badge count in topbar

### 🔒 Roles & Permissions
- **Admin**: full access
- **Supervisor**: operations + reports
- **Driver**: view routes + map
- **User**: submit complaints + view map
- Spatie Laravel Permission integration
- Per-permission checkboxes in UI

---

## 🛠 API Endpoints

### Public
```
GET  /api/v1/map/summary        # System statistics
GET  /api/v1/map/containers     # GeoJSON containers (filterable)
GET  /api/v1/map/vehicles       # GeoJSON live vehicles
GET  /api/v1/map/heatmap        # Heatmap data points
GET  /api/v1/map/complaints     # GeoJSON complaints
GET  /api/v1/map/dumpsites      # GeoJSON dumpsites
```

### Authenticated (Bearer token via Sanctum)
```
GET    /api/v1/vehicles/tracking              # Live vehicle positions
POST   /api/v1/vehicles/{id}/location         # Update vehicle GPS
PATCH  /api/v1/containers/{id}/fill-level     # IoT sensor update
PATCH  /api/v1/containers/{id}/emptied        # Mark container emptied
POST   /api/v1/routes/optimize                # Route optimization
```

---

## 🎨 Tech Stack

| Layer      | Technology                              |
|------------|-----------------------------------------|
| Backend    | Laravel 12, PHP 8.2                     |
| Database   | MySQL 8 + phpMyAdmin                    |
| Frontend   | Bootstrap 5 (RTL/LTR), jQuery, AJAX     |
| Maps       | Leaflet 1.9 + MarkerCluster + Heat      |
| Charts     | Chart.js 4                              |
| Excel      | Maatwebsite Excel 3.1                   |
| PDF        | barryvdh/laravel-dompdf                 |
| GIS API    | OpenRouteService                        |
| Auth       | Laravel Sanctum                         |
| Permissions| Spatie Laravel Permission               |
| Realtime   | Laravel Reverb (WebSockets)             |

---

## 📦 Key Packages

```json
"spatie/laravel-permission": "^6.0",   // Roles & permissions
"maatwebsite/excel": "^3.1",           // Excel import/export
"barryvdh/laravel-dompdf": "^2.2",     // PDF generation
"laravel/sanctum": "^4.0",             // API authentication
"laravel/reverb": "^1.0",              // WebSocket broadcasting
"guzzlehttp/guzzle": "^7.8"            // OpenRouteService HTTP client
```

---

## 🗺 IoT Integration (Container Sensors)

The system supports IoT sensors via REST API:

```bash
# Update container fill level from sensor
PATCH /api/v1/containers/{id}/fill-level
Authorization: Bearer {token}
Content-Type: application/json

{
  "fill_level": 87.5
}
```

```bash
# Update vehicle GPS from tracker
POST /api/v1/vehicles/{id}/location
Authorization: Bearer {token}

{
  "latitude": 31.9123,
  "longitude": 35.2045,
  "speed": 45,
  "heading": 270,
  "fuel_level": 65
}
```

---

## 🔧 Customization

### Change Default Map Center
Edit `.env`:
```env
DEFAULT_LAT=31.9038
DEFAULT_LNG=35.2034
DEFAULT_ZOOM=13
```

### Change Primary Color
In Admin → Settings → General → Primary Color

### Add New Language
1. Create `resources/lang/{code}/messages.php`
2. Add to locale switcher in `routes/web.php`

---

## 📧 Support

نظام إدارة النفايات الذكي — Built with ❤️ using Laravel 12 + Leaflet GIS
