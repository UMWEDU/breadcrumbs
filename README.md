# Unified Breadcrumbs #
**Contributors:** cgrymala

**Tags:** genesis, breadcrumbs, multisite

**Requires at least:** 4.0

**Tested up to:** 4.1.2

**Stable tag:** 0.1.3

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html


Allows you to set up a unified breadcrumb hierarchy throughout a multisite environment.

## Description ##

At this point in time, the only thing this plugin does is to allow you to add up to 3 "external" parents to a site's breadcrumb trail. The UMW home page link will automatically be prepended to the front of all breadcrumb trails. Beyond that, this plugin also allows you to specify three more levels to be prepended at the front of the breadcrumb trail.

For instance, if you are working on the Biology site, within the College of Arts & Sciences, you can have the breadcrumb trail include UMW (automatically), then CAS, before the Biology home page in the breadcrumb trail.

None of the other features that are planned for this plugin have yet been implemented.

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

### 0.1.3 ###

* Implement the manual breadcrumb process to allow immediate use of the plugin while the automated features are still being developed.

### 0.1a ###

* Initial version
