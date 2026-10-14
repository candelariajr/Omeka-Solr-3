SolrSearch (Omeka Classic) core / configset for Apache Solr 10
==============================================================

conf/        schema.xml, solrconfig.xml and the word lists they use
core.properties   lets standalone Solr discover this directory as the "omeka" core

Standalone Solr (bin/solr start --user-managed):
    copy this whole directory into the Solr home (e.g. /var/solr/data/omeka)
    and restart Solr.

SolrCloud (bin/solr start):
    bin/solr create -c omeka -d <path to this directory>/conf --shards 1 --replication-factor 1

Then set the plugin's Core URL to /solr/omeka/ and run
Solr Search > Index > Clear and Reindex in Omeka.
