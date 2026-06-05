=== MRZ Maps Exp ===
Contributors: mrzxp
Tags: google maps, map, acf, taxonomy, custom post type
Requires at least: 6.3
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Display custom posts on a Google Map using ACF-based coordinates, with taxonomy and ACF filters, hybrid search and customizable markers.

== Description ==

MRZ Maps Exp is a generic, minimalist plugin to display any custom post type on a Google Map. Coordinates are read from an ACF Google Map field declared on each post. Everything is configured from the WordPress admin — no theme code required: source post type, filters, pagination, HTML templates, custom markers, responsive layout.

= Features =

* Multiple maps via a dedicated Custom Post Type — each map has its own configuration.
* Filters by taxonomy and by ACF field, in dropdown / radio / checkbox mode, with OR / AND logic.
* Hybrid search in a single field: Google Places address suggestions **and** posts whose title matches the input.
* Optional URL filter synchronization — share a pre-filtered link.
* Customizable markers: default icon, or per-term taxonomy icon.
* 100% custom tooltip via OverlayView (not the native InfoWindow), fully overridable in CSS.
* Configurable HTML templates for the tooltip and the list, with ACF / taxonomy placeholders.
* Front pagination, automatic fit bounds after filtering, recenter on the current post in single template.
* Overlapping marker spiderfier (OverlappingMarkerSpiderfier).
* Snazzy Maps: paste a JSON to style the map.
* Responsive layout — filters above, left or right; detachable full-width search field; collapsible filters on mobile.
* Shortcode with optional forced filter: `[mrz_maps_exp id="X" filter_taxonomy="genre" filter_term="42"]`.
* Translation-ready (text-domain `mrz-maps-exp`, .pot file provided).

= Requirements =

* WordPress 6.3 or later, PHP 7.4 or later.
* Advanced Custom Fields (Pro recommended) — for the Google Map field.
* A Google Maps API key with the "Maps JavaScript", "Places" and "Geocoding" APIs enabled. The key must be declared in the theme's `functions.php` via the `mrz_maps_exp_api_key` filter or the `MRZ_MAPS_EXP_API_KEY` constant (the plugin deliberately does not expose an admin field for the key — it is a secret that has no place in the database).

= Privacy / external calls =

MRZ Maps Exp loads the Google Maps JavaScript library in the end user's browser, like any mapping plugin. No data is sent to Morez.co or any third-party service; no telemetry is collected. The Google Maps API key supplied by the site administrator is exposed on the client side (required to call Google's JS API) — it is recommended to restrict it by HTTP referrer in the Google Cloud Console.

= Source code and contributions =

Development happens publicly on GitHub: https://github.com/m0r3z/mrz-maps-exp
Issues, pull requests and forks welcome.

== Installation ==

1. Install and activate Advanced Custom Fields (Pro recommended).
2. Declare your Google Maps API key in the theme's `functions.php`:
   `add_filter( 'mrz_maps_exp_api_key', function () { return 'YOUR_API_KEY'; } );`
3. Install and activate MRZ Maps Exp from the WordPress "Plugins" screen, or upload the zip.
4. Go to the **MRZ Maps** menu → **Add a map**, then configure the source post type, filters, templates, etc.
5. Insert the generated shortcode on any page: `[mrz_maps_exp id="X"]`.

== Frequently Asked Questions ==

= Is ACF really required? =

Yes. MRZ Maps Exp relies on ACF Google Map fields to retrieve each post's latitude, longitude and address. The plugin is built to integrate with ACF rather than duplicate a custom coordinates system. The free version of ACF is enough; ACF Pro is only required if you use Pro features such as the repeater field.

= Why is the Google Maps API key not configurable from the admin? =

This is a deliberate choice. An API key is a secret that has no place in the database: it could be exfiltrated via a DB export, an unencrypted backup, or a compromised admin account. The key must be declared in the theme code via the `mrz_maps_exp_api_key` filter or the `MRZ_MAPS_EXP_API_KEY` constant. This is also the recommended pattern for key rotation.

