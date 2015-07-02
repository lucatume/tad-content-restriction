<?php


interface trc_Core_QueryInterface {

	public static function instance( $query );

	/**
	 * Initiates object properties and sets default values.
	 *
	 * @since  1.5.0
	 * @access public
	 */
	public function init();

	/**
	 * Reparse the query vars.
	 *
	 * @since  1.5.0
	 * @access public
	 */
	public function parse_query_vars();

	/**
	 * Fills in the query variables, which do not exist within the parameter.
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param array $array Defined query variables.
	 *
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
	public function fill_query_vars( $array );

	/**
	 * Parse a query string and set query type booleans.
	 *
	 * @since  1.5.0
	 * @since  4.2.0 Introduced the ability to order by specific clauses of a `$meta_query`, by passing the clause's
	 *              array key to `$orderby`.
	 * @access public
	 *
	 * @param string|array $query                  {
	 *                                             Optional. Array or string of Query parameters.
	 *
	 * @type int           $attachment_id          Attachment post ID. Used for 'attachment' post_type.
	 * @type int|string    $author                 Author ID, or comma-separated list of IDs.
	 * @type string        $author_name            User 'user_nicename'.
	 * @type array         $author__in             An array of author IDs to query from.
	 * @type array         $author__not_in         An array of author IDs not to query from.
	 * @type bool          $cache_results          Whether to cache post information. Default true.
	 * @type int|string    $cat                    Category ID or comma-separated list of IDs (this or any children).
	 * @type array         $category__and          An array of category IDs (AND in).
	 * @type array         $category__in           An array of category IDs (OR in, no children).
	 * @type array         $category__not_in       An array of category IDs (NOT in).
	 * @type string        $category_name          Use category slug (not name, this or any children).
	 * @type int           $comments_per_page      The number of comments to return per page.
	 *                                                 Default 'comments_per_page' option.
	 * @type int|string    $comments_popup         Whether the query is within the comments popup. Default empty.
	 * @type array         $date_query             An associative array of WP_Date_Query arguments.
	 *                                                 {@see WP_Date_Query::__construct()}
	 * @type int           $day                    Day of the month. Default empty. Accepts numbers 1-31.
	 * @type bool          $exact                  Whether to search by exact keyword. Default false.
	 * @type string|array  $fields                 Which fields to return. Single field or all fields (string),
	 *                                                 or array of fields. 'id=>parent' uses 'id' and 'post_parent'.
	 *                                                 Default all fields. Accepts 'ids', 'id=>parent'.
	 * @type int           $hour                   Hour of the day. Default empty. Accepts numbers 0-23.
	 * @type int|bool      $ignore_sticky_posts    Whether to ignore sticky posts or not. Setting this to false
	 *                                                 excludes stickies from 'post__not_in'. Accepts 1|true, 0|false.
	 *                                                 Default 0|false.
	 * @type int           $m                      Combination YearMonth. Accepts any four-digit year and month
	 *                                                 numbers 1-12. Default empty.
	 * @type string        $meta_compare           Comparison operator to test the 'meta_value'.
	 * @type string        $meta_key               Custom field key.
	 * @type array         $meta_query             An associative array of WP_Meta_Query arguments.
	 *                                                 {@see WP_Meta_Query->queries}
	 * @type string        $meta_value             Custom field value.
	 * @type int           $meta_value_num         Custom field value number.
	 * @type int           $menu_order             The menu order of the posts.
	 * @type int           $monthnum               The two-digit month. Default empty. Accepts numbers 1-12.
	 * @type string        $name                   Post slug.
	 * @type bool          $nopaging               Show all posts (true) or paginate (false). Default false.
	 * @type bool          $no_found_rows          Whether to skip counting the total rows found. Enabling can improve
	 *                                                 performance. Default false.
	 * @type int           $offset                 The number of posts to offset before retrieval.
	 * @type string        $order                  Designates ascending or descending order of posts. Default 'DESC'.
	 *                                                 Accepts 'ASC', 'DESC'.
	 * @type string|array  $orderby                Sort retrieved posts by parameter. One or more options may be
	 *                                                 passed. To use 'meta_value', or 'meta_value_num',
	 *                                                 'meta_key=keyname' must be also be defined. To sort by a
	 *                                                 specific `$meta_query` clause, use that clause's array key.
	 *                                                 Default 'date'. Accepts 'none', 'name', 'author', 'date',
	 *                                                 'title', 'modified', 'menu_order', 'parent', 'ID', 'rand',
	 *                                                 'comment_count', 'meta_value', 'meta_value_num', and the
	 *                                                 array keys of `$meta_query`.
	 * @type int           $p                      Post ID.
	 * @type int           $page                   Show the number of posts that would show up on page X of a
	 *                                                 static front page.
	 * @type int           $paged                  The number of the current page.
	 * @type int           $page_id                Page ID.
	 * @type string        $pagename               Page slug.
	 * @type string        $perm                   Show posts if user has the appropriate capability.
	 * @type array         $post__not_in               An array of post IDs to retrieve, sticky posts will be included
	 * @type string        $post_mime_type         The mime type of the post. Used for 'attachment' post_type.
	 * @type array         $post__not_in           An array of post IDs not to retrieve. Note: a string of comma-
	 *                                                 separated IDs will NOT work.
	 * @type int           $post_parent            Page ID to retrieve child pages for. Use 0 to only retrieve
	 *                                                 top-level pages.
	 * @type array         $post_parent__in        An array containing parent page IDs to query child pages from.
	 * @type array         $post_parent__not_in    An array containing parent page IDs not to query child pages from.
	 * @type string|array  $post_type              A post type slug (string) or array of post type slugs.
	 *                                                 Default 'any' if using 'tax_query'.
	 * @type string|array  $post_status            A post status (string) or array of post statuses.
	 * @type int           $posts_per_page         The number of posts to query for. Use -1 to request all posts.
	 * @type int           $posts_per_archive_page The number of posts to query for by archive page. Overrides
	 *                                                 'posts_per_page' when is_archive(), or is_search() are true.
	 * @type string        $s                      Search keyword.
	 * @type int           $second                 Second of the minute. Default empty. Accepts numbers 0-60.
	 * @type array         $search_terms           Array of search terms.
	 * @type bool          $sentence               Whether to search by phrase. Default false.
	 * @type bool          $suppress_filters       Whether to suppress filters. Default false.
	 * @type string        $tag                    Tag slug. Comma-separated (either), Plus-separated (all).
	 * @type array         $tag__and               An array of tag ids (AND in).
	 * @type array         $tag__in                An array of tag ids (OR in).
	 * @type array         $tag__not_in            An array of tag ids (NOT in).
	 * @type int           $tag_id                 Tag id or comma-separated list of IDs.
	 * @type array         $tag_slug__and          An array of tag slugs (AND in).
	 * @type array         $tag_slug__in           An array of tag slugs (OR in). unless 'ignore_sticky_posts' is
	 *                                                 true. Note: a string of comma-separated IDs will NOT work.
	 * @type array         $tax_query              An associative array of WP_Tax_Query arguments.
	 *                                                 {@see WP_Tax_Query->queries}
	 * @type bool          $update_post_meta_cache Whether to update the post meta cache. Default true.
	 * @type bool          $update_post_term_cache Whether to update the post term cache. Default true.
	 * @type int           $w                      The week number of the year. Default empty. Accepts numbers 0-53.
	 * @type int           $year                   The four-digit year. Default empty. Accepts any four-digit year.
	 * }
	 */
	public function parse_query( $query = '' );

