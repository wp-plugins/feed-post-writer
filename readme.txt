=== Feed Post Writer ===

Contributors: goodevilgenius
Plugin URI: https://github.com/goodevilgenius/feed-post-writer
Tags: rss, feed, posts
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: trunk
License: MIT
License URI: http://directory.fsf.org/wiki/License:Expat

This will take the first entry in an RSS feed and use it as the content for a user-chosen post.

== Description ==

This Wordpress plugin will take the first entry from a given RSS feed (or ATOM),
and use it as the content for a specific post.

I use this with the
[Custom Post Widget](https://wordpress.org/plugins/custom-post-widget/) to
create a widget that automatically updates with the most recent post of a given
feed.

== Installation ==

1. Upload the `feed-post-writer` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add feeds under "Feed Post Writer" under the "Tools" tab.
    * To find the post ID, click on Posts (or Pages, or a Custom Post Type), and click "Edit".
        The resulting URL should have "?post=X&action=edit". X, in this case, is the Post ID.

== Frequently Asked Questions ==

= Does it only work for posts? =

No, this also works for Pages, and Custom Post types.

= How do I find the Post ID? =

To find the post ID, click on Posts (or Pages, or a Custom Post Type), and click "Edit".
The resulting URL should have "?post=X&action=edit". X, in this case, is the Post ID.

= How do I request new features? =

Request new features on [the GitHub page](https://github.com/goodevilgenius/feed-post-writer/issues).

= Can it automatically create new posts? =

No, that's not the intention of this plugin. There are other ways to automatically have posts created by an RSS feed.

= Can it post more than just the first item from the feed? =

Not at this time.

== Screenshots ==

1. Settings screen

== Changelog ==

= 0.5 =
It works!

= 0.1 =
* Initial release

