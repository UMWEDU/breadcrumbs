# breadcrumbs
A unified breadcrumb plugin for WordPress multi-site/multi-network installations.

Purpose: to provide a single-source breadcrumb trail for all sites in a network. The sites will each choose their "parent," much like nesting pages. Each site can have only one parent, but a parent can have multiple children. Once a site has chosen a parent, it then can become the parent for other sites. The trick, I'm told, is making sure sites can't select circular "trees." ParentA can't at some point select one of its own children/grandchildren as its parent.