	/**
	 * Parses various taxonomy related query vars.
	 *
	 * For BC, this method is not marked as protected. See [28987].
	 *
	 * @access protected
	 * @since  3.1.0
	 *
	 * @param array &$q The query variables
	 */
	public function parse_tax_query( &$q );

	/**
	 * Sets the 404 property and saves whether query is feed.
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public function set_404();

	/**
	 * Retrieve query variable.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed  $default   Value to return if the query variable is not set. Default ''.
	 *
	 * @return mixed
	 */
	public function get( $query_var, $default = '' );

	/**
	 * Set query variable.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed  $value     Query variable value.
	 */
	public function set( $query_var, $value );

	/**
	 * Retrieve the posts based on query variables.
	 *
	 * There are a few filters and actions that can be used to modify the post
	 * database query.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @return array List of posts.
	 */
	public function get_posts();

	/**
	 * Set up the next post and iterate current post index.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @return WP_Post Next post.
	 */
	public function next_post();

	/**
	 * Sets up the current post.
	 *
	 * Retrieves the next post, sets up the post, sets the 'in the loop'
	 * property to true.
	 *
	 * @since  1.5.0
	 * @access public
	 */
	public function the_post();

	/**
	 * Whether there are more posts available in the loop.
	 *
	 * Calls action 'loop_end', when the loop is complete.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @return bool True if posts are available, false if end of loop.
	 */
	public function have_posts();

