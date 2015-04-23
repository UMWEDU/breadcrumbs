# breadcrumbs
A unified breadcrumb plugin for WordPress multi-site/multi-network installations.

Purpose: 

(1) to provide a single-source breadcrumb trail for all sites in a network. The sites will each choose their "parent," much like nesting pages. Each site can have only one parent, but a parent can have multiple children. Once a site has chosen a parent, it then can become the parent for other sites. The trick, I'm told, is making sure sites can't select circular "trees." ParentA can't at some point select one of its own children/grandchildren as its parent.

(2) to create an intentional, controlled "sitemap" display on the front-end using a combination of the nesting information provided to by the plugin and the structures of the individual sites.
