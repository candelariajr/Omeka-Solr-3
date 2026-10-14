<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<tr>
  <td class="element">

  <input
    name="facets[<?php echo html_escape($field->slug); ?>][id]"
    value="<?php echo (int) $field->id; ?>"
    type="hidden"
  />

  <span class="original-label">
    <?php echo html_escape($field->getOriginalLabel()); ?>
  </span>

  </td>

  <td>
    <input
      name="facets[<?php echo html_escape($field->slug); ?>][label]"
      value="<?php echo html_escape((string) $field->label); ?>"
      type="text"
    />
  </td>

  <?php foreach (array('is_indexed', 'is_facet') as $opt): ?>
    <td>
      <input
        name="facets[<?php echo html_escape($field->slug); ?>][<?php echo $opt; ?>]"
        <?php if ($field->$opt): ?>checked="checked"<?php endif; ?>
        type="checkbox"
      />
    </td>
  <?php endforeach; ?>

</tr>
