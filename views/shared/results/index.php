<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>


<?php queue_css_file('results'); ?>
<?php echo head(array('title' => __('Solr Search')));?>


<h1><?php echo __('Search the Collection'); ?></h1>


<!-- Search form. -->
<div class="solr">
  <form id="solr-search-form">
    <input type="submit" value="<?php echo html_escape(__('Search')); ?>" />
    <span class="float-wrap">
      <input type="text" title="<?php echo html_escape(__('Search keywords')); ?>" name="q" value="<?php
        echo array_key_exists('q', $_GET) && is_string($_GET['q']) ? html_escape($_GET['q']) : '';
      ?>" />
    </span>
  </form>
</div>


<?php if (!$results): ?>

<!-- Solr could not answer. -->
<div id="solr-results">
  <p class="solr-error"><?php echo __('Search is temporarily unavailable. Please try again later.'); ?></p>
</div>

<?php else: ?>

<!-- Applied facets. -->
<div id="solr-applied-facets">

  <ul>

    <!-- Get the applied facets. -->
    <?php foreach (SolrSearch_Helpers_Facet::parseFacets() as $f): ?>
      <li>

        <!-- Facet label. -->
        <?php $label = SolrSearch_Helpers_Facet::keyToLabel($f[0]); ?>
        <span class="applied-facet-label"><?php echo html_escape($label); ?></span> >
        <span class="applied-facet-value"><?php echo html_escape($f[1]); ?></span>

        <!-- Remove link. -->
        <?php $url = SolrSearch_Helpers_Facet::removeFacet($f[0], $f[1]); ?>
        (<a href="<?php echo $url; ?>"><?php echo __('remove'); ?></a>)

      </li>
    <?php endforeach; ?>

  </ul>

</div>


<!-- Facets. -->
<div id="solr-facets">

  <h2><?php echo __('Limit your search'); ?></h2>

  <?php $facetFields = isset($results->facet_counts->facet_fields) ? $results->facet_counts->facet_fields : array(); ?>
  <?php foreach ($facetFields as $name => $facets): ?>

    <!-- Does the facet have any hits? -->
    <?php if (count((array) $facets)): ?>

      <!-- Facet label. -->
      <?php $label = SolrSearch_Helpers_Facet::keyToLabel($name); ?>
      <strong><?php echo html_escape($label); ?></strong>

      <ul>
        <!-- Facets. -->
        <?php foreach ($facets as $value => $count): ?>
          <li class="<?php echo html_escape($value); ?>">

            <!-- Facet URL. -->
            <?php $url = SolrSearch_Helpers_Facet::addFacet($name, $value); ?>

            <!-- Facet link. -->
            <a href="<?php echo $url; ?>" class="facet-value">
              <?php echo html_escape($value); ?>
            </a>

            <!-- Facet count. -->
            (<span class="facet-count"><?php echo (int) $count; ?></span>)

          </li>
        <?php endforeach; ?>
      </ul>

    <?php endif; ?>

  <?php endforeach; ?>
</div>


<!-- Results. -->
<div id="solr-results">

  <!-- Number found. -->
  <h2 id="num-found">
    <?php echo __('%s results', (int) $results->response->numFound); ?>
  </h2>

  <?php foreach ($results->response->docs as $doc): ?>

    <?php $record = SolrSearch_Helpers_View::getDocumentRecord($doc); ?>

    <!-- Document. -->
    <div class="result">

      <!-- Header. -->
      <div class="result-header">

        <!-- Record URL. -->
        <?php $url = $record ? SolrSearch_Helpers_Index::getUri($record) : ''; ?>

        <!-- Title. -->
        <a href="<?php echo html_escape($url); ?>" class="result-title"><?php
                $title = is_array($doc->title) ? reset($doc->title) : $doc->title;
                $title = trim(strip_tags((string) $title), " \t\n\r\0\x0B");
                if ($title === '') {
                    echo '<i>' . __('Untitled') . '</i>';
                } else {
                    echo html_escape($title);
                }
            ?></a>

        <!-- Result type. -->
        <?php $type = is_array($doc->resulttype) ? reset($doc->resulttype) : $doc->resulttype; ?>
        <span class="result-type">(<?php echo html_escape((string) $type); ?>)</span>

      </div>

      <!-- Highlighting. -->
      <?php if (get_option('solr_search_hl') && isset($results->highlighting->{$doc->id})): ?>
        <ul class="hl">
          <?php foreach($results->highlighting->{$doc->id} as $field): ?>
            <?php foreach((array) $field as $hl): ?>
              <?php /* Solr HTML-encodes the snippet text (hl.encoder=html), so the only markup left is its <em> tags. */ ?>
              <li class="snippet"><?php echo strip_tags($hl, '<em>'); ?></li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php
        if ($record instanceof Item) {
            echo item_image_gallery(
                array('wrapper' => array('class' => 'gallery')),
                'square_thumbnail',
                false,
                $record
            );
        }
      ?>

    </div>

  <?php endforeach; ?>

</div>


<?php echo pagination_links(); ?>

<?php endif; ?>

<?php echo foot();
