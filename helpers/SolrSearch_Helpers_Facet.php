<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_Facet
{


    /**
     * Convert $_GET into an array with exploded facets.
     *
     * The `facet` parameter is a list of `field:"value"` pairs joined by
     * ` AND `; inside the quotes, `"` and `\` are backslash-escaped.
     *
     * @return array The parsed parameters, as array($field, $value) pairs.
     */
    public static function parseFacets()
    {

        $facets = array();

        if (array_key_exists('facet', $_GET) && is_string($_GET['facet'])) {

            // Extract the field/value facet pairs.
            preg_match_all('/(?P<field>\w+):"(?P<value>(?:[^"\\\\]|\\\\.)*)"/',
                $_GET['facet'], $matches
            );

            // Collapse into an array of pairs.
            foreach ($matches['field'] as $i => $field) {
                $value = preg_replace('/\\\\(.)/s', '$1', $matches['value'][$i]);
                if ($value !== '') {
                    $facets[] = array($field, $value);
                }
            }

        }

        return $facets;

    }


    /**
     * Rebuild the URL with a new array of facets.
     *
     * @param array $facets The parsed facets.
     * @return string The new URL, HTML-escaped for use in an attribute.
     */
    public static function makeUrl($facets)
    {

        // Collapse the facets to `:` delimited pairs.
        $fParam = array();
        foreach ($facets as $facet) {
            $value = addcslashes($facet[1], '"\\');
            $fParam[] = "{$facet[0]}:\"{$value}\"";
        }

        // Get the `q` parameter, reverting to ''.
        $qParam = array_key_exists('q', $_GET) && is_string($_GET['q']) ? $_GET['q'] : '';

        // String together the final route.
        $query = http_build_query(array(
            'q'     => $qParam,
            'facet' => implode(' AND ', $fParam)
        ));

        return html_escape(url('solr-search') . '?' . $query);

    }


    /**
     * Add a facet to the current URL.
     *
     * @param string $field The facet field.
     * @param string $value The facet value.
     * @return string The new URL.
     */
    public static function addFacet($field, $value)
    {

        // Get the current facets.
        $facets = self::parseFacets();

        // Add the facet, if it's not already present.
        if (!in_array(array($field, (string) $value), $facets, true)) {
            $facets[] = array($field, (string) $value);
        }

        // Rebuild the route.
        return self::makeUrl($facets);

    }


    /**
     * Remove a facet to the current URL.
     *
     * @param string $field The facet field.
     * @param string $value The facet value.
     * @return string The new URL.
     */
    public static function removeFacet($field, $value)
    {

        // Get the current facets.
        $facets = self::parseFacets();

        // Reject the field/value pair.
        $reduced = array();
        foreach ($facets as $facet) {
            if ($facet !== array($field, (string) $value)) $reduced[] = $facet;
        }

        // Rebuild the route.
        return self::makeUrl($reduced);

    }


    /**
     * Get the human-readable label for a facet key.
     *
     * @param string $key The facet key.
     * @return string The label (not HTML-escaped).
     */
    public static function keyToLabel($key)
    {
        $fields = get_db()->getTable('SolrSearchField');
        $field = $fields->findBySlug(preg_replace('/_s$/', '', $key));
        return $field ? $field->label : $key;
    }


}
