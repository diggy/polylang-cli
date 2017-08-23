diggy/polylang-cli
==================

CLI for the Polylang plugin

[![Build Status](https://travis-ci.org/diggy/polylang-cli.svg?branch=master)](https://travis-ci.org/diggy/polylang-cli)

Quick links: [Installation](#installation) | [Using](#using) | [Contributing](#contributing) | [Development](#development)

## Installation

Installing this package requires WP-CLI v1.3.0 or greater. Update to the latest stable release with `wp cli update`. 
Once you've done so, you can install this package with `wp package install git@github.com:diggy/polylang-cli.git`.

## Using

This package implements the following commands:

### wp pll api

Inspect Polylang procedural API functions.

~~~
wp pll api
~~~







### wp pll api list

List Polylang procedural API functions.

~~~
wp pll api list [--format=<format>]
~~~

**OPTIONS**

	[--format=<format>]
		Accepted values: table, csv, json, count, yaml. Default: table

**EXAMPLES**

    $ wp pll api list
    $ wp pll api list --format=csv



### wp pll cache

Inspect and manage Polylang languages cache.

~~~
wp pll cache
~~~







### wp pll cache clear

Clears the Polylang languages cache.

~~~
wp pll cache clear 
~~~

**EXAMPLES**

    $ wp pll cache clear
    Success: Languages cache cleared.

    $ wp pll cache clear --quiet



### wp pll cache get

Gets the Polylang languages cache.

~~~
wp pll cache get [--format=<format>]
~~~

**OPTIONS**

	[--format=<format>]
		Accepted values: table, csv, json, count, yaml. Default: table

**EXAMPLES**

    $ wp pll cache get --format=json
    Success: There are 1 items in the languages cache:
    [{"term_id":2,"name":"Nederlands","slug":"nl","term_group":0,"term_taxonomy_id":2,"taxonomy":"language","description":"nl_NL","parent":0,"count":6259,"tl_term_id":3,"tl_term_taxonomy_id":3,"tl_count":42,"locale":"nl_NL","is_rtl":0,"flag_url":"","flag":"","home_url":"http:\/\/example.dev\/nl\/","search_url":"http:\/\/example.dev\/nl\/","host":null,"mo_id":"3","page_on_front":false,"page_for_posts":false,"filter":"raw","flag_code":""}]

    $ wp pll cache get --format=csv --quiet
    term_id,name,slug,term_group,term_taxonomy_id,taxonomy,description,parent,count,tl_term_id,tl_term_taxonomy_id,tl_count,locale,is_rtl,flag_url,flag,home_url,search_url,host,mo_id,page_on_front,page_for_posts,filter,flag_code
    2,Nederlands,nl,0,2,language,nl_NL,0,10,3,3,42,nl_NL,0,,,http://example.dev/nl/,http://example.dev/nl/,,3,,,raw,



### wp pll doctor

Troubleshoot Polylang.

~~~
wp pll doctor
~~~







### wp pll doctor check

List untranslated post and term objects (translatable).

~~~
wp pll doctor check [--format=<format>]
~~~

**OPTIONS**

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - json
		  - count
		  - yaml
		---

**EXAMPLES**

    wp pll doctor check



### wp pll doctor language

Mass install, update and prune core, theme and plugin language files.

~~~
wp pll doctor language 
~~~

**EXAMPLES**

    $ wp pll doctor language



### wp pll doctor recount

Recalculate number of posts assigned to each language taxonomy term.

~~~
wp pll doctor recount 
~~~

In instances where manual updates are made to the terms assigned to
posts in the database, the number of posts associated with a term
can become out-of-sync with the actual number of posts.

This command runs wp_update_term_count() on the language taxonomy's terms
to bring the count back to the correct value.

**EXAMPLES**

    wp pll doctor recount



### wp pll doctor translate

Translate untranslated posts and taxonomies in bulk.

~~~
wp pll doctor translate 
~~~

**EXAMPLES**

    wp pll doctor translate



### wp pll flag

Inspect and manage Polylang country flags.

~~~
wp pll flag
~~~







### wp pll flag list

List Polylang country flags.

~~~
wp pll flag list [--format=<format>]
~~~

**OPTIONS**

	[--format=<format>]
		Accepted values: table, csv, json, count, yaml. Default: table

**EXAMPLES**

    $ wp pll flag list
    $ wp pll flag list --format=csv



### wp pll flag set

Set Polylang country flag for language.

~~~
wp pll flag set <language-code> <flag-code>
~~~

Run `wp pll flag list` to get a list of valid flag values.
Pass an empty string as second parameter to delete the flag value.

**OPTIONS**

	<language-code>
		Language code (slug) for the language to update. Required.

	<flag-code>
		Valid flag code for the language to update. Required.

**EXAMPLES**

    # set flag for Dutch language
    $ wp pll flag set nl nl

    # delete flag for Dutch language
    $ wp pll flag set nl ""



### wp pll lang

Manage Polylang language taxonomy and taxonomy terms.

~~~
wp pll lang
~~~







### wp pll lang create

Create a language.

~~~
wp pll lang create <name> <language-code> <locale> [--rtl=<bool>] [--order=<int>] [--flag=<string>] [--no_default_cat=<bool>]
~~~

**OPTIONS**

	<name>
		Language name (used only for display). Required.

	<language-code>
		Language code (slug, ideally 2-letters ISO 639-1 language code). Required.

	<locale>
		WordPress locale. Required.

	[--rtl=<bool>]
		Right-to-left or left-to-right. Optional. Default: false

	[--order=<int>]
		Language order. Optional.

	[--flag=<string>]
		Country code, see flags.php. Optional.

	[--no_default_cat=<bool>]
		If set, no default category will be created for this language. Optional.

**EXAMPLES**

    $ wp pll lang create Français fr fr_FR

    $ wp pll lang create Arabic ar ar_AR --rtl=true --order=3

    $ wp pll lang create --prompt
    1/7 <name>: Français
    2/7 <language-code>: fr
    3/7 <locale>: fr_FR
    4/7 [--rtl=<bool>]: 0
    5/7 [--order=<int>]: 5
    6/7 [--flag=<string>]: fr
    7/7 [--no_default_cat=<bool>]:
    Success: Language added.



### wp pll lang delete

Delete one, some or all languages.

~~~
wp pll lang delete [<language-code>] [--all] [--keep_default]
~~~

Deletes Polylang languages and uninstalls core language packs if not in use by other languages.

**OPTIONS**

	[<language-code>]
		Comma-separated slugs of the languages to delete.

	[--all]
		Delete all languages

	[--keep_default]
		Whether to keep the default language.

**EXAMPLES**

    # delete the Afrikaans language and uninstall the `af` WordPress core language pack
    $ wp pll lang delete af
    Success: Language deleted. af (af)
    Success: Language uninstalled.

    # delete all languages including the default language
    $ wp pll lang delete --all

    # delete all languages except the default language
    $ wp pll lang delete --all --keep_default



### wp pll lang generate

Generate some languages.

~~~
wp pll lang generate [--count=<number>]
~~~

**OPTIONS**

	[--count=<number>]
		How many languages to generate. Default: 10

**EXAMPLES**

    wp pll lang generate --count=25



### wp pll lang get

Get a language.

~~~
wp pll lang get <language-code> [--field=<field>] [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

	<language-code>
		ID of the term to get

	[--field=<field>]
		Instead of returning the whole term, returns the value of a single field.

	[--fields=<fields>]
		Limit the output to specific fields. Defaults to all fields.

	[--format=<format>]
		Accepted values: table, json, csv, yaml. Default: table

**EXAMPLES**

    wp pll lang get en --format=json



### wp pll lang list

List installed languages.

~~~
wp pll lang list [--<field>=<value>] [--field=<field>] [--fields=<fields>] [--format=<format>] [--pll=<value>]
~~~

List installed languages as Polylang objects. Passing `--pll=0` will output the result of `wp term list language`

**OPTIONS**

	[--<field>=<value>]
		Filter by one or more fields (see get_terms() $args parameter for a list of fields).

	[--field=<field>]
		Prints the value of a single field for each term.

	[--fields=<fields>]
		Limit the output to specific object fields.

	[--format=<format>]
		Accepted values: table, csv, json, count, yaml. Default: table

	[--pll=<value>]
		Pass 0 to list languages as WP term objects.

**AVAILABLE FIELDS (POLYLANG OBJECT)**

These fields will be displayed by default for each term:

* term_id
* name
* slug
* term_group
* count
* locale
* is_rtl
* flag_code
---
* term_taxonomy_id
* taxonomy
* description
* parent
* tl_term_id
* tl_term_taxonomy_id
* tl_count
* flag_url
* flag
* home_url
* search_url
* host
* mo_id
* page_on_front
* page_for_posts
* filter

**AVAILABLE FIELDS (WP TERM OBJECT)**

These fields will be displayed by default for each term:

* term_id
* term_taxonomy_id
* name
* slug
* description
* parent
* count

There are no optionally available fields.

**EXAMPLES**

    # list languages as wp term objects
    $ wp pll lang list --pll=0

    # list properties of languages as Polylang objects
    $ wp pll lang list --fields=host,mo_id,flag_code



### wp pll lang update

Update a language.

~~~
wp pll lang update <language-code> [--name=<name>] [--slug=<slug>] [--locale=<locale>] [--rtl=<bool>] [--order=<int>] [--flag=<string>]
~~~

**OPTIONS**

	<language-code>
		Language code (slug) for the language to update. Required.

	[--name=<name>]
		A new name for the language (used only for display). Optional.

	[--slug=<slug>]
		A new language code for the language (ideally 2-letters ISO 639-1 language code). Optional.

	[--locale=<locale>]
		Optional. A new WordPress locale for the language.

	[--rtl=<bool>]
		Optional. RTL or LTR, 1 or 0

	[--order=<int>]
		Optional. A new order (term_group) value for the language.

	[--flag=<string>]
		Optional. A new flag (country code) for the language, see flags.php.

**EXAMPLES**

    wp pll lang update en --name=English --order=15



### wp pll lang url

Get the URL for a language.

~~~
wp pll lang url <language-code>
~~~

**OPTIONS**

	<language-code>
		The language code (slug) to get the URL for. Required.

**EXAMPLES**

    wp pll lang url en
    wp pll lang url es



### wp pll menu

Manage the WP Nav Menus.

~~~
wp pll menu
~~~







### wp pll menu create

Create a new menu for each language, AND assign it to a location.

~~~
wp pll menu create <menu-name> <location> [--porcelain]
~~~

**OPTIONS**

	<menu-name>
		A descriptive name for the menu.

	<location>
		Location’s slug.

	[--porcelain]
		Output just the new menu ids.

**EXAMPLES**

    $ wp pll menu create "Primary Menu" primary
    Success: Assigned location to menu.
    Success: Assigned location to menu.
    Success: Assigned location to menu.

    $ wp pll menu create "Secondary Menu" secondary --porcelain
    21 22 23



### wp pll option

Inspect and manage Polylang settings.

~~~
wp pll option
~~~







### wp pll option default

Gets or sets the default language.

~~~
wp pll option default [<language-code>]
~~~

**OPTIONS**

	[<language-code>]
		Optional. The language code (slug) to set as default.

**EXAMPLES**

    $ wp pll option default
    $ wp pll option default nl



### wp pll option get

Get Polylang settings.

~~~
wp pll option get <option_name> [--format=<format>]
~~~

**OPTIONS**

	<option_name>
		Option name. Use the options subcommand to get a list of accepted values. Required.

	[--format=<format>]
		Get value in a particular format.
		---
		default: var_export
		options:
		  - var_export
		  - json
		  - yaml
		---

**EXAMPLES**

    $ wp pll option get default_lang



### wp pll option list

List Polylang settings.

~~~
wp pll option list [--format=<format>]
~~~

**OPTIONS**

	[--format=<format>]
		Accepted values: table, csv, json, count, yaml. Default: table

**EXAMPLES**

    $ wp pll option list
    $ wp pll option list --format=csv



### wp pll option reset

Reset Polylang settings.

~~~
wp pll option reset 
~~~

**EXAMPLES**

    $ wp pll option reset



### wp pll option sync

Enable post meta syncing across languages.

~~~
wp pll option sync <item>
~~~

Accepted values:

* taxonomies
* post_meta
* comment_status
* ping_status
* sticky_posts
* post_date
* post_format
* post_parent
* _wp_page_template
* menu_order
* _thumbnail_id

**OPTIONS**

	<item>
		Item, or comma-separated list of items, to sync. Required.

**EXAMPLES**

    $ wp pll option sync taxonomies,post_meta
    Success: Polylang `sync` option updated.



### wp pll option unsync

Disable post meta syncing across languages.

~~~
wp pll option unsync <item>
~~~

Accepted values:

* taxonomies
* post_meta
* comment_status
* ping_status
* sticky_posts
* post_date
* post_format
* post_parent
* _wp_page_template
* menu_order
* _thumbnail_id

**OPTIONS**

	<item>
		Item, or comma-separated list of items, to unsync. Required.

**EXAMPLES**

    $ wp pll option unsync post_format,_wp_page_template
    Success: Polylang `sync` option updated.



### wp pll option update

Update Polylang settings.

~~~
wp pll option update <option_name> <new_value>
~~~

**OPTIONS**

	<option_name>
		Option name. Use the options subcommand to get a list of accepted values. Required.

	<new_value>
		New value for the option. Required.

**EXAMPLES**

    $ wp pll option update default_lang nl



### wp pll post

Manage posts and their translations.

~~~
wp pll post
~~~







### wp pll post count

Count posts for a language.

~~~
wp pll post count <language-code> [--post_type=<post_type>]
~~~

**OPTIONS**

	<language-code>
		The language code (slug) to get the post count for. Required.

	[--post_type=<post_type>]
		One or more post types to get the count for for. Default: post. Optional.

**EXAMPLES**

    wp pll post count nl
    wp pll post count es --post_type=page



### wp pll post generate

Generate some posts and their translations.

~~~
wp pll post generate [--count=<number>] [--post_type=<type>] [--post_status=<status>] [--post_author=<login>] [--post_date=<yyyy-mm-dd>] [--post_content] [--max_depth=<number>] [--format=<format>]
~~~

Creates a specified number of sets of new posts with dummy data.

**OPTIONS**

	[--count=<number>]
		How many posts to generate?
		---
		default: 5
		---

	[--post_type=<type>]
		The type of the generated posts.
		---
		default: post
		---

	[--post_status=<status>]
		The status of the generated posts.
		---
		default: publish
		---

	[--post_author=<login>]
		The author of the generated posts.
		---
		default:
		---

	[--post_date=<yyyy-mm-dd>]
		The date of the generated posts. Default: current date

	[--post_content]
		If set, the command reads the post_content from STDIN.

	[--max_depth=<number>]
		For hierarchical post types, generate child posts down to a certain depth.
		---
		default: 1
		---

	[--format=<format>]
		Render output in a particular format.
		---
		default: ids
		options:
		  - progress
		  - ids
		---

**EXAMPLES**

    # Generate posts.
    $ wp pll post generate --count=10 --post_type=page --post_date=1999-01-04
    Generating posts  100% [================================================] 0:01 / 0:04

    # Generate posts with fetched content.
    $ curl http://loripsum.net/api/5 | wp pll post generate --post_content --count=10
      % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                     Dload  Upload   Total   Spent    Left  Speed
    100  2509  100  2509    0     0    616      0  0:00:04  0:00:04 --:--:--   616
    Generating posts  100% [================================================] 0:01 / 0:04

    # Add meta to every generated posts.
    $ wp pll post generate --format=ids | xargs -d ' ' -I % wp post meta add % foo bar
    Success: Added custom field.
    Success: Added custom field.
    Success: Added custom field.



### wp pll post create

Create a new post and its translations.

~~~
wp pll post create --post_type=<type> [--<field>=<value>] [--stdin] [--porcelain]
~~~

**OPTIONS**

	--post_type=<type>
		The type of the new posts. Required.

	[--<field>=<value>]
		Associative args for the new posts. See wp_insert_post(). These values will take precendence over input from STDIN.

	[--stdin]
		Read structured JSON from STDIN.

	[--porcelain]
		Output just the new post ids.

**EXAMPLES**

    # Create a post and duplicate it to all languages
    $ wp pll post create --post_type=page --post_title="Blog" --post_status=publish
    Success: Created and linked 2 posts of the page post type.

    # Create a post and its translations using structured JSON
    $ echo '{"nl":{"post_title":"Dutch title","post_content":"Dutch content"},"de":{"post_title":"German title","post_content":"German content"}}' | wp pll post create --post_type=post --stdin
    Success: Created and linked 2 posts of the post post type.



### wp pll post get

List a post and its translations, or get a post for a language.

~~~
wp pll post get <post_id> [<language-code>] [--api]
~~~

**OPTIONS**

	<post_id>
		Post ID of the post to get. Required.

	[<language-code>]
		The language code (slug) to get the post ID for, when using the --api flag. Optional.

	[--api]
		Use the Polylang API function pll_get_post()

**EXAMPLES**

    wp pll post get 12
    wp pll post get 1 es --api



### wp pll post update

Update one or more existing posts and their translations.

~~~
wp pll post update <id>... [<file>] --<field>=<value> [--defer-term-counting]
~~~

**OPTIONS**

	<id>...
		One or more IDs of posts to update.

	[<file>]
		Read post content from <file>. If this value is present, the
		    `--post_content` argument will be ignored.

  Passing `-` as the filename will cause post content to
  be read from STDIN.

	--<field>=<value>
		One or more fields to update. See wp_update_post().

	[--defer-term-counting]
		Recalculate term count in batch, for a performance boost.

**EXAMPLES**

    $ wp pll post update 13 --comment_status=closed
    Success: Updated post 13.



### wp pll post delete

Delete a post and its translations.

~~~
wp pll post delete <post_id> [--force] [--defer-term-counting]
~~~

**OPTIONS**

	<post_id>
		Post ID of the a translated post to delete. Required.

	[--force]
		Skip the trash bin.

	[--defer-term-counting]
		Recalculate term count in batch, for a performance boost.

**EXAMPLES**

    wp pll post delete 32



### wp pll post duplicate

Duplicate a post to one or more languages.

~~~
wp pll post duplicate <post_id> [<language-code>]
~~~

Syncs metadata and taxonomy terms, based on Polylang settings. Run `wp pll option list` to inspect current settings.

**OPTIONS**

	<post_id>
		Post ID of the post to duplicate. Required.

	[<language-code>]
		Language code (slug), or comma-separated list of language codes, to duplicate the post to. Omit to duplicate to all languages. Optional.

**EXAMPLES**

    # Duplicate post 23 (Dutch) to German
    $ wp pll post duplicate 23 de
    Success: Created post 68 (de) < post 23 (nl)

    # Duplicate post 23 (Dutch) to all languages (Dutch and Spanish)
    $ wp pll post duplicate 23
    Success: Updated post 68 (de) < post 23 (nl)
    Success: Created post 69 (es) < post 23 (nl)



### wp pll post list

Get a list of posts in a language.

~~~
wp pll post list <language-code> [--<field>=<value>] [--field=<field>] [--fields=<fields>] [--format=<format>]
~~~

NB: Like Polylang, this command passes a `lang` parameter to WP_Query,
i.e. `wp post list --lang=<language-code>`.

**OPTIONS**

	<language-code>
		The language code (slug) to get the post count for. Required.

	[--<field>=<value>]
		One or more args to pass to WP_Query.

	[--field=<field>]
		Prints the value of a single field for each post.

	[--fields=<fields>]
		Limit the output to specific object fields.

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - ids
		  - json
		  - count
		  - yaml
		---

**AVAILABLE FIELDS**

These fields will be displayed by default for each post:

* ID
* post_title
* post_name
* post_date
* post_status

These fields are optionally available:

* post_author
* post_date_gmt
* post_content
* post_excerpt
* comment_status
* ping_status
* post_password
* to_ping
* pinged
* post_modified
* post_modified_gmt
* post_content_filtered
* post_parent
* guid
* menu_order
* post_type
* post_mime_type
* comment_count
* filter
* url

**EXAMPLES**

    wp pll post list nl

    # List post
    $ wp pll post list es --field=ID
    568
    829
    1329
    1695

    # List posts in JSON
    $ wp pll post list en-gb --post_type=post --posts_per_page=5 --format=json
    [{"ID":1,"post_title":"Hello world!","post_name":"hello-world","post_date":"2015-06-20 09:00:10","post_status":"publish"},{"ID":1178,"post_title":"Markup: HTML Tags and Formatting","post_name":"markup-html-tags-and-formatting","post_date":"2013-01-11 20:22:19","post_status":"draft"}]

    # List all pages
    $ wp pll post list nl --post_type=page --fields=post_title,post_status
    +-------------+-------------+
    | post_title  | post_status |
    +-------------+-------------+
    | Sample Page | publish     |
    +-------------+-------------+

    # List ids of all pages and posts
    $ wp pll post list es --post_type=page,post --format=ids
    15 25 34 37 198

    # List given posts
    $ wp pll post list nl --post__in=1,3
    +----+--------------+-------------+---------------------+-------------+
    | ID | post_title   | post_name   | post_date           | post_status |
    +----+--------------+-------------+---------------------+-------------+
    | 1  | Hello world! | hello-world | 2016-06-01 14:31:12 | publish     |
    +----+--------------+-------------+---------------------+-------------+



### wp pll post-type

Inspect and manage WordPress post types and their translation status.

~~~
wp pll post-type
~~~







### wp pll post-type disable

Disable translation for post types.

~~~
wp pll post-type disable <post_types>
~~~

**OPTIONS**

	<post_types>
		One or a comma-separated list of post types to disable translation for.

**EXAMPLES**

    wp pll post-type disable book



### wp pll post-type enable

Enable translation for post types.

~~~
wp pll post-type enable <post_types>
~~~

**OPTIONS**

	<post_types>
		One or a comma-separated list of post types to enable translation for.

**EXAMPLES**

    wp pll post-type enable book



### wp pll post-type list

List post types with their translation status.

~~~
wp pll post-type list 
~~~

**EXAMPLES**

    wp pll post-type list



### wp pll plugin uninstall

Uninstall Polylang and optionally remove all data.

~~~
wp pll plugin uninstall [--force] [--skip-delete]
~~~

**OPTIONS**

	[--force]
		Ignores the Polylang `uninstall` setting and force deletes all data.

	[--skip-delete]
		If set, the plugin files will not be deleted. Only the uninstall procedure
		will be run.

**EXAMPLES**

    $ wp pll uninstall
    $ wp pll uninstall --force
    $ wp pll uninstall --force --skip-delete



### wp pll string

Inspect and manage Polylang string translations.

~~~
wp pll string
~~~







### wp pll string list

List string translations.

~~~
wp pll string list [<language-code>] [--fields=<value>] [--format=<format>] [--s=<value>] [--orderby=<value>] [--order=<value>]
~~~

**OPTIONS**

	[<language-code>]
		The language code (slug) to get the string translations for. Optional.

	[--fields=<value>]
		Limit the output to specific object fields. Valid values are: name, string, context, multiline, translations, row.

	[--format=<format>]
		Accepted values: table, csv, json, count, yaml. Default: table

	[--s=<value>]
		Search for a string in `name` and `string` fields.

	[--orderby=<value>]
		Define which column to sort.

	[--order=<value>]
		Define the order of the results, asc or desc.

**EXAMPLES**

    $ wp pll string list --s="WordPress site"

    $ wp pll string list --order=asc --orderby=string

    $ wp pll string list de --fields=string,translations

    $ wp pll string list es --format=csv



### wp pll taxonomy

Inspect and manage WordPress taxonomies and their translation status.

~~~
wp pll taxonomy
~~~







### wp pll taxonomy disable

Disable translation for taxonomies.

~~~
wp pll taxonomy disable <taxonomies>
~~~

**OPTIONS**

	<taxonomies>
		Taxonomy or comma-separated list of taxonomies to disable translation for.

**EXAMPLES**

    wp pll taxonomy disable genre



### wp pll taxonomy enable

Enable translation for taxonomies.

~~~
wp pll taxonomy enable <taxonomies>
~~~

**OPTIONS**

	<taxonomies>
		Taxonomy or comma-separated list of taxonomies to enable translation for.

**EXAMPLES**

    wp pll taxonomy enable genre



### wp pll taxonomy list

List taxonomies with their translation status.

~~~
wp pll taxonomy list [--format=<format>]
~~~

**OPTIONS**

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - ids
		  - json
		  - count
		  - yaml
		---

**EXAMPLES**

    wp pll taxonomy list



### wp pll term

Inspect and manage WordPress taxonomy terms and their translations.

~~~
wp pll term
~~~







### wp pll term get

Get details about a translated term.

~~~
wp pll term get <taxonomy> <term-id> [--field=<field>] [--fields=<fields>] [--format=<format>] [--api]
~~~

**OPTIONS**

	<taxonomy>
		Taxonomy of the term to get

	<term-id>
		ID of the term to get

	[--field=<field>]
		Instead of returning the whole term, returns the value of a single field.

	[--fields=<fields>]
		Limit the output to specific fields. Defaults to all fields.

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - json
		  - yaml
		---

	[--api]
		Use the Polylang API function pll_get_term_translations()

**EXAMPLES**

    # Get details about a category with term ID 18.
    $ wp pll term get category 18



### wp pll term duplicate

Duplicate a taxonomy term to one or more languages.

~~~
wp pll term duplicate <taxonomy> <term-id> [<language-code>]
~~~

**OPTIONS**

	<taxonomy>
		Taxonomy of the term to duplicate

	<term-id>
		ID of the term to duplicate

	[<language-code>]
		Language code (slug), or comma-separated list of language codes, to duplicate the term to. Omit to duplicate to all languages. Optional.

**EXAMPLES**

    # Duplicate term 18 of the category taxonomy to all other languages.
    $ wp pll term duplicate category 18



### wp pll term delete

Delete an existing taxonomy term and its translations.

~~~
wp pll term delete <taxonomy> <term-id>...
~~~

Errors if the term doesn't exist, or there was a problem in deleting it.

**OPTIONS**

	<taxonomy>
		Taxonomy of the term to delete.

	<term-id>...
		One or more IDs of terms to delete.

**EXAMPLES**

    # Delete a term (English) and its translations (Spanish, French)
    $ wp pll term delete post_tag 56
    Deleted post_tag 56.
    Deleted post_tag 57.
    Deleted post_tag 58.
    Success: Deleted 3 of 3 terms.



### wp pll term list

Get a list of taxonomy terms for a language.

~~~
wp pll term list <taxonomy> <language-code> [--<field>=<value>] [--field=<field>] [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

	<taxonomy>
		List terms of one or more taxonomies. Required.

	<language-code>
		The language code (slug) to get the taxonomy terms for. Required.

	[--<field>=<value>]
		Filter by one or more fields (see get_terms() $args parameter for a list of fields).

	[--field=<field>]
		Prints the value of a single field for each term.

	[--fields=<fields>]
		Limit the output to specific object fields.

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - ids
		  - json
		  - count
		  - yaml
		---

**AVAILABLE FIELDS**

These fields will be displayed by default for each term:

* term_id
* term_taxonomy_id
* name
* slug
* description
* parent
* count

These fields are optionally available:

* url

**EXAMPLES**

    # List post categories
    $ wp pll term list color nl --format=csv
    term_id,term_taxonomy_id,name,slug,description,parent,count
    2,2,Rood,rood,,0,1
    3,3,Blauw,blauw,,0,1

    # List post tags
    $ wp pll term list post_tag en --fields=name,slug
    +-----------+-------------+
    | name      | slug        |
    +-----------+-------------+
    | Articles  | articles    |
    | aside     | aside       |
    +-----------+-------------+



### wp pll term generate

Generate some taxonomy terms and their translations.

~~~
wp pll term generate <taxonomy> [--count=<number>] [--max_depth=<number>] [--format=<format>]
~~~

Creates a specified number of sets of new terms and their translations with dummy data.

**OPTIONS**

	<taxonomy>
		The taxonomy for the generated terms.

	[--count=<number>]
		How many sets of terms to generate?
		---
		default: 5
		---

	[--max_depth=<number>]
		Generate child terms down to a certain depth.
		---
		default: 1
		---

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - json
		  - yaml
		  - ids
		---

**EXAMPLES**

    # Generate some post categories, and translations.
    $ wp pll term generate category --count=3 --format=ids
    115 116 117 118 119 120

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/diggy/polylang-cli/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/diggy/polylang-cli/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/diggy/polylang-cli/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Development

### Behat Tests
To run the Behat tests for polylang-cli, `cd` into the package directory and run `$ ./vendor/bin/behat --expand` from the command line. To run a specific group of tests use the `tags` parameter; e.g.: `$ ./vendor/bin/behat --expand --tags @pll-lang`


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
