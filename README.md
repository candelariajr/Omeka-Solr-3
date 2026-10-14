# SolrSearch for Omeka Classic

**SolrSearch** replaces the default Omeka search interface with one powered by
[Apache Solr][solr], a scalable and feature-rich search engine that supports
faceting and hit highlighting. In most cases, Omeka's built-in searching
capabilities work great, but there are a couple of situations where it might
make sense to take a look at Solr:

  - When you have a really large collection, and want something a bit faster;

  - When your site contains a lot of text content, and you want to take
    advantage of Solr's hit highlighting functionality, which displays a
    preview snippet from each of the matching records;

  - When your site makes use of a lot of different taxonomies (collections,
    item types, etc.), and you want to use Solr's faceting capabilities, which
    make it possible for users to refine searches by cropping down the set of
    results to focus on specific categories.

## Requirements

  - **Apache Solr 10** (tested with 10.0.0). The Solr configuration in
    `solr-core/omeka/conf` is written for Solr 10; see the note in
    `solrconfig.xml` if you need to run it on Solr 9.
  - **Omeka Classic 3.x** (tested with 3.2.2).
  - **PHP 7.1 or newer** (whatever your Omeka version requires; tested on 8.3,
    and checked for PHP 8.4/8.5 deprecations).
  - Optional: Simple Pages and/or Exhibit Builder, whose pages and exhibits are
    indexed too.

## Installation

### 1. The Solr core / collection

The configuration lives in `solr-core/omeka/conf` (`schema.xml`,
`solrconfig.xml` and the word lists). Solr 10 starts in SolrCloud mode by
default; use whichever of these matches how you run Solr.

**Standalone Solr** (`bin/solr start --user-managed`):

```sh
cp -r solr-core/omeka "$SOLR_HOME/omeka"      # e.g. /var/solr/data/omeka
chown -R solr:solr "$SOLR_HOME/omeka"
# restart Solr, or load it without a restart:
curl "http://localhost:8983/solr/admin/cores?action=CREATE&name=omeka&instanceDir=omeka"
```

(If you use the CoreAdmin `CREATE` call, delete `core.properties` from the
copied directory first; Solr refuses to create a core where one is already
defined.)

**SolrCloud** (`bin/solr start`):

```sh
bin/solr create -c omeka -d /path/to/solr-core/omeka/conf --shards 1 --replication-factor 1
```

Note that in Solr 10 `-s` means `--solr-url`, not shards.

Either way, the plugin's **Core URL** is then `/solr/omeka/`.

**Upgrading from an older Solr (4.x–9.x):** install the new configuration and
then run **Solr Search › Index › Clear and Reindex** in Omeka. Solr 10 cannot
read indexes built by Solr 8 or older, and the schema changed, so a reindex
is required anyway. Field names did not change, so your field and facet
settings in Omeka are kept.

### 2. The Omeka plugin

  1. Put this directory in Omeka's `plugins` folder and name it `SolrSearch`.
  2. On Omeka's **Plugins** page, click **Install** (or **Upgrade**) for
     Solr Search.

For more information, check out the [Managing Plugins][managing-plugins] guide.

Reindexing runs as an Omeka background job, which needs the PHP command-line
binary. If reindexing never starts, set `background.php.path` in
`application/config/config.ini` to the full path of the PHP CLI (for example
`/usr/bin/php`; `which php` will tell you).

## Configuration

### Server Configuration

To get started, click on the "Solr Search" tab, which displays a form with Solr
connection parameters:

  - **Server Host**: The location of the Solr server, without the port number.

  - **Server Port**: The port that Solr is listening on (Solr's default is
    8983).

  - **Core URL**: The path of the Solr core or collection in which documents
    should be indexed, for example `/solr/omeka/`.

After making changes to the connection parameters, click the "Save Settings"
button. If the plugin is able to connect to Solr, a green notification saying
"Solr connection is valid" will be displayed.

### Collections Configuration

You can also decide not to index certain collections of items. By default, all
collections are indexed. However, if you go to the "Collections" tab, then you
can select collections to *exclude* from indexing.

### Field Configuration