= Is the plugin compatible with the Salient theme or with other mapping plugins? =

Yes. MRZ Maps Exp detects whether Google Maps JS is already loaded by another plugin (e.g. Salient via `salient-core` / `nectar_gmap`) and does not re-enqueue it in that case. A `mrz_maps_exp_skip_gmaps_enqueue` filter lets you override the behavior if needed.

= How do I add content inside the tooltip or list items? =

In the map's "HTML templates" metabox, free-form HTML templates are configurable with placeholders: `{post_title}`, `{post_url}`, `{post_excerpt}`, `{post_thumbnail}`, `{post_thumbnail_url}`, `{%acf_field_name%}`, `{taxonomy:slug}`, `{taxonomy:slug:first}`. Conditional blocks `{#if %field%}…{/if}` are supported so that an area is only rendered if the field is filled.

= Can I filter the map through the URL to share a pre-filtered link? =

Yes — opt-in option in the Filters metabox. When enabled, the active filters are reflected in the URL in real time (`?gm_<map_id>_tax_<slug>=12,34&gm_<map_id>_acf_<field>=value`), and a link pasted with these parameters will pre-activate the matching filters.

= Does the plugin handle a large number of markers? =

The front rendering easily handles a few thousand markers (data is injected as inline JSON and filtered on the client side). For larger volumes, consider limiting the initial dataset via a forced filter on the taxonomy or via the admin "Maximum number of posts" option.

== Screenshots ==

1. **Source data** metabox: choose the source post type, the ACF coordinates field, and an optional load limit.
2. **HTML templates** metabox: customizable templates for the tooltip and for the list items, with ACF and taxonomy placeholders.
3. **Cosmetic** metabox: default marker, size, Spiderfier toggle, and per-term taxonomy markers.
4. **Filters** metabox: configure taxonomy and ACF field filters (dropdown / radio / checkbox mode, OR / AND logic).

== External services ==

This plugin relies on the Google Maps JavaScript API to display the interactive map, geocode addresses entered in the search field, and provide place autocomplete suggestions. The Google Maps API key is supplied by the site administrator (via the `mrz_maps_exp_api_key` filter or `MRZ_MAPS_EXP_API_KEY` constant in the theme); the plugin itself does not bundle any key.

= What is sent and when =

* When a page containing the `[mrz_maps_exp]` shortcode is loaded, the visitor's browser loads the Google Maps JavaScript library from `https://maps.googleapis.com/maps/api/js`, including the `places` library. The Google Maps API key is appended as a query parameter.
* When the visitor types in the search field, each keystroke (debounced) sends the current query string to Google's Places Autocomplete service to retrieve address suggestions. If the visitor picks a suggestion, Google's Places Details service is then called to obtain the coordinates for that place.
* The visitor's IP address is transmitted to Google as part of these HTTP requests, as it is for any third-party request initiated by their browser.
* No data is sent to Google when the plugin is only installed or activated in the admin — only when a public page containing the shortcode is loaded.

The plugin does not send any data to Morez.co or to any other third-party service. No telemetry is collected.

= Provider and legal links =

* Service: Google Maps Platform (Google LLC).
* Terms of Service: https://cloud.google.com/maps-platform/terms
* Privacy Policy: https://policies.google.com/privacy

It is the responsibility of site administrators to obtain a valid Google Maps API key, accept Google's Terms of Service for the project that key belongs to, and disclose the use of Google Maps in their own site's privacy policy where required.

== Changelog ==

= 1.0.6 =
* Translated the readme description, FAQ, installation steps and requirements to English (wordpress.org guideline). The shipped code already uses English-friendly identifiers; only the documentation was localized to French.
* Made the GitHub repository public so the Plugin URI and Repository URL resolve correctly for the review team (previously 404 due to the private repo).

