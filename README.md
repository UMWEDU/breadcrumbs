# Unified Breadcrumbs #
**Contributors:** cgrymala
  
**Tags:** genesis, breadcrumbs, multisite
  
**Requires at least:** 4.0
  
**Tested up to:** 4.1.2
  
**Stable tag:** 0.1a
  
**License:** GPLv2 or later
  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html
  

Allows you to set up a unified breadcrumb hierarchy throughout a multisite environment.

## Description ##

A unified breadcrumb plugin for WordPress multi-site/multi-network installations.

Purpose:

1. to provide a single-source breadcrumb trail for all sites in a network. The sites will each choose their "parent," much like nesting pages. Each site can have only one parent, but a parent can have multiple children. Once a site has chosen a parent, it then can become the parent for other sites. The trick, I'm told, is making sure sites can't select circular "trees." ParentA can't at some point select one of its own children/grandchildren as its parent.
1. to create an intentional, controlled "sitemap" display on the front-end using a combination of the nesting information provided to by the plugin and the structures of the individual sites.

## Installation ##

1. Upload the `unified-breadcrumbs` directory to `wp-content/plugins`
1. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions ##

### How do I change the URL/name of the root home page? ###

Use the `unified-breadcrumbs-home-link` and `unified-breadcrumbs-home-url` filters

### How do I configure the hierarchy? ###

On each site, there is a settings area where you can specify which site is the parent of the current site

## Changelog ##

### 0.1a ###

* Initial version
