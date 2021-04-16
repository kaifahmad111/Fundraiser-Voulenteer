<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();
/* Start the Loop */
$homePageEvents = new  WP_Query( array(
    'posts_per_page' => -1,
    'post_type' => 'voulunteer'
) );
//echo get_term( $term->parent, 'voulunteer');
//$terms = get_the_terms( get_the_ID(), 'voulunteer' );
while ($homePageEvents->have_posts()) {
    $homePageEvents->the_post();
    echo "<h2>".get_the_title()."</h2>";
    echo get_the_ID()."<br>";
    //$meta = array();
    $meta = get_post_meta(get_the_ID() , "meta-box-state" , true); 
    echo $meta."<br>";
    //echo get_the_parent();
    echo wp_trim_words(get_the_content(), 18);
}


get_footer();
