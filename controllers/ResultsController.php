<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_ResultsController
    extends Omeka_Controller_AbstractActionController
{


    /**
     * The facets table.
     *
     * @var SolrSearchFieldTable
     */
    protected $_fields;


    /**
     * Cache the facets table.
     */
    public function init()
    {
        $this->_fields = $this->_helper->db->getTable('SolrSearchField');
    }


    /**
     * Intercept queries from the simple search form.
     */
    public function interceptorAction()
    {
        $this->_redirect('solr-search?'.http_build_query(array(
            'q' => (string) $this->_request->getParam('query')
        )));
    }


    /**
     * Display Solr results.
     */
    public function indexAction()
    {

        // Get pagination settings.
        $limit = max(1, (int) get_option('per_page_public'));
        $page  = max(1, (int) $this->_request->getParam('page', 1));
        $start = ($page-1) * $limit;


        // determine whether to display private items or not
        // items will only be displayed if:
        // solr_search_display_private_items has been enabled in the Solr Search admin panel
        // user is logged in
        // user_role has sufficient permissions

        $user = current_user();
        if(get_option('solr_search_display_private_items')
            && $user
            && is_allowed('Items','showNotPublic')) {
            // limit to public items
            $limitToPublicItems = false;
        } else {
            $limitToPublicItems = true;
        }

        // Execute the query.
        $results = $this->_search($start, $limit, $limitToPublicItems);

        // Set the pagination.
        Zend_Registry::set('pagination', array(
            'page'          => $page,
            'total_results' => $results ? (int) $results->response->numFound : 0,
            'per_page'      => $limit
        ));

        // Push results to the view.
        $this->view->results = $results;

    }


    /**
     * Pass setting to Solr search
     *
     * @param int $offset Results offset
     * @param int $limit  Limit per page
     * @param bool $limitToPublicItems Only return public records.
     * @return Apache_Solr_Response|null Solr results, or null if Solr could
     * not answer (the view then shows a message instead of a server error).
     */
    protected function _search($offset, $limit, $limitToPublicItems = true)
    {

        // Connect to Solr.
        $solr = SolrSearch_Helpers_Index::connect();

        // Get the parameters.
        $params = $this->_getParameters($limitToPublicItems);

        // Construct the query.
        $query = $this->_getQuery();

        // Execute the query.
        try {
            return $solr->search($query, $offset, $limit, $params);
        } catch (Apache_Solr_HttpTransportException $e) {
            // A 400 usually means the user typed query syntax Solr can't
            // parse (an unbalanced quote or parenthesis, a stray AND...).
            // Retry once with every special character escaped.
            if ($e->getResponse()->getHttpStatus() == 400 && $query !== '*:*') {
                try {
                    $escaped = preg_replace_callback(
                        '/\b(AND|OR|NOT)\b/',
                        function ($m) { return strtolower($m[1]); },
                        Apache_Solr_Service::escape($query)
                    );
                    return $solr->search($escaped, $offset, $limit, $params);
                } catch (Exception $e2) {
                    $e = $e2;
                }
            }
            $this->_logSearchError($e);
        } catch (Exception $e) {
            $this->_logSearchError($e);
        }

        return null;

    }


    /**
     * Record why a search failed.
     *
     * @param Exception $e The error.
     */
    protected function _logSearchError($e)
    {
        $detail = '';
        if ($e instanceof Apache_Solr_HttpTransportException) {
            $body = (string) $e->getResponse()->getRawResponse();
            if ($body !== '') {
                $detail = ': ' . substr(preg_replace('/\s+/', ' ', $body), 0, 500);
            }
        }
        error_log('SolrSearch: search failed: ' . $e->getMessage() . $detail);
    }


    /**
     * Form the main Solr query from the user's search terms.
     *
     * @return string The Solr query.
     */
    protected function _getQuery()
    {

        // Get the `q` GET parameter.
        $query = trim((string) $this->_request->getParam('q'), " \t\n\r\0\x0B");

        // If defined, replace `:`; otherwise, revert to `*:*`.
        // Also, clean it up some.
        if ($query !== '') {
            $query = str_replace(':', ' ', $query);
            $to_remove = array('[', ']');
            foreach ($to_remove as $c) {
                $query = str_replace($c, '', $query);
            }
            if (trim($query, " \t\n\r\0\x0B") === '') {
                $query = '*:*';
            }
        } else {
            $query = '*:*';
        }

        return $query;

    }


    /**
     * Build the filter queries: the selected facets, and the public flag when
     * the visitor may not see private records. Filters are sent as `fq` so
     * they don't change the relevance of the user's query and are cached by Solr.
     *
     * @param bool $limitToPublicItems Only return public records.
     * @return array Filter queries.
     */
    protected function _getFilters($limitToPublicItems = true)
    {

        $filters = array();

        // Selected facets (only well-formed field:"value" pairs are honored).
        foreach (SolrSearch_Helpers_Facet::parseFacets() as $facet) {
            $filters[] = '{!term f=' . $facet[0] . '}' . $facet[1];
        }

        // Limit the query to public records if required.
        if ($limitToPublicItems) {
            $filters[] = 'public:true';
        }

        return $filters;

    }


    /**
     * Construct the Solr search parameters.
     *
     * @param bool $limitToPublicItems Only return public records.
     * @return array Array of fields to pass to Solr
     */
    protected function _getParameters($limitToPublicItems = true)
    {

        // Get a list of active facets.
        $facets = $this->_fields->getActiveFacetKeys();

        return array(

            'fq'                  => $this->_getFilters($limitToPublicItems),
            'facet'               => 'true',
            'facet.field'         => $facets,
            'facet.mincount'      => 1,
            'facet.limit'         => get_option('solr_search_facet_limit'),
            'facet.sort'          => get_option('solr_search_facet_sort'),
            'hl'                  => get_option('solr_search_hl')?'true':'false',
            'hl.method'           => 'unified',
            'hl.encoder'          => 'html',
            'hl.snippets'         => get_option('solr_search_hl_snippets'),
            'hl.fragsize'         => get_option('solr_search_hl_fragsize'),
            'hl.maxAnalyzedChars' => get_option('solr_search_hl_max_analyzed_chars'),
            'hl.fl'               => '*_t'

        );

    }


}
