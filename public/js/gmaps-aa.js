(function () {
	'use strict';

	var instances = [];
	var googleReady = false;

	function bootAll() {
		document.querySelectorAll('[data-gmaps-aa="1"]').forEach(function (wrapper) {
			if (wrapper.__gmapsAAInited) { return; }
			wrapper.__gmapsAAInited = true;
			try {
				initInstance(wrapper);
			} catch (e) {
				console.error('[gmaps-aa]', e);
			}
		});
	}

	// Callback Google Maps JS.
	window.gmapsAABoot = function () {
		googleReady = true;
		bootAll();
	};

	document.addEventListener('DOMContentLoaded', function () {
		if (googleReady) { bootAll(); }
	});

	function readData(wrapper) {
		var node = wrapper.querySelector('.gmaps-aa-data');
		if (!node) { return null; }
		try {
			return JSON.parse(node.textContent || node.innerText || '{}');
		} catch (e) {
			console.error('[gmaps-aa] invalid JSON', e);
			return null;
		}
	}

	function haversineKm(a, b) {
		var R = 6371;
		var dLat = deg2rad(b.lat - a.lat);
		var dLng = deg2rad(b.lng - a.lng);
		var s = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
			Math.cos(deg2rad(a.lat)) * Math.cos(deg2rad(b.lat)) *
			Math.sin(dLng / 2) * Math.sin(dLng / 2);
		var c = 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
		return R * c;
	}

	function deg2rad(d) { return d * Math.PI / 180; }

	function initInstance(wrapper) {
		var data = readData(wrapper);
		if (!data || !data.config) { return; }

		var config = data.config;
		var mapEl = wrapper.querySelector('.gmaps-aa-map');
		if (!mapEl) { return; }

		var mapOptions = {
			center: config.center,
			zoom: config.zoom,
			streetViewControl: false,
			mapTypeControl: false,
			fullscreenControl: true
		};
		if (config.style && config.style.length) {
			mapOptions.styles = config.style;
		}

		var map = new google.maps.Map(mapEl, mapOptions);
		var infoWindow = new google.maps.InfoWindow();

		var markers = data.points.map(function (p) {
			var opts = {
				position: { lat: p.lat, lng: p.lng },
				map: map,
				title: p.address || ''
			};
			if (p.icon) {
				opts.icon = { url: p.icon, scaledSize: new google.maps.Size(40, 40) };
			}
			var marker = new google.maps.Marker(opts);
			marker.__point = p;
			marker.addListener('click', function () {
				infoWindow.setContent(p.tooltip || '');
				infoWindow.open({ map: map, anchor: marker });
			});
			return marker;
		});

		var clusterer = null;
		if (config.clustering && window.markerClusterer && markerClusterer.MarkerClusterer) {
			clusterer = new markerClusterer.MarkerClusterer({ map: map, markers: markers.slice() });
		}

		// Liste.
		var listEl = wrapper.querySelector('.gmaps-aa-list');
		function renderList(visiblePoints) {
			if (!listEl) { return; }
			listEl.innerHTML = '';
			visiblePoints.forEach(function (p) {
				var w = document.createElement('div');
				w.className = 'gmaps-aa-list-item-wrap';
				w.innerHTML = p.listItem || '';
				w.addEventListener('click', function () {
					map.panTo({ lat: p.lat, lng: p.lng });
					map.setZoom(Math.max(map.getZoom(), 14));
					var m = markers.find(function (mm) { return mm.__point.id === p.id; });
					if (m) {
						infoWindow.setContent(p.tooltip || '');
						infoWindow.open({ map: map, anchor: m });
					}
				});
				listEl.appendChild(w);
			});
		}

		// Filtres.
		var currentFilters = {};  // taxonomy => array of term IDs (numbers), empty/undefined = pas de filtre
		var searchCenter = null;
		var searchRadiusKm = config.search && config.search.radius ? config.search.radius : 0;
		var searchCircle = null;

		function passesFilters(point) {
			// AND entre taxonomies, OR à l'intérieur.
			for (var tax in currentFilters) {
				if (!currentFilters.hasOwnProperty(tax)) { continue; }
				var needed = currentFilters[tax];
				if (!needed || !needed.length) { continue; }
				var has = point.terms && point.terms[tax];
				if (!has || !has.length) { return false; }
				var ok = needed.some(function (n) { return has.indexOf(n) !== -1; });
				if (!ok) { return false; }
			}
			// Géographique.
			if (searchCenter && searchRadiusKm > 0) {
				var d = haversineKm(searchCenter, { lat: point.lat, lng: point.lng });
				if (d > searchRadiusKm) { return false; }
			}
			return true;
		}

		function applyFilters() {
			var visiblePoints = [];
			var visibleMarkers = [];
			markers.forEach(function (m) {
				var ok = passesFilters(m.__point);
				m.setMap(ok ? map : null);
				if (ok) {
					visiblePoints.push(m.__point);
					visibleMarkers.push(m);
				}
			});
			if (clusterer) {
				clusterer.clearMarkers();
				clusterer.addMarkers(visibleMarkers);
			}
			renderList(visiblePoints);
		}

		// Branche les inputs de filtres.
		wrapper.querySelectorAll('.gmaps-aa-filter-input').forEach(function (input) {
			var tax = input.getAttribute('data-taxonomy');
			var handler = function () {
				if (input.tagName === 'SELECT') {
					var v = input.value;
					currentFilters[tax] = v ? [parseInt(v, 10)] : [];
				} else if (input.type === 'radio') {
					if (input.checked) {
						currentFilters[tax] = input.value ? [parseInt(input.value, 10)] : [];
					}
				} else if (input.type === 'checkbox') {
					if (!currentFilters[tax]) { currentFilters[tax] = []; }
					var val = parseInt(input.value, 10);
					var idx = currentFilters[tax].indexOf(val);
					if (input.checked && idx === -1) {
						currentFilters[tax].push(val);
					} else if (!input.checked && idx !== -1) {
						currentFilters[tax].splice(idx, 1);
					}
				}
				applyFilters();
			};
			input.addEventListener('change', handler);
			// Initialise l'état initial (pour les cases cochées dès le rendu).
			if ((input.type === 'checkbox' || input.type === 'radio') && input.checked) {
				handler();
			} else if (input.tagName === 'SELECT' && input.value) {
				handler();
			}
		});

		// Recherche par adresse.
		var searchInput = wrapper.querySelector('.gmaps-aa-search');
		var radiusInput = wrapper.querySelector('.gmaps-aa-radius');
		var clearBtn = wrapper.querySelector('.gmaps-aa-search-clear');

		if (searchInput) {
			var geocoder = new google.maps.Geocoder();
			var debounceTimer = null;

			function runSearch() {
				var addr = searchInput.value.trim();
				if ('' === addr) {
					searchCenter = null;
					if (searchCircle) { searchCircle.setMap(null); searchCircle = null; }
					applyFilters();
					return;
				}
				geocoder.geocode({ address: addr }, function (results, status) {
					if (status !== 'OK' || !results || !results[0]) {
						return;
					}
					var loc = results[0].geometry.location;
					searchCenter = { lat: loc.lat(), lng: loc.lng() };
					map.panTo(searchCenter);

					if (searchCircle) { searchCircle.setMap(null); searchCircle = null; }
					if (config.search.showCircle) {
						searchCircle = new google.maps.Circle({
							map: map,
							center: searchCenter,
							radius: searchRadiusKm * 1000,
							fillOpacity: 0.1,
							strokeWeight: 1
						});
					}
					applyFilters();
				});
			}

			searchInput.addEventListener('input', function () {
				clearTimeout(debounceTimer);
				debounceTimer = setTimeout(runSearch, 400);
			});

			if (radiusInput) {
				radiusInput.addEventListener('change', function () {
					searchRadiusKm = parseInt(radiusInput.value, 10) || 0;
					if (searchCircle) { searchCircle.setRadius(searchRadiusKm * 1000); }
					applyFilters();
				});
			}
			if (clearBtn) {
				clearBtn.addEventListener('click', function () {
					searchInput.value = '';
					runSearch();
				});
			}
		}

		// Rendu initial.
		applyFilters();

		instances.push({ wrapper: wrapper, map: map, markers: markers });
	}
})();
