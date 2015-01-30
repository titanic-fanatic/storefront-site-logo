<?php
/**
 * Plugin Name: Storefront Site Logo
 * Plugin URI: http://wooassist.com/
 * Description: Lets you add a logo to your site by adding a Branding tab to the customizer where you can choose between "Title and Tagline" or "Logo image" for the Storefront theme.
 * Version: 0.1.0
 * Author: WooAssist
 * Author URI: http://wooassist.com/
 * Text Domain: wooassist
 * License: GPL2
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

Class WoA_SF_Logo_Change
{
	/*
	* Primary Constructor
	*/
	function __construct() {

		register_activation_hook(  __FILE__, array( $this, 'activation_hook' ) );
		add_action( 'wp', array( $this, 'init' ), 1000 );
		add_action( 'customize_register', array( $this, 'customize_register') );
	}

	/*
	* Initialize the plugin
	*/
	function init() {

		$header_layout = function_exists( 'Storefront_Designer' ) ? get_theme_mod( 'sd_header_layout', 'compact' ) : 'compact';
		
		// replace default branding function
		if ( $header_layout == 'expanded' ) { // check if Storefront Designer plugin is installed
			remove_action( 'storefront_header', 'storefront_site_branding', 45 );
			add_action( 'storefront_header', array( $this, 'branding' ), 45 );
		} else {
			remove_action( 'storefront_header', 'storefront_site_branding', 20 );
			add_action( 'storefront_header', array( $this, 'branding' ), 20 );
		}
		
	}

	/*
	* Check if using Storefront theme
	*/
	function activation_hook() {

		if( 'storefront' != basename( TEMPLATEPATH ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'Sorry, you can&rsquo;t activate this plugin unless you have installed the Storefront theme.' );
		}
	}

	/*
	* Customize funciton
	*/
	function customize_register( $wp_customize ) {

		// adding the section
		$wp_customize->add_section( 
			'woa_branding' , array(
			    'title'      => __( 'Branding', 'wooassist' ),
			    'priority'   => 30,
			)
		);

		$wp_customize->add_setting( 'woa_sf_enable_logo' );
		$wp_customize->add_setting( 'woa_sf_logo' );

		// adding the controls
		$wp_customize->add_control(
	        new WP_Customize_Control(
	            $wp_customize,
	            'woa_sf_enable_logo_img',
	            array(
	                'label'      => __( 'Choose branding style', 'wooassist' ),
	                'section'    => 'woa_branding',
	                'settings'   => 'woa_sf_enable_logo',
	                'type'		 => 'radio',
	                'choices'	 => array(
	                	'title_tagline'		=>	__( 'Title and Tagline', 'wooassist' ),
	                	'logo_img'			=>	__( 'Logo image', 'wooassist' )
	                )
	            )
	        )
	    );

		$wp_customize->add_control(
	        new WP_Customize_Image_Control(
	            $wp_customize,
	            'woa_sf_logo_img',
	            array(
	                'label'      => __( 'Logo Image', 'wooassist' ),
	                'section'    => 'woa_branding',
	                'settings'   => 'woa_sf_logo'
	            )
	        )
	    );

	}

	/*
	* Override default storefront branding
	*/
	function branding() {

		$check = get_theme_mod( 'woa_sf_enable_logo', 'title_tagline' );
		$logo = get_theme_mod( 'woa_sf_logo', null );

		if( ( $check == 'logo_img' ) && $logo ) { ?>
			<div class="site-branding">
				<img src="<?php echo $logo; ?>" style="display:inline-block;">
			</div>
		<?php
		}
		else if ( function_exists( 'jetpack_has_site_logo' ) && jetpack_has_site_logo() ) {
			jetpack_the_site_logo();
		} else { ?>
			<div class="site-branding">
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<p class="site-description"><?php bloginfo( 'description' ); ?></p>
			</div>
		<?php }
	}
}
new WoA_SF_Logo_Change;