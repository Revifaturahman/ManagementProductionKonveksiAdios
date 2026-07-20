
let map;
let marker;

let createModal;
let mapModalInstance;

// ======================
// NORMALIZE
// ======================

function normalizeAddress(q) {

    q = q.toLowerCase();

    q = q.replace(/\bjl\b/gi, 'jalan');
    q = q.replace(/\bjln\b/gi, 'jalan');
    q = q.replace(/\bgg\b/gi, 'gang');

    q = q.replace(/\bkec\b\.?/gi, '');
    q = q.replace(/\bkecamatan\b/gi, '');

    q = q.replace(/\s+/g, ' ').trim();

    return q;

}

// ======================
// REVERSE GEOCODE
// ======================

async function reverseGeocode(lat, lng) {

    try {

        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`
        );

        const data = await response.json();

        if (data.display_name) {

            document.getElementById('address').value =
                data.display_name;

        }

    } catch (err) {

        console.log(err);

    }

}

// ======================
// OPEN MAP MODAL
// ======================

window.openMapModal = function () {

    createModal =
        bootstrap.Modal.getInstance(
            document.getElementById('obrasModal')
        );

    mapModalInstance =
        new bootstrap.Modal(
            document.getElementById('mapModal')
        );

    mapModalInstance.show();

}

// ======================
// SEARCH LOCATION
// ======================

window.searchLocation = async function () {

    let query =
        document.getElementById('searchLocation').value;

    if (!query) {
        alert('Masukkan alamat');
        return;
    }

    query = normalizeAddress(query);

    const queries = [

        query,

        query + ', Bandung',

        query + ', Jawa Barat',

        query + ', Indonesia'

    ];

    for (let q of queries) {

        try {

            const response = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&accept-language=id&limit=1`
            );

            const data = await response.json();

            if (data.length > 0) {

                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);

                map.setView([lat, lng], 18);

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                document.getElementById('address').value =
                    data[0].display_name;

                if (marker) {

                    marker.setLatLng([lat, lng]);

                } else {

                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);

                }

                return;

            }

        } catch (err) {

            console.log(err);

        }

    }

    alert('Lokasi tidak ditemukan');

}

// ======================
// INIT MAP
// ======================

document.addEventListener('DOMContentLoaded', function () {

    const mapModal =
        document.getElementById('mapModal');

    if (!mapModal) return;

    mapModal.addEventListener('shown.bs.modal', function () {

        if (!map) {

            map = L.map('map')
                .setView([-6.914744, 107.609810], 13);

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    attribution:
                        '&copy; OpenStreetMap contributors'
                }
            ).addTo(map);

            map.on('click', async function (e) {

                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                await reverseGeocode(lat, lng);

                if (marker) {

                    marker.setLatLng(e.latlng);

                } else {

                    marker = L.marker(e.latlng, {
                        draggable: true
                    }).addTo(map);

                }

                mapModalInstance.hide();

                setTimeout(() => {

                    createModal.show();

                }, 300);

            });

        }

        setTimeout(() => {

            map.invalidateSize();

        }, 200);

    });

});