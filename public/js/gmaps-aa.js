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

	// Popup personnalisée basée sur google.maps.OverlayView (DOM sous notre contrôle).
	function createPopupClass() {
		function Popup() {
			google.maps.OverlayView.call(this);
			this.position = null;
			this.pixelOffset = { x: 0, y: -24 };

			this.container = document.createElement('div');
			this.container.className = 'gmaps-aa-popup';

			this.closeBtn = document.createElement('button');
			this.closeBtn.type = 'button';
			this.closeBtn.className = 'gmaps-aa-popup-close';
			this.closeBtn.setAttribute('aria-label', 'Fermer');
			this.closeBtn.innerHTML = '&times;';

			this.body = document.createElement('div');
			this.body.className = 'gmaps-aa-popup-body';

			this.container.appendChild(this.closeBtn);
			this.container.appendChild(this.body);

			var self = this;
			this.closeBtn.addEventListener('click', function () { self.close(); });

			// Évite que les clics/scroll dans la popup ne déclenchent la carte.
			google.maps.OverlayView.preventMapHitsAndGesturesFrom(this.container);
		}
		Popup.prototype = Object.create(google.maps.OverlayView.prototype);

		Popup.prototype.setContent = function (html) {
			this.body.innerHTML = html || '';
		};
		Popup.prototype.setPixelOffset = function (offset) {
			this.pixelOffset = offset || { x: 0, y: -24 };
			this.draw();
		};
		Popup.prototype.setPosition = function (latLng) {
			this.position = latLng;
			this.draw();
		};
		Popup.prototype.open = function (options) {
			options = options || {};
			if (options.anchor && options.anchor.getPosition) {
				this.position = options.anchor.getPosition();
			}
			if (options.map) {
				this.setMap(options.map);
			}
		};
		Popup.prototype.close = function () {
			this.setMap(null);
		};
		Popup.prototype.onAdd = function () {
			var panes = this.getPanes();
			if (panes && panes.floatPane) {
				panes.floatPane.appendChild(this.container);
			}
		};
		Popup.prototype.onRemove = function () {
			if (this.container.parentNode) {
				this.container.parentNode.removeChild(this.container);
			}
		};
		Popup.prototype.draw = function () {
			if (!this.position) { return; }
			var proj = this.getProjection();
			if (!proj) { return; }
			var px = proj.fromLatLngToDivPixel(this.position);
			if (!px) { return; }
			this.container.style.left = (px.x + this.pixelOffset.x) + 'px';
			this.container.style.top = (px.y + this.pixelOffset.y) + 'px';
		};
		return Popup;
	}

	function initInstance(wrapper) {
		var data = readData(wrapper);
		if (!data || !data.config) { return; }

		var config = data.config;
		var mapEl = wrapper.querySelector('.gmaps-aa-map');
		if (!mapEl) { return; }

		// Mobile / tactile : toujours zoom direct (greedy, un doigt déplace,
		// pinch pour zoomer). Desktop : toujours greedy aussi — si le mode
		// « coopératif » est demandé, on simule manuellement en exigeant Ctrl,
		// sans afficher le message natif de Google Maps.
		var isCoarsePointer = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
		var mapOptions = {
			center: config.center,
			zoom: config.zoom,
			minZoom: config.zoomMin || 1,
			maxZoom: config.zoomMax || 22,
			zoomControl: true,
			streetViewControl: false,
			mapTypeControl: false,
			fullscreenControl: true,
			gestureHandling: 'greedy'
		};
		if (config.style && config.style.length) {
			mapOptions.styles = config.style;
		}

		var map = new google.maps.Map(mapEl, mapOptions);

		// Simulation du mode « coopératif » sans le message natif :
		// en capture on intercepte le wheel et on bloque sa propagation
		// vers Google Maps si Ctrl/Cmd n'est pas maintenu.
		if (config.cooperativeZoom && !isCoarsePointer) {
			mapEl.addEventListener('wheel', function (e) {
				if (!e.ctrlKey && !e.metaKey) {
					e.stopPropagation();
				}
			}, { capture: true, passive: true });
		}

		var markerW = parseInt(config.markerWidth, 10) || 32;
		var markerSize = new google.maps.Size(markerW, markerW);
		var defaultIconUrl = config.defaultIconUrl || '';

		var Popup = createPopupClass();
		var popup = new Popup();
		// L'anchor par défaut du Marker est au bas-centre de l'image :
		// la position lat/lng correspond donc à la pointe du pin.
		// On remonte la popup de (hauteur du marker) + un petit écart.
		popup.setPixelOffset({ x: 0, y: -(markerW + 8) });

		function openPopupOn(marker, point) {
			popup.setContent(point.tooltip || '');
			popup.open({ map: map, anchor: marker });
			// Deux rAF pour laisser Google Maps positionner la popup et le
			// navigateur calculer sa taille avant de corriger le débordement.
			requestAnimationFrame(function () {
				requestAnimationFrame(ensurePopupVisible);
			});
		}

		function ensurePopupVisible() {
			if (!popup.container || !popup.container.offsetParent) { return; }
			var mapRect = mapEl.getBoundingClientRect();
			var popRect = popup.container.getBoundingClientRect();
			var margin = 12;
			var dx = 0;
			var dy = 0;

			if (popRect.left < mapRect.left + margin) {
				dx = popRect.left - (mapRect.left + margin);
			} else if (popRect.right > mapRect.right - margin) {
				dx = popRect.right - (mapRect.right - margin);
			}
			if (popRect.top < mapRect.top + margin) {
				dy = popRect.top - (mapRect.top + margin);
			} else if (popRect.bottom > mapRect.bottom - margin) {
				dy = popRect.bottom - (mapRect.bottom - margin);
			}
			if (dx !== 0 || dy !== 0) {
				map.panBy(dx, dy);
			}
		}

		// Ferme la popup au clic sur une zone vide de la carte.
		if (config.closePopupOnMapClick) {
			map.addListener('click', function () {
				popup.close();
			});
		}

		// Spiderfier : dépile les marqueurs superposés.
		var oms = null;
		if (config.spiderfier && typeof OverlappingMarkerSpiderfier !== 'undefined') {
			oms = new OverlappingMarkerSpiderfier(map, {
				markersWontMove: true,
				markersWontHide: true,
				keepSpiderfied: true,
				circleSpiralSwitchover: 9,
				legWeight: 2
			});
			oms.addListener('click', function (marker) {
				openPopupOn(marker, marker.__point);
			});
		}

		var markers = data.points.map(function (p) {
			var opts = {
				position: { lat: p.lat, lng: p.lng },
				map: map,
				title: p.address || '',
				// Rend les markers comme <img> individuels dans le DOM : nécessaire
				// pour que les filtres CSS (ombrage) s'appliquent.
				optimized: false
			};
			var iconUrl = p.icon || defaultIconUrl;
			if (iconUrl) {
				opts.icon = { url: iconUrl, scaledSize: markerSize };
			}
			var marker = new google.maps.Marker(opts);
			marker.__point = p;
			if (oms) {
				oms.addMarker(marker);
			} else {
				marker.addListener('click', function () {
					openPopupOn(marker, p);
				});
			}
			return marker;
		});

		// Liste + pagination.
		var listEl = wrapper.querySelector('.gmaps-aa-list');
		var paginationEl = wrapper.querySelector('.gmaps-aa-pagination');
		var pageCurrentEl = wrapper.querySelector('.gmaps-aa-page-current');
		var pageTotalEl = wrapper.querySelector('.gmaps-aa-page-total');
		var pagePrev = wrapper.querySelector('.gmaps-aa-page-prev');
		var pageNext = wrapper.querySelector('.gmaps-aa-page-next');
		var perPage = parseInt(config.perPage, 10) || 0;
		var currentPage = 1;
		var lastVisiblePoints = [];

		function renderListPage() {
			if (!listEl) { return; }
			listEl.innerHTML = '';
			var points = lastVisiblePoints;
			var slice;
			if (perPage > 0) {
				var start = (currentPage - 1) * perPage;
				slice = points.slice(start, start + perPage);
			} else {
				slice = points;
			}
			var clickAction = config.listClickAction || 'tooltip';
			slice.forEach(function (p) {
				var w;
				if (clickAction === 'link' && p.url) {
					w = document.createElement('a');
					w.href = p.url;
					w.className = 'gmaps-aa-list-item-wrap gmaps-aa-list-item-link';
				} else {
					w = document.createElement('div');
					w.className = 'gmaps-aa-list-item-wrap';
				}
				w.innerHTML = p.listItem || '';

				if (clickAction === 'tooltip') {
					w.addEventListener('click', function () {
						map.panTo({ lat: p.lat, lng: p.lng });
						map.setZoom(Math.max(map.getZoom(), 14));
						var m = markers.find(function (mm) { return mm.__point.id === p.id; });
						if (m) {
							openPopupOn(m, p);
						}
					});
				} else if (clickAction === 'none') {
					w.classList.add('gmaps-aa-list-item-inert');
				}
				// 'link' : comportement natif <a href> (aucun handler JS).

				listEl.appendChild(w);
			});
			updatePaginationUI();
		}

		function updatePaginationUI() {
			if (!paginationEl) { return; }
			var total = lastVisiblePoints.length;
			if (perPage <= 0 || total <= perPage) {
				paginationEl.hidden = true;
				return;
			}
			var totalPages = Math.ceil(total / perPage);
			paginationEl.hidden = false;
			if (pageCurrentEl) { pageCurrentEl.textContent = String(currentPage); }
			if (pageTotalEl) { pageTotalEl.textContent = String(totalPages); }
			if (pagePrev) { pagePrev.disabled = currentPage <= 1; }
			if (pageNext) { pageNext.disabled = currentPage >= totalPages; }
		}

		function renderList(visiblePoints) {
			lastVisiblePoints = visiblePoints;
			currentPage = 1;
			renderListPage();
		}

		if (pagePrev) {
			pagePrev.addEventListener('click', function () {
				if (currentPage > 1) { currentPage -= 1; renderListPage(); }
			});
		}
		if (pageNext) {
			pageNext.addEventListener('click', function () {
				var totalPages = perPage > 0 ? Math.ceil(lastVisiblePoints.length / perPage) : 1;
				if (currentPage < totalPages) { currentPage += 1; renderListPage(); }
			});
		}

		// Filtres : deux familles.
		//   tax[taxonomy] = [termId, ...]       (ids numériques)
		//   acf[fieldName] = [value, ...]       (valeurs string)
		var currentFilters = { tax: {}, acf: {} };

		// Logique par filtre (OR par défaut, AND si l'admin l'a choisi).
		var filterLogic = { tax: {}, acf: {} };
		(data.filters || []).forEach(function (f) {
			var key = f.type === 'acf' ? f.field : f.taxonomy;
			if (!key) { return; }
			filterLogic[f.type][key] = f.logic === 'and' ? 'and' : 'or';
		});

		var searchCenter = null;
		var searchRadiusKm = config.search && config.search.radius ? config.search.radius : 0;

		function hasAny(obj) {
			for (var k in obj) {
				if (obj.hasOwnProperty(k) && obj[k] && obj[k].length) { return true; }
			}
			return false;
		}

		function passesFilters(point) {
			// Taxonomies — AND entre taxos ; OR/AND intra selon la config du filtre.
			for (var tax in currentFilters.tax) {
				if (!currentFilters.tax.hasOwnProperty(tax)) { continue; }
				var neededT = currentFilters.tax[tax];
				if (!neededT || !neededT.length) { continue; }
				var hasT = point.terms && point.terms[tax];
				if (!hasT || !hasT.length) { return false; }
				var logicT = filterLogic.tax[tax] === 'and' ? 'and' : 'or';
				var okT = logicT === 'and'
					? neededT.every(function (n) { return hasT.indexOf(n) !== -1; })
					: neededT.some(function (n) { return hasT.indexOf(n) !== -1; });
				if (!okT) { return false; }
			}
			// Champs ACF — AND entre champs ; OR/AND intra selon la config du filtre.
			for (var field in currentFilters.acf) {
				if (!currentFilters.acf.hasOwnProperty(field)) { continue; }
				var neededA = currentFilters.acf[field];
				if (!neededA || !neededA.length) { continue; }
				var v = point.acfValues && point.acfValues[field];
				if (v === undefined || v === null || v === '') { return false; }
				var arr = (Array.isArray(v) ? v : [v]).map(String);
				var logicA = filterLogic.acf[field] === 'and' ? 'and' : 'or';
				var okA = logicA === 'and'
					? neededA.every(function (n) { return arr.indexOf(String(n)) !== -1; })
					: neededA.some(function (n) { return arr.indexOf(String(n)) !== -1; });
				if (!okA) { return false; }
			}
			// Géographique.
			if (searchCenter && searchRadiusKm > 0) {
				var d = haversineKm(searchCenter, { lat: point.lat, lng: point.lng });
				if (d > searchRadiusKm) { return false; }
			}
			return true;
		}

		var isInitialRender = true;

		function applyFilters(opts) {
			opts = opts || {};
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
			renderList(visiblePoints);

			// Fit bounds après filtrage (jamais au boot, jamais après une recherche géographique
			// où l'on préfère centrer sur l'adresse trouvée avec config.zoomSearch).
			if (
				config.fitbounds &&
				!isInitialRender &&
				!opts.skipFitBounds &&
				visibleMarkers.length > 0
			) {
				var bounds = new google.maps.LatLngBounds();
				visibleMarkers.forEach(function (m) { bounds.extend(m.getPosition()); });
				map.fitBounds(bounds);
			}
			isInitialRender = false;
		}

		// Branche les inputs de filtres (taxonomies et champs ACF).
		wrapper.querySelectorAll('.gmaps-aa-filter-input').forEach(function (input) {
			var type = input.getAttribute('data-filter-type') || 'tax';
			var key = type === 'acf'
				? input.getAttribute('data-field')
				: input.getAttribute('data-taxonomy');
			if (!key) { return; }

			// Pour les valeurs : tax = int (term_id), acf = string (valeur brute)
			var toValue = function (raw) {
				if (raw === '' || raw === null || raw === undefined) { return null; }
				return type === 'acf' ? String(raw) : parseInt(raw, 10);
			};

			var bag = currentFilters[type];

			var handler = function () {
				if (input.tagName === 'SELECT') {
					var v = toValue(input.value);
					bag[key] = (v === null) ? [] : [v];
				} else if (input.type === 'radio') {
					if (input.checked) {
						var v2 = toValue(input.value);
						bag[key] = (v2 === null) ? [] : [v2];
					}
				} else if (input.type === 'checkbox') {
					if (!bag[key]) { bag[key] = []; }
					var v3 = toValue(input.value);
					if (v3 === null) { return; }
					var idx = bag[key].indexOf(v3);
					if (input.checked && idx === -1) {
						bag[key].push(v3);
					} else if (!input.checked && idx !== -1) {
						bag[key].splice(idx, 1);
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

		// Recherche par adresse (rayon géré en admin, pas d'UI utilisateur).
		var searchInput = wrapper.querySelector('.gmaps-aa-search');
		var clearBtn = wrapper.querySelector('.gmaps-aa-search-clear');

		function clearSearch() {
			searchCenter = null;
			if (searchInput) { searchInput.value = ''; }
		}

		function resetAllFilters() {
			currentFilters.tax = {};
			currentFilters.acf = {};
			wrapper.querySelectorAll('.gmaps-aa-filter-input').forEach(function (input) {
				if (input.type === 'checkbox') {
					input.checked = false;
				} else if (input.type === 'radio') {
					input.checked = (input.value === '');
				} else if (input.tagName === 'SELECT') {
					input.value = '';
				}
			});
			clearSearch();
			applyFilters({ skipFitBounds: true });
			// Retour à la vue par défaut.
			map.setCenter(config.center);
			map.setZoom(config.zoom);
		}

		if (searchInput) {
			function applySearchLocation(latLng) {
				searchCenter = { lat: latLng.lat(), lng: latLng.lng() };
				map.panTo(searchCenter);
				if (config.zoomSearch) {
					map.setZoom(config.zoomSearch);
				}
				// Pas de fitBounds après recherche : on respecte config.zoomSearch.
				applyFilters({ skipFitBounds: true });
			}

			// Autocomplétion Google Places (lib 'places' chargée via l'URL du loader).
			var autocomplete = null;
			if (google.maps.places && google.maps.places.Autocomplete) {
				autocomplete = new google.maps.places.Autocomplete(searchInput, {
					types: ['geocode'],
					fields: ['geometry', 'formatted_address']
				});
				autocomplete.addListener('place_changed', function () {
					var place = autocomplete.getPlace();
					if (place && place.geometry && place.geometry.location) {
						applySearchLocation(place.geometry.location);
					}
				});
				// Empêche la soumission de formulaire à la touche Entrée dans certains thèmes.
				searchInput.addEventListener('keydown', function (e) {
					if (e.key === 'Enter') { e.preventDefault(); }
				});
			}

			// Fallback geocode si le champ est vidé (ou si Autocomplete n'est pas dispo).
			var geocoder = new google.maps.Geocoder();
			var debounceTimer = null;

			searchInput.addEventListener('input', function () {
				if (searchInput.value.trim() === '') {
					clearSearch();
					applyFilters();
					return;
				}
				if (autocomplete) { return; }
				clearTimeout(debounceTimer);
				debounceTimer = setTimeout(function () {
					geocoder.geocode({ address: searchInput.value.trim() }, function (results, status) {
						if (status === 'OK' && results && results[0]) {
							applySearchLocation(results[0].geometry.location);
						}
					});
				}, 400);
			});
		}

		if (clearBtn) {
			clearBtn.addEventListener('click', resetAllFilters);
		}

		// Toggle mobile : repli/dépli du bloc de filtres.
		var filtersBlock = wrapper.querySelector('.gmaps-aa-filters');
		var filtersToggle = wrapper.querySelector('.gmaps-aa-filters-toggle');
		if (filtersBlock && filtersToggle) {
			filtersToggle.addEventListener('click', function () {
				var isOpen = filtersBlock.classList.toggle('is-open');
				filtersToggle.setAttribute('aria-expanded', String(isOpen));
			});
		}

		// Rendu initial.
		applyFilters();

		instances.push({ wrapper: wrapper, map: map, markers: markers });
	}
})();
