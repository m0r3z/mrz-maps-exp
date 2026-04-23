# gmaps-aa

Plugin WordPress générique de cartographie Google Maps basé sur les champs ACF `google_map`.

## Prérequis

- WordPress 6.3 ou supérieur (utilisation de `strategy: async` pour les scripts)
- PHP 7.4 ou supérieur
- Advanced Custom Fields (Pro recommandé)

## Fonctionnalités

- Multi-cartes indépendantes via un Custom Post Type
- Source configurable : n'importe quel post type avec un champ ACF de type `google_map`
- Filtres front par taxonomie (dropdown, radio, cases à cocher)
- Dépilement des marqueurs superposés (OverlappingMarkerSpiderfier, bundlé localement)
- Skin Snazzy Maps (coller le JSON depuis snazzymaps.com)
- Icône de marker personnalisable par terme de taxonomie
- Templates HTML d'infobulle et de liste avec placeholders et conditionnels
- Recherche par adresse avec rayon en km
- Shortcode `[gmaps_aa id="X"]` avec options de filtre forcé

## Clé Google Maps API

**Le plugin ne stocke pas la clé en base** : elle doit être fournie par le thème. Trois méthodes, essayées dans cet ordre :

### 1. Filtre (recommandé)

```php
// functions.php
add_filter( 'gmaps_aa_api_key', function () {
    return 'VOTRE_CLE_API';
} );
```

### 2. Constante

```php
// wp-config.php ou functions.php
define( 'GMAPS_AA_API_KEY', 'VOTRE_CLE_API' );
```

### 3. Réglage ACF

Si vous avez déjà configuré ACF pour les champs `google_map`, gmaps-aa réutilise automatiquement `acf_get_setting('google_api_key')`.

### Restrictions recommandées

Dans la Google Cloud Console, restreignez votre clé publique par **HTTP Referrer** à votre domaine (`*.monsite.com/*`) pour éviter toute utilisation frauduleuse. Une seconde clé non restreinte peut être utilisée côté admin si besoin via le même filtre en contexte admin.

## Utilisation

1. Créez une carte depuis le menu **gmaps-aa**.
2. Configurez les métaboxes (source, affichage, templates, style, recherche).
3. Collez le shortcode affiché dans la colonne « Shortcode » sur la page souhaitée.

```text
[gmaps_aa id="123"]
[gmaps_aa id="123" filter_taxonomy="category" filter_term="42" hide_forced_filter="true"]
```

## Placeholders disponibles dans les templates

| Placeholder | Sortie |
|---|---|
| `{post_title}` | Titre du post (échappé) |
| `{post_url}` | Permalien |
| `{post_excerpt}` | Extrait |
| `{post_thumbnail}` | Balise `<img>` de la thumbnail |
| `{post_thumbnail_url}` | URL de la thumbnail |
| `{%nom_champ_acf%}` | Valeur d'un champ ACF (échappement adapté au type) |
| `{taxonomy:slug}` | Termes séparés par virgule |
| `{taxonomy:slug:first}` | Premier terme uniquement |

### Conditionnels

```text
{#if %annu_fonction%}<div>{%annu_fonction%}</div>{/if}
```

Le bloc est affiché uniquement si la valeur est non vide.

## Hooks exposés

- `gmaps_aa_api_key` (filter)
- `gmaps_aa_capability` (filter, défaut `manage_options`)
- `gmaps_aa_cache_ttl` (filter, défaut 300 s)
- `gmaps_aa_template_value` (filter)
- `gmaps_aa_template_kses_allowed` (filter)
- `gmaps_aa_skip_gmaps_enqueue` (filter, utile en coexistence avec d'autres loaders Google Maps)

## Compatibilité

- Coexistence testée avec le thème Salient (`salient-core` / `nectar_gmap`) : les deux systèmes chargent leurs propres instances sans conflit.

## Licence

GPLv2 or later.
