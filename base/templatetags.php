<?php
/**
 * Infinity Theme: template tags
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package infinity
 * @subpackage base
 * @since 1.0
 */

/**
 * Print a basic title
 */
function infinity_base_title()
{
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', infinity_text_domain ), max( $paged, $page ) );
}

/**
 * Show author box (if on an author page)
 */
function infinity_base_author_box()
{
	if ( is_author() ):
		// queue the first post, that way we know who the author is when we
		// try to get their name, URL, description, avatar, etc.
		if ( have_posts() ):
			the_post();

			// if a user has filled out their description, show a bio on their entries.
			if ( get_the_author_meta( 'description' ) ):
				infinity_get_template_part( 'templates/parts/author-box' );
			endif;

			// reset the loop so we don't break later queries
			rewind_posts();
		endif;
	endif;
}

/**
 * Show sidebars based on page type (including BP components)
 */
function infinity_base_sidebars()
{
		if ( is_page() ) {
			global $post;
			if ( function_exists('bp_is_member') && bp_is_member() ) {
				if ( is_active_sidebar( 'member-sidebar' ) ) {
					dynamic_sidebar( 'member-sidebar' );
				} else { ?>
				<div class="widget"><h4>BP Member Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
           } elseif ( function_exists('bp_is_page') && bp_is_page(BP_GROUPS_SLUG) ) {
                if ( is_active_sidebar( 'groups-sidebar' ) ) {
                    dynamic_sidebar( 'groups-sidebar');
				} else { ?>
				<div class="widget"><h4>BP Group Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
            } elseif ( function_exists('bp_is_page') && bp_is_page(BP_FORUMS_SLUG) ) {
                if ( is_active_sidebar( 'forums-sidebar' ) ) {
                    dynamic_sidebar( 'forums-sidebar');
				} else { ?>
				<div class="widget"><h4>BP Forums Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
            } elseif ( function_exists('bp_is_page') && bp_is_page(BP_BLOGS_SLUG) ) {
                if ( is_active_sidebar( 'blogs-sidebar' ) ) {
                    dynamic_sidebar( 'blogs-sidebar');
				} else { ?>
				<div class="widget"><h4>BP Blogs Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
			} elseif( is_single() ) {
				if ( is_active_sidebar( 'single-sidebar' ) ) {
					dynamic_sidebar( 'single-sidebar');
				} else { ?>
				<div class="widget"><h4>Single Posts Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
			} elseif( is_front_page() ) {
				if ( is_active_sidebar( 'activity-sidebar' ) ) {
					dynamic_sidebar( 'activity-sidebar' );
				} else { ?>
				<div class="widget"><h4>Home Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
			} else {
				if ( is_active_sidebar( 'page-sidebar' ) ) {
					dynamic_sidebar( 'page-sidebar');
				} else { ?>
				<div class="widget"><h4>Page Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
				}
			}
		} else {
			if ( is_active_sidebar( 'blog-sidebar' ) ) {
				dynamic_sidebar( 'blog-sidebar');
			} else { ?>
				<div class="widget"><h4>Blog Sidebar.</h4>
				<a href="<?php echo home_url( '/'  ); ?>wp-admin/widgets.php" title="Add Widgets">Add Widgets</a></div><?php
			}
		}
}

//
// Custom Conditionals
//

/**
 * Returns true if not in admin dir
 *
 * @return boolean
 */
function is_not_admin() {
    return ( !is_admin() );
}

?>