This form makes it possible to configure (a) which metadata elements and Omeka
categories ("fields") are stored as searchable content in Solr and (b) which
fields should be used as "facets", groupings of records that can be used to
iteratively narrow down the set of results.

> If you've installed any new metadata elements and they're missing from this
> form, click the "Load New Elements" button at the bottom of the page. The
> page will reload, and the new elements will be listed.

For each element, there are three options:

  - **Facet Label**: The label used as the heading for the facet corresponding
    to the field. In most cases, it probably just makes sense to use the
    canonical name as the element (the default), but this makes it possible to
    create a customized interface that doesn't map onto the nomenclature of the
    metadata.

  - **Is Indexed?**: If checked, the content in this field will be stored as
    full-text-searchable content in Solr. As a rule of thumb, it makes sense to
    index any fields that contain non-trivial text content, but not fields that
    contain non-semantic data or identifiers.

  - **Is Facet?**: If checked, the field will be used as a facet in the
    results. As a rule of thumb, **a field might be a useful facet if it
    contains a controlled vocabulary**. For example, imagine you use one of
    three values in the Dublin Core "Type" field - `type1`, `type2`, and
    `type3`. This would make a good facet, because users would be able to hone
    in on the implicit relationships among items of the same type. It wouldn't
    make sense to use something like the "Description" field as a facet,
    though, two items will almost never share the exact same description (or,
    at least, they probably shouldn't!). Facet values longer than 8,000
    characters are truncated by Solr.

Use the accordion to expand and contract the fields in the three categories.
There are two types of fields - the "Omeka Categories," which aren't actually
metadata elements but rather high-level taxonomies that are baked in to the
structure of Omeka, and the metadata elements (Dublin Core and Item Type
Metadata) that can be used to describe items.

After you've made changes, click the "Update Search Fields" to save the
configuration.

### Results Configuration

This form exposes options for two features in Solr: **hit highlighting**, which
makes it possible to display preview snippets for each result that excerpt
portions of the metadata that are relevant to the query, and **faceting**,
which makes it possible for users to progressively refine large result sets by
honing in on specific categories.

  - **Enable Highlighting**: Set whether highlighting snippets should be
    displayed.

  - **Extent of Document Highlightable**: Set the amount of the document to
    scan when highlighting. By default, to save time, this is limited to 51200
    characters. If you have documents in the results that don't have snippets,
    you can make this larger.

  - **Number of Snippets**: The maximum number of snippets to display for a
    result.

  - **Snippet Length**: The approximate length of each snippet.

  - **Facet Ordering**: The criteria by which to sort the facets in the
    results.

  - **Facet Count**: The maximum number of facets to display.

  - **Display private items**: Let logged-in users whose role can see private
    items find them in search results.

Click "Save Settings" to update the configuration.

### Index Items

After making changes in the "Field Configuration" and "Results Configuration"
tabs, it's necessary to reindex the content in the site in order for the
changes to take effect. SolrSearch doesn't do this automatically because
reindexing can take as long as a few minutes for really large sites.

When you're ready, just click the "Clear and Reindex" button. This will spawn
off a background process behind the scenes that rebuilds the index according to
the new configuration options.

### Private Items

Public and private items are both indexed. Anonymous visitors (and users whose
role can't see private items) only get public items, published Simple Pages
and public exhibits. With **Display private items** enabled, logged-in users
whose role allows it also get private items.

## Searching

Once the content has been indexed, head to the public site and type a search
query into the regular Omeka search input. When the query is submitted,
SolrSearch will intercept the request and redirect to a custom interface that
displays results from Solr with faceting and hit highlighting.

If Solr can't be reached, the results page says so instead of failing, and the
reason is written to the PHP error log (as are failed index updates).

## Thanks

Official development has stopped on the Scholars' Lab version of this plugin.
I (@candelariajr) have taken over in order to keep developing it as we love to
use the plugin.

This couldn't have been accomplished without help from:

The Scholars Lab Team

Special thanks to:
* @anuragji
* @marcobat
* Adam Doan
* @cokernel
* Dave Lester


[solr]: https://solr.apache.org/
[managing-plugins]: https://omeka.org/classic/docs/Admin/Adding_and_Managing_Plugins/
