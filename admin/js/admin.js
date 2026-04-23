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

	document.addEventListener('DOMContentLoaded', function () {
		updateTaxonomyVisibility();
		var sel = document.getElementById('gmaps_aa_source_pt');
		if (sel) {
			sel.addEventListener('change', updateTaxonomyVisibility);
		}
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
