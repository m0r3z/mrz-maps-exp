(function () {
	'use strict';

	var pickerMap = null;
	var pickerMarker = null;

	function currentPostType() {
		var sel = document.getElementById('gmaps_aa_source_pt');
		return sel ? sel.value : '';
	}

	function updateTaxonomyVisibility() {
		var pt = currentPostType();
		var rows = document.querySelectorAll('.gmaps-aa-taxo-row');
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
		var el = document.getElementById('gmaps_aa_picker');
		if (!el || typeof google === 'undefined' || !google.maps) { return; }

		var latEl = document.getElementById('gmaps_aa_center_lat');
		var lngEl = document.getElementById('gmaps_aa_center_lng');
		var zoomEl = document.getElementById('gmaps_aa_zoom');

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
		var latEl = document.getElementById('gmaps_aa_center_lat');
		var lngEl = document.getElementById('gmaps_aa_center_lng');
		latEl.value = lat.toFixed(6);
		lngEl.value = lng.toFixed(6);
		if (pickerMarker) {
			pickerMarker.setPosition({ lat: lat, lng: lng });
		}
	}

	// Callback global déclenché par le loader Google Maps.
	window.gmapsAAAdminBoot = initPicker;

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
		var wrap = document.querySelector('.gmaps-aa-acf-filters');
		var tpl = document.getElementById('gmaps-aa-acf-row-template');
		var addBtn = document.querySelector('.gmaps-aa-acf-add');
		if (!wrap || !tpl || !addBtn) { return; }

		function bindRow(row) {
			var removeBtn = row.querySelector('.gmaps-aa-acf-remove');
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
					return logicSel ? logicSel.closest('.gmaps-aa-acf-col') : null;
				}
			);
		}

		wrap.querySelectorAll('.gmaps-aa-acf-row').forEach(bindRow);

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
		document.querySelectorAll('.gmaps-aa-taxo-row').forEach(function (row) {
			wireLogicToggle(
				row,
				'.gmaps-aa-taxo-mode',
				function (r) { return r.querySelector('.gmaps-aa-taxo-logic'); }
			);
		});
	}

	// Media picker générique : fonctionne sur tout élément .gmaps-aa-media-picker
	// contenant .gmaps-aa-media-id, .gmaps-aa-media-url, .gmaps-aa-media-choose,
	// .gmaps-aa-media-clear et .gmaps-aa-media-preview.
	function bindMediaPicker(container) {
		if (container.__gmapsAAMediaBound) { return; }
		container.__gmapsAAMediaBound = true;

		var idInput = container.querySelector('.gmaps-aa-media-id');
		var urlInput = container.querySelector('.gmaps-aa-media-url');
		var choose = container.querySelector('.gmaps-aa-media-choose');
		var clear = container.querySelector('.gmaps-aa-media-clear');
		var preview = container.querySelector('.gmaps-aa-media-preview');

		function renderPreview(url) {
			if (!preview) { return; }
			preview.innerHTML = url
				? '<img src="' + url + '" alt="" style="max-width:60px;height:auto;" />'
				: '';
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
		(root || document).querySelectorAll('.gmaps-aa-media-picker').forEach(bindMediaPicker);
	}

	// Color picker (WP Color Picker).
	function initColorPicker() {
		if (typeof jQuery === 'undefined' || !jQuery.fn.wpColorPicker) { return; }
		jQuery('.gmaps-aa-color-picker').wpColorPicker();
	}

	// Chargement AJAX des termes de la taxonomie choisie + reconstruction des lignes.
	function initPrimaryTaxonomy() {
		var sel = document.getElementById('gmaps_aa_primary_taxonomy');
		var container = document.getElementById('gmaps_aa_term_markers');
		var tpl = document.getElementById('gmaps-aa-term-row-template');
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
				var preview = row.querySelector('.gmaps-aa-media-preview');
				if (preview) {
					preview.innerHTML = '<img src="' + term.icon_url + '" alt="" style="max-width:60px;height:auto;" />';
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
			form.append('action', 'gmaps_aa_fetch_terms');
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
		var sel = document.getElementById('gmaps_aa_source_pt');
		if (sel) {
			sel.addEventListener('change', updateTaxonomyVisibility);
		}
		initAcfRepeater();
		initTaxoLogicToggle();
		initMediaPickers();
		initColorPicker();
		initPrimaryTaxonomy();
		if (window.gmapsAAAdmin && !window.gmapsAAAdmin.hasApiKey) {
			var el = document.getElementById('gmaps_aa_picker');
			if (el) {
				el.innerHTML = '<p style="padding:10px;color:#666;">' +
					'Clé Google Maps API manquante — la mini-carte est désactivée. Ajoutez la clé via le filtre gmaps_aa_api_key.' +
					'</p>';
			}
		}
	});
})();