	/**
	 * Rewind the posts and reset post index.
	 *
	 * @since  1.5.0
	 * @access public
	 */
	public function rewind_posts();

	/**
	 * Iterate current comment index and return comment object.
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return object Comment object.
	 */
	public function next_comment();

	/**
	 * Sets up the current comment.
	 *
	 * @since  2.2.0
	 * @access public
	 * @global object $comment Current comment.
	 */
	public function the_comment();

	/**
	 * Whether there are more comments available.
	 *
	 * Automatically rewinds comments when finished.
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return bool True, if more comments. False, if no more posts.
	 */
	public function have_comments();

	/**
	 * Rewind the comments, resets the comment index and comment to first.
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function rewind_comments();

	/**
	 * Sets up the WordPress query by parsing query string.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @param string $query URL query string.
	 *
	 * @return array List of posts.
	 */
	public function query( $query );

	/**
	 * Retrieve queried object.
	 *
	 * If queried object is not set, then the queried object will be set from
	 * the category, tag, taxonomy, posts page, single post, page, or author
	 * query variable. After it is set up, it will be returned.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @return object
	 */
	public function get_queried_object();

	/**
	 * Retrieve ID of the current queried object.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_queried_object_id();

	/**
	 * Constructor.
	 *
	 * Sets up the WordPress query, if parameter is not empty.
	 *
	 * @since  1.5.0
	 * @access public
	 *
	 * @param string|array $query URL query string or array of vars.
	 */
	public function __construct( $query = '' );

	/**
	 * Make private properties readable for backwards compatibility.
	 *
	 * @since  4.0.0
	 * @access public
	 *
	 * @param string $name Property to get.
	 *
	 * @return mixed Property.
	 */
	public function __get( $name );

	/**
	 * Make private properties checkable for backwards compatibility.
	 *
	 * @since  4.0.0
	 * @access public
	 *
	 * @param string $name Property to check if set.
	 *
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name );

	/**
	 * Make private/protected methods readable for backwards compatibility.
	 *
	 * @since  4.0.0
	 * @access public
	 *
	 * @param callable $name      Method to call.
	 * @param array    $arguments Arguments to pass when calling.
	 *
	 * @return mixed|bool Return value of the callback, false otherwise.
	 */
	public function __call( $name, $arguments );

	/**
	 * Is the query for an existing archive page?
	 *
	 * Month, Year, Category, Author, Post Type archive...
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_archive();

	/**
	 * Is the query for an existing post type archive page?
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post_types Optional. Post type or array of posts types to check against.
	 *
	 * @return bool
	 */
	public function is_post_type_archive( $post_types = '' );

	/**
	 * Is the query for an existing attachment page?
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $attachment Attachment ID, title, slug, or array of such.
	 *
	 * @return bool
	 */
	public function is_attachment( $attachment = '' );

	/**
	 * Is the query for an existing author archive page?
	 *
	 * If the $author parameter is specified, this function will additionally
	 * check if the query is for one of the authors specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 *
	 * @return bool
	 */
	public function is_author( $author = '' );

	/**
	 * Is the query for an existing category archive page?
	 *
	 * If the $category parameter is specified, this function will additionally
	 * check if the query is for one of the categories specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 *
	 * @return bool
	 */
	public function is_category( $category = '' );

