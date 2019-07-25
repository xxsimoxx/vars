# cpvars

With cpvars you can define name-value associations from the admin.

Then, in your content you can insert
`[cpvars]name[/cpvars]`
and get _value_ displayed.

Useful if you have a value (a phone number, number of employees) in several pages that can change, so you can change this once from the admin.

There is also an option (that affects every shortcode in your site) to display shortcodes in areas where normally they are not.
- single_post_title
- the_title
- widget_text
- widget_title
- bloginfo
- get_post_metadata

It integrates into TinyMCE by adding a menu.
