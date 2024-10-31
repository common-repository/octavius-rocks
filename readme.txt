=== Octavius Rocks ===
Contributors: Octavius.rocks,edwardbock,pixelwelt,benjamin.birkenhake,kroppenstedt,palasthotel
Donate link: https://octavius.rocks/
Tags: analytics, service, tracking, optimization
Requires at least: 4.0
Tested up to: 6.2.0
Requires PHP: 7.4
Stable tag: 3.9.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl

Works with octavius analytics service

Octavius Version: 5.6.4

== Description ==

The octavius service is made for live tracking and analysing how well your teasers and links are klicked. It's optimized to work with our [Grid](http://wordpress.org/plugins/grid "Grid Landingpage Editor") plugin.

== Installation ==

1. Upload octatvius-client.zip to the /wp-content/plugins/ directory
2. Extract the plugin to octavius-rocks folder
3. Activate the plugin
4. Config the service on the settings page
5. Pageviews will be tracked automatically. To track rendered-events and klicks for teasers, add do_action('render_octavius_attributes', 'your_viewmode') to your teaser-links.

== Frequently Asked Questions ==

= Do I need to register somewhere to use this plugin? =

Yes. You can get your beta key at www.octavius.rocks

== Screenshots ==

1. Octavius Service

== Documentation ==

To get your octavius attributes you can use this two options:

do_action( 'render_octavius_teaser_attributes', 'VIEWMODE' );
apply_filters( 'get_octavius_teaser_attributes', 'VIEWMODE' );

This will get the pagetype, content_id and content_type will be generated automatically. If you like, you can overwrite the viewmode.

With this options, you can use the attributes more flexible and set the values on your own. Please pass an array with one or more of the following properties:
viewmode, content_type, pagetype, content_id, region

add_action( 'render_octavius_attributes', array( 'region' => 'head', ... ) );
add_filter( 'get_octavius_attributes', array( 'region' => 'head', ... ) );

== Changelog ==

= 3.9.3 =
 * Optimization: default config cache ttl to 0 which means always deliver from cache
 * Optimization: is updating query cache timeout for transient lock

= 3.9.2 =
 * Bugfix: some undefined access warnings

= 3.9.1 =
 * Bugfix: Language info in posts table in none wpml sites removed

= 3.9.0 =
 * Feature: WPML support

= 3.8.0 =
 * Feature: Big Octavius dashboard update with better filter controls for more insights

= 3.7.1 =
 * Bugfix: PHP 8 usort error wrong type when saving breakpoints
 * Bugfix: Undefined index warning fix when using forms in admin

= 3.7.0 =
 * Feature: Topic views are now available
 * Security fix: unescaped input field values

= 3.6.14 =
 * Library update: PHP 8 fix

= 3.6.13 =
 * Bugfix: Server connection health check
 * Update: JavaScript libraries

= 3.6.12 =
 * Bugfix: Day shift bugfix in pageviews per day charts
 * Bugfix: Instance of WP_Post_Type check fix

= 3.6.11 =
 * Bugfix: Query cache could not be updated

= 3.6.10 =
 * Bugfix: Query cache was not working in some rare cases for example empty result cache

= 3.6.9 =
 * Bugfix: Safari parse date crash fix

= 3.6.8 =
 * WARNING: Runs only with Octavius.Rocks Service v5.6.4 and higher
 * Feature: Latest 7 days pageviews in gutenberg sidebar

= 3.6.7 =
 * Optimization: Query cache schedule

= 3.6.6 =
 * Feature: Query caching that can be kept warm via cron wp cli script.

= 3.6.5 =
 * Feature: new filter for dashboard access capability modification
 * BlockX: most read block type for blockx plugin

= 3.6.4 =
 * Optimization: Public accessible function to modify store state
 * Optimization: Skip large taxonomies for dashboard query filter
 * Bugfix: Timezone problems
 * Bugfix: enqueue assets on wordpress < 5.x was broken

= 3.6.3 =
 * Feature: Custom Gutenberg sidebar
 * Feature: Direct link from post events to query dashboard
 * Optimization: Breakpoints partitioning visual fix
 * Bugfix: JavaScript errors customizer

