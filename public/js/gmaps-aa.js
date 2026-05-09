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
			this.container.setAttribute('role', 'dialog');
			this.container.setAttribute('aria-modal', 'true');
			this.container.setAttribute('tabindex', '-1');

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

			// Navigation clavier : Escape ferme, Tab reste piégé dans la popup.
			this.container.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') {
					e.stopPropagation();
					self.close();
					return;
				}
				if (e.key !== 'Tab') { return; }
				var focusables = self.container.querySelectorAll(
					'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
				);
				if (!focusables.length) { return; }
				var first = focusables[0];
				var last = focusables[focusables.length - 1];
				if (e.shiftKey && document.activeElement === first) {
					last.focus();
					e.preventDefault();
				} else if (!e.shiftKey && document.activeElement === last) {
					first.focus();
					e.preventDefault();
				}
			});

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
			// navigateur calculer sa taille avant de corriger le débordement
			// et de transférer le focus pour l'accessibilité clavier.
			requestAnimationFrame(function () {
				requestAnimationFrame(function () {
					ensurePopupVisible();
					if (popup.closeBtn) {
						popup.closeBtn.focus();
					}
				});
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

		// Escape global : ferme la popup même si le focus est sorti.
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && popup.getMap()) {
				popup.close();
			}
		});

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

			writeUrlFilters();
		}

		// Synchronisation des filtres avec l'URL (opt-in via config.urlFilters).
		// Format : ?gm_<map_id>_tax_<slug>=12,34&gm_<map_id>_acf_<field>=foo,bar
		function urlPrefix() {
			return 'gm_' + (data.id || 0) + '_';
		}

		function applyUrlFilters() {
			if (!config.urlFilters) { return; }
			var params = new URLSearchParams(window.location.search);
			var prefix = urlPrefix();
			var taxPrefix = prefix + 'tax_';
			var acfPrefix = prefix + 'acf_';

			params.forEach(function (value, key) {
				if (key.indexOf(taxPrefix) === 0) {
					var slug = key.slice(taxPrefix.length);
					var ids = value.split(',')
						.map(function (v) { return parseInt(v, 10); })
						.filter(function (n) { return !isNaN(n); });
					if (ids.length) { currentFilters.tax[slug] = ids; }
				} else if (key.indexOf(acfPrefix) === 0) {
					var field = key.slice(acfPrefix.length);
					var vals = value.split(',').filter(function (v) { return v !== ''; });
					if (vals.length) { currentFilters.acf[field] = vals; }
				}
			});

			// Synchronise les inputs avec l'état lu depuis l'URL.
			wrapper.querySelectorAll('.gmaps-aa-filter-input').forEach(function (input) {
				var type = input.getAttribute('data-filter-type') || 'tax';
				var key = type === 'acf'
					? input.getAttribute('data-field')
					: input.getAttribute('data-taxonomy');
				if (!key) { return; }
				var arr = currentFilters[type][key] || [];
				if (input.tagName === 'SELECT') {
					input.value = arr.length ? String(arr[0]) : '';
				} else if (input.type === 'radio') {
					input.checked = arr.length
						? String(arr[0]) === input.value
						: input.value === '';
				} else if (input.type === 'checkbox') {
					input.checked = arr.some(function (v) { return String(v) === input.value; });
				}
			});
		}

		function writeUrlFilters() {
			if (!config.urlFilters) { return; }
			var params = new URLSearchParams(window.location.search);
			var prefix = urlPrefix();

			// Supprime tous les params de cette carte avant de réécrire.
			Array.from(params.keys()).forEach(function (key) {
				if (key.indexOf(prefix) === 0) { params.delete(key); }
			});

			Object.keys(currentFilters.tax).forEach(function (slug) {
				var arr = currentFilters.tax[slug];
				if (arr && arr.length) {
					params.set(prefix + 'tax_' + slug, arr.join(','));
				}
			});
			Object.keys(currentFilters.acf).forEach(function (field) {
				var arr = currentFilters.acf[field];
				if (arr && arr.length) {
					params.set(prefix + 'acf_' + field, arr.join(','));
				}
			});

			var qs = params.toString();
			var newUrl = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
			try {
				history.replaceState(history.state, '', newUrl);
			} catch (e) {
				// Si replaceState échoue (très rare, ex: file://), on fail silencieusement.
			}
		}

		// Lit l'URL et coche les inputs correspondants AVANT la boucle d'init —
		// la boucle ci-dessous appelle handler() pour les inputs déjà cochés
		// (forçage shortcode ou sync URL), donc on doit lire l'URL avant.
		applyUrlFilters();

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

		// Recherche : dropdown unifiée (fiches locales + suggestions d'adresses Google).
		var searchInput = wrapper.querySelector('.gmaps-aa-search');
		var searchDropdown = wrapper.querySelector('.gmaps-aa-search-dropdown');
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
			if (searchDropdown) { closeDropdown(); }
			applyFilters({ skipFitBounds: true });
			// Retour à la vue par défaut.
			map.setCenter(config.center);
			map.setZoom(config.zoom);
		}

		// Normalisation pour matching texte (minuscules + retrait des diacritiques).
		function normalize(s) {
			return String(s || '')
				.normalize('NFD')
				.replace(/[\u0300-\u036f]/g, '')
				.toLowerCase();
		}

		var dropdownItems = [];
		var activeIndex = -1;

		function closeDropdown() {
			if (!searchDropdown) { return; }
			searchDropdown.hidden = true;
			searchDropdown.replaceChildren();
			dropdownItems = [];
			activeIndex = -1;
			if (searchInput) {
				searchInput.setAttribute('aria-expanded', 'false');
				searchInput.removeAttribute('aria-activedescendant');
			}
		}

		function setActive(idx) {
			if (!searchDropdown || !dropdownItems.length) { return; }
			if (idx < 0) { idx = dropdownItems.length - 1; }
			if (idx >= dropdownItems.length) { idx = 0; }
			activeIndex = idx;
			dropdownItems.forEach(function (item, i) {
				if (i === idx) {
					item.el.classList.add('is-active');
					item.el.setAttribute('aria-selected', 'true');
					if (searchInput) {
						searchInput.setAttribute('aria-activedescendant', item.el.id);
					}
					if (item.el.scrollIntoView) {
						item.el.scrollIntoView({ block: 'nearest' });
					}
				} else {
					item.el.classList.remove('is-active');
					item.el.setAttribute('aria-selected', 'false');
				}
			});
		}

		function applySearchLocation(latLng) {
			searchCenter = { lat: latLng.lat(), lng: latLng.lng() };
			map.panTo(searchCenter);
			if (config.zoomSearch) {
				map.setZoom(config.zoomSearch);
			}
			// Pas de fitBounds après recherche : on respecte config.zoomSearch.
			applyFilters({ skipFitBounds: true });
		}

		if (searchInput && searchDropdown) {
			var searchCfg = config.search || {};
			var localMatchEnabled = !!searchCfg.localMatch;
			var sectionLocalLabel = searchCfg.sectionLocal || 'Fiches';
			var sectionAddressLabel = searchCfg.sectionAddress || 'Adresses';
			var dropdownId = searchDropdown.id || '';

			// Index local pour le matching titre des fiches.
			var localIndex = localMatchEnabled
				? data.points.map(function (p) {
					return { id: p.id, title: p.title || '', _norm: normalize(p.title || '') };
				}).filter(function (p) { return p._norm !== ''; })
				: [];

			var hasPlaces = !!(google.maps.places && google.maps.places.AutocompleteService);
			var autocompleteService = hasPlaces ? new google.maps.places.AutocompleteService() : null;
			var placesService = hasPlaces ? new google.maps.places.PlacesService(map) : null;
			var sessionToken = (hasPlaces && google.maps.places.AutocompleteSessionToken)
				? new google.maps.places.AutocompleteSessionToken()
				: null;

			function selectLocalPoint(pointId) {
				var marker = markers.find(function (m) { return m.__point.id === pointId; });
				if (!marker) { return; }
				var p = marker.__point;
				if (searchInput) { searchInput.value = p.title || ''; }
				closeDropdown();
				map.panTo({ lat: p.lat, lng: p.lng });
				if (config.zoomSearch) {
					map.setZoom(config.zoomSearch);
				}
				openPopupOn(marker, p);
			}

			function selectGoogleSuggestion(prediction) {
				if (searchInput) { searchInput.value = prediction.description || ''; }
				closeDropdown();
				if (!placesService) { return; }
				placesService.getDetails(
					{ placeId: prediction.place_id, fields: ['geometry'], sessionToken: sessionToken },
					function (place, status) {
						if (status === google.maps.places.PlacesServiceStatus.OK && place && place.geometry && place.geometry.location) {
							applySearchLocation(place.geometry.location);
							// Nouvelle session pour la prochaine recherche.
							sessionToken = google.maps.places.AutocompleteSessionToken
								? new google.maps.places.AutocompleteSessionToken()
								: null;
						}
					}
				);
			}

			function buildSection(labelText) {
				var li = document.createElement('li');
				li.className = 'gmaps-aa-search-section';
				li.setAttribute('role', 'presentation');
				li.textContent = labelText;
				return li;
			}

			function buildOption(text, idx, onSelect) {
				var li = document.createElement('li');
				li.className = 'gmaps-aa-search-option';
				li.id = dropdownId + '-opt-' + idx;
				li.setAttribute('role', 'option');
				li.setAttribute('aria-selected', 'false');
				li.textContent = text;
				li.addEventListener('mousedown', function (e) {
					// mousedown plutôt que click pour qu'il soit traité avant le blur.
					e.preventDefault();
					onSelect();
				});
				li.addEventListener('mousemove', function () {
					setActive(dropdownItems.findIndex(function (it) { return it.el === li; }));
				});
				return li;
			}

			function renderDropdown(query, localResults, googleResults) {
				searchDropdown.replaceChildren();
				dropdownItems = [];
				activeIndex = -1;

				if (!localResults.length && !googleResults.length) {
					searchDropdown.hidden = true;
					searchInput.setAttribute('aria-expanded', 'false');
					searchInput.removeAttribute('aria-activedescendant');
					return;
				}

				var idx = 0;
				if (localResults.length) {
					searchDropdown.appendChild(buildSection(sectionLocalLabel));
					localResults.forEach(function (entry) {
						var optionIdx = idx;
						var li = buildOption(entry.title, optionIdx, function () {
							selectLocalPoint(entry.id);
						});
						searchDropdown.appendChild(li);
						dropdownItems.push({
							el: li,
							select: function () { selectLocalPoint(entry.id); }
						});
						idx += 1;
					});
				}
				if (googleResults.length) {
					searchDropdown.appendChild(buildSection(sectionAddressLabel));
					googleResults.forEach(function (prediction) {
						var optionIdx = idx;
						var li = buildOption(prediction.description, optionIdx, function () {
							selectGoogleSuggestion(prediction);
						});
						searchDropdown.appendChild(li);
						dropdownItems.push({
							el: li,
							select: function () { selectGoogleSuggestion(prediction); }
						});
						idx += 1;
					});
				}

				searchDropdown.hidden = false;
				searchInput.setAttribute('aria-expanded', 'true');
			}

			var debounceTimer = null;
			var requestSeq = 0;

			function runSearch(query) {
				var seq = ++requestSeq;
				var localResults = [];
				if (localMatchEnabled && localIndex.length) {
					var qn = normalize(query);
					if (qn !== '') {
						for (var i = 0; i < localIndex.length && localResults.length < 5; i += 1) {
							if (localIndex[i]._norm.indexOf(qn) !== -1) {
								localResults.push(localIndex[i]);
							}
						}
					}
				}

				if (!autocompleteService) {
					if (seq === requestSeq) {
						renderDropdown(query, localResults, []);
					}
					return;
				}

				autocompleteService.getPlacePredictions(
					{ input: query, types: ['geocode'], sessionToken: sessionToken },
					function (predictions, status) {
						if (seq !== requestSeq) { return; } // Réponse obsolète.
						var googleResults = (status === google.maps.places.PlacesServiceStatus.OK && predictions)
							? predictions
							: [];
						renderDropdown(query, localResults, googleResults);
					}
				);
			}

			searchInput.addEventListener('input', function () {
				var q = searchInput.value.trim();
				if (q === '') {
					clearTimeout(debounceTimer);
					closeDropdown();
					clearSearch();
					applyFilters();
					return;
				}
				clearTimeout(debounceTimer);
				debounceTimer = setTimeout(function () { runSearch(q); }, 200);
			});

			searchInput.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowDown') {
					if (dropdownItems.length) {
						e.preventDefault();
						setActive(activeIndex < 0 ? 0 : activeIndex + 1);
					}
				} else if (e.key === 'ArrowUp') {
					if (dropdownItems.length) {
						e.preventDefault();
						setActive(activeIndex < 0 ? dropdownItems.length - 1 : activeIndex - 1);
					}
				} else if (e.key === 'Enter') {
					if (activeIndex >= 0 && dropdownItems[activeIndex]) {
						e.preventDefault();
						dropdownItems[activeIndex].select();
					} else {
						// Évite la soumission de formulaire dans certains thèmes.
						e.preventDefault();
					}
				} else if (e.key === 'Escape') {
					if (!searchDropdown.hidden) {
						e.stopPropagation();
						closeDropdown();
					}
				} else if (e.key === 'Tab') {
					closeDropdown();
				}
			});

			searchInput.addEventListener('blur', function () {
				// setTimeout pour laisser un éventuel mousedown se propager.
				setTimeout(closeDropdown, 0);
			});

			searchInput.addEventListener('focus', function () {
				if (searchInput.value.trim() !== '' && dropdownItems.length) {
					searchDropdown.hidden = false;
					searchInput.setAttribute('aria-expanded', 'true');
				}
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
