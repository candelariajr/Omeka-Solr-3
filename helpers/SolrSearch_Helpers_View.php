<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_View
{


    /**
     * This returns the base URL for the results page.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getBaseUrl()
    {
        return url('solr-search');
    }


    /**
     * Lookup the element name for a solr element.
     *
     * @param string $field Field name to look up.
     *
     * @return string Human readable solr element name.
     */
    public static function lookupElement($field)
    {
        $fieldArray = explode('_', $field);
        $fieldId = $fieldArray[0];
        $db = get_db();
        $element = $db->getTable('Element')->find($fieldId);
        return $element['name'];
    }


    /**
     * Return the path for an image.
     *
     * @param string $type Omeka File type (size).
     * @param int $fileId Id of the file to look up.
     *
     * @return string Link to file.
     */
    public static function getImagePath($type='fullsize', $fileId=null)
    {
        $db   = get_db();
        $file = $fileId ? $db->getTable('File')->find($fileId) : null;
        return $file ? $file->getWebPath($type) : '';
    }


    /**
     * Generate an image tag for use in search results.
     *
     * @param int $image_id Image to look up.
     * @param string $alt Alt text for image.
     *
     * @return string $html Link to image.
     * @author Wayne Graham <wsg4w@virginia.edu>
     **/
    public static function createResultImgHtml($image_id, $alt)
    {
        $html = '';
        $thumbnail  = self::getImagePath('square_thumbnail', $image_id);
        $fullsize   = self::getImagePath('fullsize', $image_id);

        $html .= '<a class="solr_search_image" href="' . html_escape($fullsize) . '">';
        $html .= '<img alt="' . html_escape($alt) . '" src="' . html_escape($thumbnail) . '" />';
        $html .= '</a>';

        return $html;
    }


    /**
     * Get the URL for the record that corresponds to a Solr document.
     *
     * @param Apache_Solr_Document $doc A Solr document.
     *
     * @return string The record URL.
     */
    public static function getDocumentUrl($doc)
    {
        $record = self::getDocumentRecord($doc);
        return $record ? SolrSearch_Helpers_Index::getUri($record) : '';
    }


    /**
     * Load the Omeka record a Solr document was built from.
     *
     * @param Apache_Solr_Document $doc A Solr document.
     *
     * @return Omeka_Record_AbstractRecord|null The record, or null if it no
     * longer exists (e.g. deleted since the last reindex) or the model is
     * unknown (e.g. the plugin that provided it was uninstalled).
     */
    public static function getDocumentRecord($doc)
    {
        $model = is_array($doc->model) ? reset($doc->model) : $doc->model;
        $id    = is_array($doc->modelid) ? reset($doc->modelid) : $doc->modelid;
        if (!$model || !$id || !class_exists($model)) {
            return null;
        }
        try {
            return get_db()->getTable($model)->find($id);
        } catch (Exception $e) {
            return null;
        }
    }


}