= 3.6.2 =
 * Feature: theme template for most read widget
 * Bugfix: deprecated pageview_pixel amp pageviews were missing in posts table numbers
 * Bugfix: dependency to grid plugin fixed

= 3.6.1 =
 * Feature: query tool remembers last query config
 * Feature: Event type is selectable with event query tool
 * Feature: 5 minute and hourly event chart
 * Optimization: automatically query for default values on dashboard
 * Bugfix: pageview dashboard widget fix. wrong time interval
 * Bugfix: wp_timezone does not exists before WordPress 5.3
 * Bugfix: JavaScript pollyfills to fix Edge Browser errors

= 3.6.0 =
 * Breaking change: JavaScript API has changed completely. Please check any extensions.
 * Breaking change: use octavius_rocks_register_frontend_plugins filter to register frontend extension javascript for proper dependency handling
 * Feature: Most read widget
 * Optimization: UI and visualizations cleanup
 * Optimization: No external script loadings anymore
 * Optimization: Less script pageload
 * Optimization: Less Settings for easier configuration

= 3.5.3 =
 * Bugfix: countable error in cron task

= 3.5.2 =
 * Optimization: Widget only update on own config changes
 * Bugfix: Realtime widget config not working

= 3.5.1
 * Optimization: Page info hook
 * Optimization: Meta box with more infos to content events
 * Optimization: Meta box with more infos to content events

= 3.5.0 =
 * Optimization: Performance of widgets
 * Feature: AMP Support
 * Bugfix: Post meta box numbers not working correctly
 * Bugfix: problems with empty pageview cache calls

= 3.4.3 =
 * Optimization: Number format for post table pageviews. Helps with large numbers.
 * Bugfix: Add live pageviews in content tables in backend was not working
 * Bugfix: Missing protocol in formular url on settings pages.
 * Bugfix: TimeZone not set fix.

= 3.4.2 =
 * Feature: Grid box supports multiple post types
 * Optimization: Pageviews in every post type tables
 * Optimization: JavaScript performance
 * Optimization: Last fetch of server configuration in correct timezone
 * Optimization: Socket connection health check
 * Bugfix: problems with multisite installations
 * Different other bugfix

= 3.4.1 =
 * Feature: Custom live breakpoints cluster
 * Feature: reduce to a set of live breakpoints
 * optimization: collect post ids to one ajax call in posts-table.js
 * Bugfix: OQL NOT IN condition fix
 * Bugfix: posts-table.js javascript
 * Bugfix: Bad response exception fix
 * Optimization: Increase timeout for import of pageviews on cli execution. Large instances wont work otherwise.

= 3.4.0 =
 * Optimization: PageviewCache table now caches pageviews so we can order by them in posts table
 * Feature: WP_Query orderby pageviews available.
 * Feature: Posts tables are sortable by pageviews

= 3.3.7
 * Feautre: Pageviews in post edit meta box
 * Feature: new filter octavius_rocks_do_not_load_scripts
 * Feature: new filter octavius_rocks_skip_pageview
 * Feature: new filter octavius_rocks_pageview_entity
 * Optimization: do not track post preview or customizer preview
 * Bugfix: post edit widget.css missing fix
 * Bugfix: pageview and pageview_pixel events leading to wrong numbers ins posts table

= 3.3.6
 * Feature: Pageviews column in posts table.
 * Feature: Filter to change arguments for posts table pageviews column octavius query.
 * Optimization: Hook to add grid boxes which depend on octavius top box
 * Optimization: More options for grid box
 * Optimization: Tracking of custom taxonomies
 * Optimization: term/ID is not tracking field term_taxonomy/ID
 * Bugfix: Grid box date and post type fix

= 3.3.5 =
 * Feature: Submenu hook.
 * Feature: Submenu overview hook.

= 3.3.4 =
 * Bugfix: Settings tabs not working
 * Bugfix: Missing datepicker css

= 3.3.3 =
 * Feature: Brand new dashboards and widgets
 * Feature: Grid box for top contents
 * Feature: Track categories for is_single pageview
 * Feature: Track tags for is_single pageview

= 3.3.2 =
 * Bugfix: Server connection setting issues.
 * Bugfix: Fetching fresh configuration on schedule.

= 3.3.1 =
 * Bugfix: Live breakpoints bugfix

= 3.3.0 =
 * Automatic configuration
 * Live breakpoints