= 1.0.5 =
* Plugin Name shortened from "MRZ Maps Experience" to "MRZ Maps Exp" so the displayed name matches the plugin slug `mrz-maps-exp`. Resolves the `textdomain_mismatch` warning of the wordpress.org automated scan without renaming the slug. All internal identifiers (constants `MRZ_MAPS_EXP_*`, function prefix `mrz_maps_exp_*`, post_meta prefix `_mrz_maps_exp_*`, namespace `MrzMapsExp\`, CPT `mrz_maps_exp_map`, shortcode `[mrz_maps_exp]`, script/style handles, CSS classes) stay unchanged.
* `Tested up to: 7.0` (was `6.9` — outdated_tested_upto_header).

= 1.0.3 =
* Updated the readme `Contributors:` line to point at the actual wordpress.org account that owns the plugin (`mrzxp`). The GitHub repository remains `m0r3z/mrz-maps-exp` — the two accounts are intentionally distinct.

= 1.0.2 =
* Pre-submission polish for wordpress.org. No functional change.
* `uninstall.php`: rewrote the three `$wpdb->query()` calls using `$wpdb->prepare()` and `$wpdb->esc_like()` instead of hand-escaped LIKE patterns, matching the strictest Plugin Check expectations.
* `metabox-templates.php`: wrapped the inline `<code>` placeholders documentation in `wp_kses()` with an explicit allowlist instead of `echo`ing literal HTML.
* `map-wrapper.php`: minor cleanup of a `<?php echo ' / '; ?>` separator in the pagination markup (replaced with a literal character).

= 1.0.1 =
* Fixed admin label: the sidebar menu still displayed "GMaps" instead of "MRZ Maps". CPT `menu_name` corrected.
* Fixed uninstall: `uninstall.php` still targeted the old `_gmaps_aa_*` prefixes for the post_meta, term_meta and transient cleanup (escaped underscores in the SQL LIKE patterns were missed by the 1.0.0 bulk rename).
* Fixed documentation: residual mentions of the "GMaps" menu in `readme.txt` and `README.md` corrected to "MRZ Maps".

= 1.0.0 =
* First public release under the Morez.co identity. Full rebrand from the internal "GMaps-AA" codebase: new plugin slug (`mrz-maps-exp`), new text domain, new PHP namespace (`MrzMapsExp`), new constant / function / meta / CPT prefixes (`MRZ_MAPS_EXP_*`, `mrz_maps_exp_*`, `_mrz_maps_exp_*`, `mrz_maps_exp_map`). Existing sites coming from the internal "GMaps-AA" codebase must reconfigure their maps after upgrade — there is no automatic data migration.
* Compliance with the wordpress.org plugin guidelines:
  * The admin menu icon CSS is no longer printed inline via `admin_head`; it is now enqueued via `wp_register_style` + `wp_add_inline_style` on `admin_enqueue_scripts`.
  * Removed the now-unnecessary `load_plugin_textdomain()` call (since WordPress 4.6, translations of plugins hosted on wordpress.org are loaded automatically).
  * Added a dedicated `== External services ==` section in the readme documenting the use of the Google Maps JavaScript API, what data is sent, and links to Google's Terms of Service and Privacy Policy.

== Upgrade Notice ==

= 1.0.6 =
Readme description translated to English (wordpress.org requirement) and GitHub repository made public so the Plugin URI resolves. No code change.

= 1.0.5 =
Plugin Name shortened to "MRZ Maps Exp" to match the existing slug `mrz-maps-exp`. No data migration needed — all internal identifiers and data are unchanged.

= 1.0.3 =
Updated readme `Contributors:` to the actual wp.org account (`mrzxp`). Cosmetic only.

= 1.0.2 =
Pre-submission polish for wordpress.org. No functional change, safe upgrade.

= 1.0.1 =
Small post-rebrand fixes: admin menu label ("GMaps" → "MRZ Maps"), uninstall cleanup targeting the right prefixes. Recommended upgrade.

= 1.0.0 =
First public release as "MRZ Maps Exp" (Morez.co). Full rebrand from the internal "GMaps-AA" codebase — namespaces, prefixes, slug and CPT all changed. Sites previously running the internal "GMaps-AA" build must reconfigure their maps after upgrade.