	/**
	 * Is the query for an existing tag archive page?
	 *
	 * If the $tag parameter is specified, this function will additionally
	 * check if the query is for one of the tags specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 *
	 * @return bool
	 */
	public function is_tag( $tag = '' );

	/**
	 * Is the query for an existing taxonomy archive page?
	 *
	 * If the $taxonomy parameter is specified, this function will additionally
	 * check if the query is for that specific $taxonomy.
	 *
	 * If the $term parameter is specified in addition to the $taxonomy parameter,
	 * this function will additionally check if the query is for one of the terms
	 * specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $taxonomy Optional. Taxonomy slug or slugs.
	 * @param mixed $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 *
	 * @return bool
	 */
	public function is_tax( $taxonomy = '', $term = '' );

	/**
	 * Whether the current URL is within the comments popup window.
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_comments_popup();

	/**
	 * Is the query for an existing date archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_date();

	/**
	 * Is the query for an existing day archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_day();

	/**
	 * Is the query for a feed?
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $feeds Optional feed types to check.
	 *
	 * @return bool
	 */
	public function is_feed( $feeds = '' );

	/**
	 * Is the query for a comments feed?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_comment_feed();

	/**
	 * Is the query for the front page of the site?
	 *
	 * This is for what is displayed at your site's main URL.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_on_front'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true when viewing that page.
	 *
	 * Otherwise the same as @see WP_Query::is_home()
	 *
	 * @since 3.1.0
	 *
	 * @return bool True, if front of site.
	 */
	public function is_front_page();

	/**
	 * Is the query for the blog homepage?
	 *
	 * This is the page which shows the time based blog content of your site.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_for_posts'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true only on the page you set as the "Posts page".
	 *
	 * @see   WP_Query::is_front_page()
	 *
	 * @since 3.1.0
	 *
	 * @return bool True if blog view homepage.
	 */
	public function is_home();

	/**
	 * Is the query for an existing month archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_month();

	/**
	 * Is the query for an existing single page?
	 *
	 * If the $page parameter is specified, this function will additionally
	 * check if the query is for one of the pages specified.
	 *
	 * @see   WP_Query::is_single()
	 * @see   WP_Query::is_singular()
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $page Page ID, title, slug, path, or array of such.
	 *
	 * @return bool
	 */
	public function is_page( $page = '' );

	/**
	 * Is the query for paged result and not for the first page?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_paged();

	/**
	 * Is the query for a post or page preview?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_preview();

	/**
	 * Is the query for the robots file?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_robots();

	/**
	 * Is the query for a search?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_search();

	/**
	 * Is the query for an existing single post?
	 *
	 * Works for any post type, except attachments and pages
	 *
	 * If the $post parameter is specified, this function will additionally
	 * check if the query is for one of the Posts specified.
	 *
	 * @see   WP_Query::is_page()
	 * @see   WP_Query::is_singular()
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post Post ID, title, slug, path, or array of such.
	 *
	 * @return bool
	 */
	public function is_single( $post = '' );

	/**
	 * Is the query for an existing single post of any post type (post, attachment, page, ... )?
	 *
	 * If the $post_types parameter is specified, this function will additionally
	 * check if the query is for one of the Posts Types specified.
	 *
	 * @see   WP_Query::is_page()
	 * @see   WP_Query::is_single()
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post_types Optional. Post Type or array of Post Types
	 *
	 * @return bool
	 */
	public function is_singular( $post_types = '' );

	/**
	 * Is the query for a specific time?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_time();

	/**
	 * Is the query for a trackback endpoint call?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_trackback();

	/**
	 * Is the query for an existing year archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_year();

	/**
	 * Is the query a 404 (returns no results)?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_404();

	/**
	 * Is the query the main query?
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public function is_main_query();

	/**
	 * Set up global post data.
	 *
	 * @since 4.1.0
	 *
	 * @param WP_Post $post Post data.
	 *
	 * @return bool True when finished.
	 */
	public function setup_postdata( $post );

	/**
	 * After looping through a nested query, this function
	 * restores the $post global to the current post in this query.
	 *
	 * @since 3.7.0
	 */
	public function reset_postdata();
}