= 3.2.5 =
 * new public functions

= 3.2.4 =
 * Scripts with slim version. Use SCRIPT_DEBUG to for debugging.

= 3.2.3 =
 * Critical bug fix: top live contents dashboard widget

= 3.2.2 =
 * WP-Configable options
 * options are cached

= 3.2.1 =
 * term and author live tracking
 * out of the box search terms tracking
 * show_on_front == "posts" track a wrong content id fix
 * author page tracking fix
 * term page tracking fix
 * Use local script loading bugfix
 * new action octavius_rocks_page_info
 * page info collect on admin pages fix


= 3.2.0 =
 * Statistics on post pages
 * Public query shortcut functions octavius_rocks_query_top_contents and octavius_rocks_query_for_post
 * default breakpoint calculation for entities in 20px steps

= 3.1.2
 * Multi-site settings fix
 * New api version 5.6.1

= 3.1.1 =
 * New Settings structure
 * New JavaScript for tracking
 * JavaScript from uploads optional

= 3.1 =
 * Dashboard widgets added
 * Octavius Query API
 * Action render_js renamed to octavius_rocks_render_js
 * Action render_octavius_attributes renamed to octavius_rocks_render_attributes
 * Action render_octavius_teaser_attributes renamed to octavius_rocks_render_teaser_attributes
 * Filter octavius_plugins renamed to octavius_rocks_plugins
 * Fitler get_octavius_attributes renamed to octavius_rocks_get_attributes
 * Filter get_octavius_teaser_attributes renamed to octavius_rocks_teaser_attributes
 * Tracking pageview with websockets

= 3.0.1 =
 * Fixed error message on installation
 * Validation of server settings form
 * Update octavius js download mechanism

= 3.0 =
 * Add functionality to prepare themes for tracking more easy
 * moved extra features like a/b-testing to new plugins

= 2.0 =
 * Improved A/B-Testing: Create your test, start and evaluate them and improve your articles
 * Get slack notifications when test are finished
 * Get a detailed evaluation

= 1.4 =
 * Refactored

= 1.3.10 =
 * Added functionality for notification service and global sample size setting.

= 1.3.9 =
 * Fixed some errors in AB-Results Box.

= 1.3.8 =
 * Top Box replaces by Custom Report Box

= 1.3.7 =
 * Short php code fix for larger compatibility
 * hide loading icon on dashboard widget if no items found

= 1.3.6 =
 * Dashboard widget calculates results for posts by significance

= 1.3.5 =
 * Hook for including octavius scripts in other plugins

= 1.3.4 =
 * Enable or disable A/B Tests for post option

= 1.3.3 =
 * Only logged out and abonnement users get tracked

= 1.3.2 =
 * WP 4.3 tested
 * Variant result loaded to post object
 * ignore pageview variant if variant is selected

= 1.3.1 =
 * AB results with variant names
 * AB dashboard widget
 * AB variant decision selectable

= 1.3.0 =
* More filters for ab testing results

= 1.2.8 =
* Event type selection in ab result result chart

= 1.2.8 =
* Referer tracking for pageview

= 1.2.7 =
* A/B variant tracking filter

= 1.2.6 =
* Pageview can track A/B variant

= 1.2.5 =
* Wrong content id for pageview fix

= 1.2.4 =
* A/B Testing
* Tracking pixel display fix

= 1.2.1 =
* Dashboard original tracked url as title attribute

= 1.2.1 =
* Pageviews tracking

= 1.2 =
* Top list Grid box

= 1.1.1 =
* Dashboard Timestamp fix
* Dashboard GUI speed
* Dashboard post titles

= 1.1.1 =
* Dashboard content limitations fix

= 1.1 =
* Dashboard Widget

= 1.0.3 =
* Miscalculation fixes

= 1.0 =
* Provides tracking js and admin button to analyse pages

== Upgrade Notice ==

= 3.6.8 =

Works only with Octavius.Rocks v5.6.4 and higher.

= 3.6.0 =

The JavaScript api for frontend as well as admin usage has changed completely. Please check any extensions.

= 3.3.3 =

We are tracking categories and tags into tag1 to tag10 fields by default now. If you use there fields for custom tracking please check if it is still working as expected.


== Arbitrary section ==
