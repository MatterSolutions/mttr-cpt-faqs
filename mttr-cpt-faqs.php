<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: Matter Kit - FAQs
 * Description: This plugin adds a custom post type for faqs.
 * Version: 1.0.0
 * Author: Matter Solutions
 * Author URI: https://www.mttr.io
 */


add_action( 'plugins_loaded', array( 'mttr_custom_post_type_mttr_faqs', 'init' ) );


class mttr_custom_post_type_mttr_faqs {


	protected static $instance = NULL;
	public static $slug = 'mttr_faqs';
	public static $singular_name = 'FAQ';


    public static function getInstance() {

        NULL === self::$instance and self::$instance = new self;
        return self::$instance;

    }


    public static function init() {

    	// Register the post type
    	add_action( 'init', array( self::getInstance(), 'register_post_type' ), 3 );

    	// Register the Taxonomy
    	add_action( 'init', array( self::getInstance(), 'register_taxonomy' ), 3 );

    	// Remove the flexible content, it's not needed
    	add_filter( 'mttr_flex_layouts_locations_post_types_array', array( self::getInstance(), 'unhook_flexible_content' ) );

    	// Filter the ordering for auto grid items
    	add_filter( 'mttr_latest_posts_' . self::$slug, array( self::getInstance(), 'filter_grid_ordering' ) );

    	// Add Gforms options if wanted
    	add_filter( 'gform_pre_render', array( self::getInstance(), 'gravity_forms_faqs' ) );
		add_filter( 'gform_pre_validation', array( self::getInstance(), 'gravity_forms_faqs' ) );
		add_filter( 'gform_pre_submission_filter', array( self::getInstance(), 'gravity_forms_faqs' ) );
		add_filter( 'gform_admin_pre_render', array( self::getInstance(), 'gravity_forms_faqs' ) );

    }


    function register_taxonomy() {

    	register_taxonomy(
			self::$slug . '_category',
			self::$slug,
			array(
				'label' => __( self::$singular_name . ' Categories' ),
				'rewrite' => array(
					'slug' => 'faqs',
					'with_front' => false
				),
				'hierarchical' => true,
			)
		);

    }


    function register_post_type() {

    	if ( function_exists( 'mttr_generate_cpt_labels' ) ) {

    		$labels = mttr_generate_cpt_labels( 'FAQ' );

    	} else {

    		$labels = array(

    			'name' => __( 'FAQs' ),
				'singular_name' => __( 'FAQ' ),

			);

    	}

		register_post_type( self::$slug,

			// CPT Options
			array(

				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'has_archive' => false,
				'description'=> '',
				'rewrite' => array(
					'slug' => 'faqs',
					'with_front' => false,
				),
				'exclude_from_search' => true,
				'capability_type'     => 'page',
				'menu_icon'           => 'dashicons-editor-help',
				'supports' => array(

		            'title',
		            'editor',
		            'page-attributes',

		        ),

			)

		);

	}



	function filter_grid_ordering( $args ) {

		if ( $args['post_type'] == self::$slug ) {

			$args['orderby'] = 'menu_order title';
			$args['order'] = 'ASC';

		}

		return $args;

	}



	function unhook_flexible_content( $post_types ) {

		unset( $post_types[self::$slug] );

		return $post_types;

	}


	// Add the class 'mttr-cpt-faqs' to a checkbox, radio or dropdown field and it will automatically populate with the faqs

	function gravity_forms_faqs( $form ) {

	    foreach ( $form['fields'] as &$field ) {

	        if ( $field->type != 'select' && $field->type != 'radio' && $field->type != 'checkbox' && strpos( $field->cssClass, 'mttr-cpt-faqs' ) === false ) {
	            continue;
	        }

	        // you can add additional parameters here to alter the posts that are retrieved
	        // more info: [http://codex.WordPress.org/Template_Tags/get_posts](http://codex.WordPress.org/Template_Tags/get_posts)
	        $posts = get_posts( 'numberposts=-1&post_status=publish&post_type=' . self::$slug . '&order=ASC&orderby=title' );

	        if ( is_array( $posts ) && !empty( $posts ) ) {

		        $choices = array();

		        foreach ( $posts as $post ) {

		            $choices[] = array( 'text' => $post->post_title, 'value' => $post->post_title );

		        }

		        // update 'Select a Post' to whatever you'd like the instructive option to be
		        $field->choices = $choices;

		    }

	    }

	    return $form;
	}


}