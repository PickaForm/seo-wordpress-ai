=== SEO WP AI ===
Contributors: DnL team
Donate link: https://seowp.ai/#download
Tags: SEO, OpenAI, Yoast
Tested up to: 6.2
Requires at least: 5.9
Requires PHP: 7.2.5
Version: 1.0
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

SEO WP AI leverages the power of AI to automatically fill the main fields of Yoast SEO plugin.

== Description ==

Extension for the Yoast SEO plugin in Wordpress, harnessing artificial intelligence to optimize each page of your website.
Maximize your SEO potential by utilizing our powerful solution seamlessly integrated with the Yoast SEO plugin.
Benefit from advanced optimization, intelligent keyword suggestions, and in-depth content analysis in just one click.

== Installation ==

First ensure you have Yoast SEO plugin enabled in Wordpress, then:
1. Download the plugin from the [WordPress Plugin Directory](https://wordpress.org/plugins/) or from [SEO WP AI website](https://seowp.ai/download-free/).
2. Unzip the file.
3. Copy the plugin folder into the `/wp-content/plugins/` directory.
4. Go to the WordPress dashboard and navigate to the "Plugins" section.
5. Activate the plugin.
Done!

== Configuration ==

1. Navigate to the "Settings" section, then "SEO WP AI"
2. Enter a passphrase to encrypt your OpenAI api key, then enter the OpenAI key and validate
You're good!

== Usage ==

- Go to the Wordpress page, blog article or product you want to reference
- Don't forget to save your page if you've just modified it
- Scroll down to where Yoast SEO is located and you'll see an additional button: "SEO WP AI".
- Click on this button: the page will start loading while requesting OpenAI with your content
- You should see the result filled in the Yoast fields "focus key phrase" and "meta description".
- In the event of an error, check that the OPEN AI api key has been entered correctly.

For now, the button automagically fills the 2 main SEO fields (focus key phrase + description).
Don't forget that the future version of SEO WP AI will do much more if you support us!

== Project structure ==

- seo-wp-ai.css: Application styles
- seo-wp-ai.js: Client-side UI built with KissJS for interacting with the user and displaying messages
- seo-wp-ai.php: Server configuration file to run Wordpress
- licence.txt: SEO WP AI licence terms + GPL 3 license terms
- readme.txt: this file

== Changelog ==

= 1.0 =
* Initial release of the plugin.

== Upgrade Notice ==

= 1.0 =
* No updates required.

== Contributions ==

DnL team.

== License ==

GNU General Public License v3

== Contact ==

https://seowp.ai/contact-us/
