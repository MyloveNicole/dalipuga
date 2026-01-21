<?php
include("includes/auth_check.php");
include("db_connect.php");
include("header.php");
?>

<div class="container-fluid px-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Administrators</h1>
            <p class="text-muted mb-0">Manage system administrators and their contact details</p>
        </div>
    </div>

    <!-- Admin Table Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Admin List</h6>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Username</th>
                            <th scope="col">Email Address</th>
                            <th scope="col">Contact Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT Admin_Id, Username, Email, Contact_Number FROM admin";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Admin_Id']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['Username']) ?></td>
                                    <td><?= htmlspecialchars($row['Email']) ?></td>
                                    <td><?= htmlspecialchars($row['Contact_Number']) ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    No administrators found.
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Letters Map Card (shows locations of residents who sent letters) -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Residents Who Sent Letters — Map</h6>
            <small class="text-muted">Markers show location for each submitted letter</small>
        </div>
        <div class="card-body">
            <div id="lettersMap" style="height: 500px; border-radius: 8px;"></div>
            <p class="small text-muted mt-2">Note: locations are approximated from the address text.</p>
        </div>
    </div>

</div>

<?php include("footer.php"); ?>

<!-- Leaflet CSS/JS for the admin map -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<!-- Leaflet Geocoding -->
<script src="https://unpkg.com/leaflet-control-geocoder@1.14.1/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@1.14.1/dist/Control.Geocoder.css" />

<script>
// Address to coordinate mapping for Dalipuga area
const addressCoordinates = {
    '123 Mabini St': [8.9145, 124.3012],
    '456 Rizal Ave': [8.9168, 124.3045],
    '789 Bonifacio St': [8.9122, 124.2989],
    '321 Palma St': [8.9188, 124.3078],
    '654 Quezon Ave': [8.9098, 124.2967],
    'Purok 5': [8.9155, 124.3025],
    'Dalipuga': [8.915, 124.301]
};

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function geocodeAddress(address) {
    // Check if we have a predefined coordinate for this address
    if (addressCoordinates[address]) {
        return Promise.resolve(addressCoordinates[address]);
    }

    // Try to extract location from address string
    for (const [key, coords] of Object.entries(addressCoordinates)) {
        if (address.toLowerCase().includes(key.toLowerCase())) {
            return Promise.resolve(coords);
        }
    }

    // If no match, use Nominatim (OpenStreetMap) geocoding
    // Adding "Dalipuga" to the search to get better results
    const searchQuery = address + ", Dalipuga, Lanao del Sur, Philippines";
    
    return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            } else {
                // Fallback to general Dalipuga area with a slight offset
                const hash = address.split('').reduce((h, c) => ((h << 5) - h) + c.charCodeAt(0) | 0, 0);
                const offsetLat = ((hash & 0xffff) / 0xffff - 0.5) * 0.02;
                const offsetLng = (((hash >>> 16) & 0xffff) / 0xffff - 0.5) * 0.02;
                return [8.915 + offsetLat, 124.301 + offsetLng];
            }
        })
        .catch(err => {
            console.warn('Geocoding error:', err);
            // Return Dalipuga center as fallback
            return [8.915, 124.301];
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // Center of Dalipuga, Lanao del Sur
    const centerLat = 8.915;
    const centerLng = 124.301;

    const map = L.map('lettersMap').setView([centerLat, centerLng], 14);

    // Use satellite/map hybrid layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Add a layer for satellite view option
    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri',
        maxZoom: 19
    });

    // Layer control
    L.control.layers(
        {
            'Map': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19
            }),
            'Satellite': satellite
        },
        {}
    ).addTo(map);

    // Custom icon for letter markers
    const letterIcon = L.icon({
        iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDI0IDI0Ij48cmVjdCBmaWxsPSIjZmY0NDQ0IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHJ4PSIyIi8+PHRleHQgeD0iNiIgeT0iMTgiIGZvbnQtc2l6ZT0iMTYiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSI+4pmkPC90ZXh0Pjwvc3ZnPg==',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    fetch('get_letters_locations.php')
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                console.log('No letters found');
                return;
            }

            console.log('Found ' + data.length + ' letters');

            // Process each letter and geocode its location
            data.forEach((letter, index) => {
                const locationText = letter.resident_location || 'Dalipuga';
                
                geocodeAddress(locationText).then(coords => {
                    const lat = coords[0];
                    const lng = coords[1];

                    const statusBadge = letter.status === 'unread' ? 
                        '<span class="badge bg-warning">Unread</span>' : 
                        '<span class="badge bg-success">Read</span>';

                    const popupHtml = `
                        <div style="min-width:240px; font-family: Arial, sans-serif;">
                            <div style="margin-bottom: 10px;">
                                <strong style="color:#d32f2f; font-size: 16px;">${escapeHtml(letter.resident_name || 'Unnamed')}</strong><br>
                                ${statusBadge}
                            </div>
                            <hr style="margin: 8px 0; border: none; border-top: 1px solid #ddd;">
                            <div style="font-size: 13px; color: #333;">
                                <strong>Subject:</strong> ${escapeHtml(letter.subject || 'N/A')}<br>
                                <strong>Location:</strong> ${escapeHtml(letter.resident_location || 'N/A')}<br>
                                <strong>📞 Contact:</strong> ${escapeHtml(letter.resident_contact || 'N/A')}<br>
                                <strong>Date:</strong> ${new Date(letter.date_sent).toLocaleString()}<br>
                            </div>
                        </div>
                    `;

                    const marker = L.marker([lat, lng], { icon: letterIcon }).addTo(map);
                    marker.bindPopup(popupHtml);
                    
                    // Add circle around marker to show area
                    L.circle([lat, lng], {
                        color: '#ff4444',
                        fillColor: '#ff4444',
                        fillOpacity: 0.1,
                        radius: 100
                    }).addTo(map);

                    console.log('Added marker for: ' + letter.resident_name + ' at ' + lat + ',' + lng);
                }).catch(err => {
                    console.error('Error geocoding address: ' + locationText, err);
                });
            });
        })
        .catch(err => {
            console.error('Error loading letters for map:', err);
        });

    // Add a scale control
    L.control.scale().addTo(map);
});
</script>