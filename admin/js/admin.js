(function () {
	'use strict';

	var pickerMap = null;
	var pickerMarker = null;

	function currentPostType() {
		var sel = document.getElementById('mrz_maps_exp_source_pt');
		return sel ? sel.value : '';
	}

	function updateTaxonomyVisibility() {
		var pt = currentPostType();
		var rows = document.querySelectorAll('.mrz-maps-exp-taxo-row');
		rows.forEach(function (row) {
			var types = (row.getAttribute('data-object-types') || '').split(',');
			var visible = types.indexOf(pt) !== -1;
			row.classList.toggle('is-hidden', !visible);
			if (!visible) {
				var cb = row.querySelector('input[type="checkbox"]');
				if (cb) { cb.checked = false; }
			}
		});
	}

	function initPicker() {
		var el = document.getElementById('mrz_maps_exp_picker');
		if (!el || typeof google === 'undefined' || !google.maps) { return; }

		var latEl = document.getElementById('mrz_maps_exp_center_lat');
		var lngEl = document.getElementById('mrz_maps_exp_center_lng');
		var zoomEl = document.getElementById('mrz_maps_exp_zoom');

		var lat = parseFloat(latEl.value) || 46.603354;
		var lng = parseFloat(lngEl.value) || 1.888334;
		var zoom = parseInt(zoomEl.value, 10) || 6;

		pickerMap = new google.maps.Map(el, {
			center: { lat: lat, lng: lng },
			zoom: zoom,
			streetViewControl: false,
			mapTypeControl: false,
			fullscreenControl: false
		});
		pickerMarker = new google.maps.Marker({
			position: { lat: lat, lng: lng },
			map: pickerMap,
			draggable: true
		});

		pickerMap.addListener('click', function (ev) {
			setLatLng(ev.latLng.lat(), ev.latLng.lng());
		});
		pickerMarker.addListener('dragend', function (ev) {
			setLatLng(ev.latLng.lat(), ev.latLng.lng());
		});
		pickerMap.addListener('zoom_changed', function () {
			zoomEl.value = pickerMap.getZoom();
		});
	}

	function setLatLng(lat, lng) {
		var latEl = document.getElementById('mrz_maps_exp_center_lat');
		var lngEl = document.getElementById('mrz_maps_exp_center_lng');
		latEl.value = lat.toFixed(6);
		lngEl.value = lng.toFixed(6);
		if (pickerMarker) {
			pickerMarker.setPosition({ lat: lat, lng: lng });
		}
	}

	// Callback global déclenché par le loader Google Maps.
	window.mrzMapsExpAdminBoot = initPicker;

	// Le sélecteur OU/ET n'a de sens qu'en mode « Cases à cocher ». On le masque
	// pour les autres modes, en ligne taxonomie et en ligne filtre ACF.
	function wireLogicToggle(row, modeSelector, logicElementResolver) {
		var modeSel = row.querySelector(modeSelector);
		if (!modeSel) { return; }
		var target = logicElementResolver(row);
		if (!target) { return; }
		function update() {
			target.style.display = modeSel.value === 'checkbox' ? '' : 'none';
		}
		modeSel.addEventListener('change', update);
		update();
	}

	function initAcfRepeater() {
		var wrap = document.querySelector('.mrz-maps-exp-acf-filters');
		var tpl = document.getElementById('mrz-maps-exp-acf-row-template');
		var addBtn = document.querySelector('.mrz-maps-exp-acf-add');
		if (!wrap || !tpl || !addBtn) { return; }

		function bindRow(row) {
			var removeBtn = row.querySelector('.mrz-maps-exp-acf-remove');
			if (removeBtn) {
				removeBtn.addEventListener('click', function (e) {
					e.preventDefault();
					row.remove();
				});
			}
			// Toggle du sélecteur OU/ET selon le mode.
			wireLogicToggle(
				row,
				'select[name*="[mode]"]',
				function (r) {
					var logicSel = r.querySelector('select[name*="[logic]"]');
					return logicSel ? logicSel.closest('.mrz-maps-exp-acf-col') : null;
				}
			);
		}

		wrap.querySelectorAll('.mrz-maps-exp-acf-row').forEach(bindRow);

		addBtn.addEventListener('click', function (e) {
			e.preventDefault();
			var nextIndex = parseInt(wrap.getAttribute('data-next-index') || '0', 10);
			var html = tpl.innerHTML.replace(/__INDEX__/g, String(nextIndex));
			var temp = document.createElement('div');
			temp.innerHTML = html.trim();
			var newRow = temp.firstChild;
			wrap.appendChild(newRow);
			bindRow(newRow);
			wrap.setAttribute('data-next-index', String(nextIndex + 1));
		});
	}

	function initTaxoLogicToggle() {
		document.querySelectorAll('.mrz-maps-exp-taxo-row').forEach(function (row) {
			wireLogicToggle(
				row,
				'.mrz-maps-exp-taxo-mode',
				function (r) {
					var logicSel = r.querySelector('.mrz-maps-exp-taxo-logic');
					return logicSel ? logicSel.closest('.mrz-maps-exp-taxo-col') : null;
				}
			);
		});
	}

	// Media picker générique : fonctionne sur tout élément .mrz-maps-exp-media-picker
	// contenant .mrz-maps-exp-media-id, .mrz-maps-exp-media-url, .mrz-maps-exp-media-choose,
	// .mrz-maps-exp-media-clear et .mrz-maps-exp-media-preview.
	function bindMediaPicker(container) {
		if (container.__mrzMapsExpMediaBound) { return; }
		container.__mrzMapsExpMediaBound = true;

		var idInput = container.querySelector('.mrz-maps-exp-media-id');
		var urlInput = container.querySelector('.mrz-maps-exp-media-url');
		var choose = container.querySelector('.mrz-maps-exp-media-choose');
		var clear = container.querySelector('.mrz-maps-exp-media-clear');
		var preview = container.querySelector('.mrz-maps-exp-media-preview');

		function renderPreview(url) {
			if (!preview) { return; }
			preview.innerHTML = '';
			if (!url) { return; }
			var img = document.createElement('img');
			img.alt = '';
			img.style.maxWidth = '60px';
			img.style.height = 'auto';
			img.src = url;
			preview.appendChild(img);
		}

		var frame = null;
		if (choose) {
			choose.addEventListener('click', function (e) {
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: 'Choisir une image',
					multiple: false,
					library: { type: 'image' },
					button: { text: 'Utiliser cette image' }
				});
				frame.on('select', function () {
					var a = frame.state().get('selection').first().toJSON();
					if (idInput) { idInput.value = a.id; }
					if (urlInput) { urlInput.value = a.url; }
					renderPreview(a.url);
				});
				frame.open();
			});
		}
		if (clear) {
			clear.addEventListener('click', function (e) {
				e.preventDefault();
				if (idInput) { idInput.value = ''; }
				if (urlInput) { urlInput.value = ''; }
				renderPreview('');
			});
		}
	}

	function initMediaPickers(root) {
		(root || document).querySelectorAll('.mrz-maps-exp-media-picker').forEach(bindMediaPicker);
	}

	// Color picker (WP Color Picker).
	function initColorPicker() {
		if (typeof jQuery === 'undefined' || !jQuery.fn.wpColorPicker) { return; }
		jQuery('.mrz-maps-exp-color-picker').wpColorPicker();
	}

	// Chargement AJAX des termes de la taxonomie choisie + reconstruction des lignes.
	function initPrimaryTaxonomy() {
		var sel = document.getElementById('mrz_maps_exp_primary_taxonomy');
		var container = document.getElementById('mrz_maps_exp_term_markers');
		var tpl = document.getElementById('mrz-maps-exp-term-row-template');
		if (!sel || !container || !tpl) { return; }

		var ajaxUrl = container.getAttribute('data-ajax-url');
		var nonce = container.getAttribute('data-nonce');

		function buildRow(term) {
			var html = tpl.innerHTML
				.replace(/__TERM_ID__/g, String(term.id))
				.replace(/__TERM_NAME__/g, term.name
					.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'))
				.replace(/__ICON_URL__/g, (term.icon_url || '')
					.replace(/"/g, '&quot;'))
				.replace(/__ICON_ID__/g, String(term.icon_id || ''));
			var temp = document.createElement('div');
			temp.innerHTML = html.trim();
			var row = temp.firstChild;
			if (term.icon_url) {
				var preview = row.querySelector('.mrz-maps-exp-media-preview');
				if (preview) {
					var img = document.createElement('img');
					img.alt = '';
					img.style.maxWidth = '60px';
					img.style.height = 'auto';
					img.src = term.icon_url;
					preview.appendChild(img);
				}
			}
			return row;
		}

		sel.addEventListener('change', function () {
			var tax = sel.value;
			container.innerHTML = '<p>Chargement…</p>';
			if (!tax) {
				container.innerHTML = '<p class="description">Sélectionnez d\'abord une taxonomie ci-dessus.</p>';
				return;
			}
			var form = new FormData();
			form.append('action', 'mrz_maps_exp_fetch_terms');
			form.append('nonce', nonce);
			form.append('taxonomy', tax);

			fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: form })
				.then(function (r) { return r.json(); })
				.then(function (json) {
					container.innerHTML = '';
					if (!json || !json.success) {
						container.innerHTML = '<p class="description">Erreur de chargement.</p>';
						return;
					}
					var terms = json.data || [];
					if (!terms.length) {
						container.innerHTML = '<p class="description">Aucun terme disponible dans cette taxonomie.</p>';
						return;
					}
					terms.forEach(function (t) {
						var row = buildRow(t);
						container.appendChild(row);
						initMediaPickers(row);
					});
				})
				.catch(function () {
					container.innerHTML = '<p class="description">Erreur réseau.</p>';
				});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		updateTaxonomyVisibility();
		var sel = document.getElementById('mrz_maps_exp_source_pt');
		if (sel) {
			sel.addEventListener('change', updateTaxonomyVisibility);
		}
		initAcfRepeater();
		initTaxoLogicToggle();
		initMediaPickers();
		initColorPicker();
		initPrimaryTaxonomy();
		if (window.mrzMapsExpAdmin && !window.mrzMapsExpAdmin.hasApiKey) {
			var el = document.getElementById('mrz_maps_exp_picker');
			if (el) {
				el.innerHTML = '<p style="padding:10px;color:#666;">' +
					'Clé Google Maps API manquante — la mini-carte est désactivée. Ajoutez la clé via le filtre mrz_maps_exp_api_key.' +
					'</p>';
			}
		}
	});
})();
