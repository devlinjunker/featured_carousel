<?php
/*
Plugin Name: Featured Carousel
Plugin URI: 
Description: This plugin creates an image carousel from images that are in your gallery and text that is specified on the options page, and/or from the featured image and excerpt of posts on your site. The carousel is created using the jQuery Cycle plugin. You can set what is displayed in the carousel via the options page that is added to the admin menu. You can include the carousel on your homepage by using the <code>featured_carousel_display();</code> template tag, which will generate all the necessary HTML for the carousel.
Version: 0.2
Author: Devlin Junker
Author URI: 
Last Updated: 1/04/2012

This plugin inherits the GPL license from it's parent system, WordPress.
*/

/*****************
* INCLUDED FILES *
******************/

 
/*****************************************
* DEFINE VARIABLES TO BE USED FOR PLUGIN *
******************************************/
// Effects Array
$featured_carousel_effects = array(
	'none'			=> 'None',
	'fade'			=> 'Fade',
	'cover' 		=> 'Cover',
	'toss'			=> 'Toss',
	'uncover'		=> 'Uncover',
	'wipe'			=> 'Wipe',
	'blindX' 		=> 'Blind X',
	'blindY' 		=> 'Blind Y',
	'scrollUp'		=> 'Scroll Up',
	'scrollDown'		=> 'Scroll Down',
	'scrollLeft'		=> 'Scroll Left',
	'scrollRight'		=> 'Scroll Right',
	'slideX'		=> 'Slide X',
	'slideY'		=> 'Slide Y',
);
// TODO: Make Visible once Implemented
$content_locations = array(
	'left' 			=> 'Left', 
	'right' 		=> 'Right',
	//'bottom'		=> 'Bottom',
	//'top'			=> 'Top',
	//'below' 		=> 'Below',
	//'above' 		=> 'Above'
);

$controller_locations = array(
	'content_top_left'	=> 'Content Upper Left',
	'content_top_middle'	=> 'Content Upper Middle',
	'content_top_right'	=> 'Content Upper Right',
	'content_bottom_left'	=> 'Content Lower Left',
	'content_bottom_middle' => 'Content Lower Middle',
	'content_bottom_right'	=> 'Content Lower Right',
	'image_top_left'	=> 'Image Upper Left',
	'image_top_middle'	=> 'Image Upper Middle',
	'image_top_right'	=> 'Image Upper Right',
	'image_bottom_left'	=> 'Image Lower Left',
	'image_bottom_middle'	=> 'Image Lower Middle',
	'image_bottom_right'	=> 'Image Lower Right'
);

$image_locations = array(
	'left'			=> 'Left',
	'right'			=> 'Right',
	'center'		=> 'Center'
);

$title_sizes = array(
	'h1'			=> 'Header 1',
	'h2'			=> 'Header 2',
	'h3'			=> 'Header 3',
	'h4'			=> 'Header 4',
	'h5'			=> 'Header 5',
	'h6'			=> 'Header 6',
	'b'			=> 'Bolded',
	'p'			=> 'Font Size',
);

$title_locs = array(
	'block' 		=> 'Seperate Line',
	'inline'		=> 'Same Line'
);

// Defaults settings for Carousel
$featured_carousel_settings_defaults = apply_filters('featured_carousel_settings_defaults', array(
	'slide_count' 		=> 2,
	
	// Container Settings
	'div' 			=> 'carousel',
	'height' 		=> 250,		// Pixels
	'width' 		=> 938,		// Pixels
	
	// Image Settings
	'image_loc'		=> 'center',
	'image_color' 		=> 'FFFFFF',
	
	// Content Settings
	'content_loc'		=> 'left',
	'content_height'	=> 100,		// Percent
	'content_width' 	=> 25, 		// Percent
	'content_pad'		=> 20,		// Pixels
	'content_color' 	=> '000000',
	'text_color' 		=> 'FFFFFF',
	'title_size'		=> 'h2',
	'title_location'	=> 'block',
	'overlap' 		=> false,
	'opacity'		=> '40',	// Percent
	
	// Controller Settings
	'control_visible'	=> true,
	'control_loc'		=> 'image_top_right',
	'slide_nums'		=> true,
	'control_padding'	=> '7',		// Pixels
	'control_opacity'	=> '100',	// Percent
	'control_color' 	=> '000000',
	'active_color'		=> '000000',
	'active_text'		=> 'FFFFFF',
	'inactive_color'	=> 'FFFFFF',
	'inactive_text'		=> '000000',
	
	// Transition Settings
	'effect' 		=> 'fade',
	'delay' 		=> 3,		// Seconds
	'duration' 		=> 1,		// Seconds
));

// Defaults for Slides (no images and placeholder text)
$featured_carousel_slides_defaults = apply_filters ('featured_carousel_slides_defaults', array(
	's_1' => array(
			'url' 		=> Null,
			'title' 	=> "PlaceHolder Title",
			'text' 		=> "This is the Featured Carousel Plugin, you can edit the content of the carousel from the Featured Carousel Tab in the Admin Menu",
			'img' 		=> Null,
			'title_visible'	=> true,
			'crop' 		=> false
			),
	's_2' => array(
			'url' 		=> Null,
			'title' 	=> "PlaceHolder Title",
			'text' 		=> "This is the Featured Carousel Plugin, you can edit the content of the carousel from the Featured Carousel Tab in the Admin Menu",
			'img' 		=> Null,
			'title_visible'	=> true,
			'crop' 		=> false
			)
));

//	Pull the User Defined Settings from the DB
$featured_carousel_settings = get_option('featured_carousel_settings');
$featured_carousel_slides = get_option('featured_carousel_slides');

//	If there are no User Defined Settings, use Defaults
$featured_carousel_settings = wp_parse_args($featured_carousel_settings, $featured_carousel_settings_defaults);
$featured_carousel_slides = wp_parse_args($featured_carousel_slides, $featured_carousel_slides_defaults);


if( is_admin() ){
	add_action( 'admin_menu', 'add_featured_carousel_admin_menu' );
	add_action( 'admin_init', 'register_featured_carousel_settings_page' );

}else{
	add_action( 'wp_enqueue_scripts', 'register_featured_carousel_display_files' );
	add_action( 'wp_head', 'featured_carousel_display_header_script' );
}

function register_featured_carousel_display_files(){
	if( is_home() ){
		// Register Files
		wp_register_style( 'featured_carousel_styles', plugins_url('carousel.css', __FILE__) );
		wp_register_script( 'jquery_cycle', plugins_url('jquery.cycle.all.min.js', __FILE__) );
	
		// Add to Home Page	
		wp_enqueue_style( 'featured_carousel_styles' );
		wp_enqueue_script( 'jquery_cycle' );
	}
}

## 
## Creates and Inserts the Javascript to Make Carousel
##
function featured_carousel_display_header_script(){
	global $featured_carousel_settings;
	
	print "<script type='text/javascript'>
	jQuery(document).ready(function($) {
		$('#$featured_carousel_settings[div]').cycle({ 
			fx: '$featured_carousel_settings[effect]',
			timeout: ".($featured_carousel_settings['delay'] * 1000).",
			speed: ".($featured_carousel_settings['duration'] * 1000).",
			pause: 1,
			fit: 1,
			pager: '#controller'
		});
	});
	</script>";
	
	if( $featured_carousel_settings['control_visible'] == true ){
		print "<style>
			#controller a{
				background-color: #$featured_carousel_settings[inactive_color];
				color: #$featured_carousel_settings[inactive_text];
			}

			#controller a.activeSlide, #controller a:hover{
				background-color: #$featured_carousel_settings[active_color];
				color: #$featured_carousel_settings[active_text];
			}
		</style>";
	}
}

/********************************
* CAROUSEL OPTIONS AND SETTINGS *
*********************************/


##
## Adds the Featured Carousel Tab to the Admin Menu
## 
function add_featured_carousel_admin_menu(){
	// Add Page to Menu
	$page = add_menu_page( 'Featured Carousel', 'Featured Carousel', 'manage_options', 'featured_carousel', 'featured_carousel_menu_page', "", "40.5");
	
	// Add Stylesheet Hook to Page
	add_action( 'admin_print_styles-'.$page , 'featured_carousel_options_sheets');

	// Add Hook for Header Script
	//add_action( 'wp_head', 'featured_carousel_options_header');
}

##
## Adds Options Stylesheet to Plugin Options Page
##
function featured_carousel_options_sheets(){
	wp_enqueue_style('featured_carousel_options_styles');
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media_upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery');
}

##
## Adds Script to top of Options Page 
##
function featured_carousel_options_script(){
	print "<script type='text/javascript'>
	jQuery(document).ready(function($) {
		$('input#image_select_button').each(function(){
			$(this).attr('onclick', 'carousel_image_select(this)');
		});
	}); 

	function carousel_image_select(element){
		var slide_num = jQuery(element).attr('data-slide-num');
		formfield = jQuery('#image_select[data-slide-num='+slide_num+']').attr('name');
		tb_show('', 'media-upload.php?type=image&TB_iframe=true');
			
		window.send_to_editor = function(html){
			var imgurl = jQuery('img', html).attr('src');
			jQuery('#image_select[data-slide-num='+slide_num+']').val(imgurl);
			tb_remove(); 
		};

		return false;
	}
	</script>";
}

##
## Registers the Carousel Settings so Wordpress will handle the options page
##
function register_featured_carousel_settings_page(){
	global $featured_carousel_settings;
	
	global $featured_carousel_effects, $content_locations, $controller_locations, $image_locations, $title_sizes, $title_locs;
	
	// Register Stylesheets
	wp_register_style( 'featured_carousel_options_styles', plugins_url('options.css', __FILE__) );
	
	register_setting( 'featured_carousel_settings_group', 'featured_carousel_settings', 'sanitize_featured_carousel_settings');
	register_setting( 'featured_carousel_settings_group', 'featured_carousel_slides', 'sanitize_featured_carousel_slides'); 
	
	
	/*** Section to Configure Slides ***/
	add_settings_section( 'featured_carousel_slides', 'Slides', 'featured_carousel_options_section', 'featured_carousel' );
	
	// Add Field to Change Number of Slides
	add_settings_field( 'slide_count', 'Number of Slides', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_slides', array( 'setting_name' => 'slide_count', 'max_char' => 1, 'help' => 'Save Changes to Edit New Slides' ) );
	
	// Add Fields to Manipulate Slide Content (Adds the Number Specified in Settings)
	for($i = 1; $i <= $featured_carousel_settings['slide_count']; $i++){
		add_settings_field ("slide_$i", "Slide $i", 'featured_carousel_slide_input', 'featured_carousel', 'featured_carousel_slides', array( 'slide_num' => $i ) );
	}
	
	
	/*** Section to Configure Carousel Container ***/
	add_settings_section( 'featured_carousel_container_settings', 'Container Settings', 'featured_carousel_options_section', 'featured_carousel' );
	
	// Add Container ID Field
	add_settings_field ( 'div', 'Container ID', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_container_settings', array( 'setting_name' => 'div' ) );
	
	// Add Container Width Field
	add_settings_field ( 'width', 'Container Width', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_container_settings', array( 'setting_name' => 'width', 'max_char' => 3, 'follow' => 'pixels' ) );
	
	// Add Container Height Field
	add_settings_field ( 'height', 'Container Height', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_container_settings', array( 'setting_name' => 'height', 'max_char' => 3, 'follow' => 'pixels' ) );
	
	
	/*** Section to Configure Content ***/
	add_settings_section( 'featured_carousel_content_settings', 'Content Settings', 'featured_carousel_options_section', 'featured_carousel' );
	
	// Add Content Location
	add_settings_field( 'content_loc', 'Content Location', 'featured_carousel_settings_dropdown', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'content_loc', 'options' => $content_locations ) );
	
	// Add Content Width Field
	add_settings_field ( 'content_width', 'Content Width', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'content_width', 'max_char' => 3, 'follow' => '%', 'explain' => 'Relative to Carousel Container Size' ) );
	
	// Add Content Height Field
	add_settings_field ( 'content_height', 'Content Height', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'content_height', 'max_char' => 3, 'follow' => '%', 'explain' => 'Relative to Carousel Container Size' ) );
	
	// Add Content Height Field
	add_settings_field ( 'content_pad', 'Content Padding', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'content_pad', 'max_char' => 3, 'follow' => 'px' ) );
	
	// Add Content Background Color Field
	add_settings_field( 'content_color', 'Content Background Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'content_color', 'max_char' => 6 ) );
	
	// Add Content Background Color Field
	add_settings_field( 'text_color', 'Content Text Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'text_color', 'max_char' => 6 ) );
	
	// Add Title Size Field
	add_settings_field( 'title_size', 'Title Size', 'featured_carousel_settings_dropdown', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'title_size', 'options' => $title_sizes ) );
	
	// Add Title Location Field
	add_settings_field( 'title_loc', 'Title Location', 'featured_carousel_settings_dropdown', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'title_loc', 'options' => $title_locs ) );
	
	// Add Content Overlap Field
	add_settings_field( 'overlap', 'Content Overlap', 'featured_carousel_settings_checkbox', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'overlap', 'explain' => 'Check if content should overlap image' ) );
	
	// Add Content Opacity Field
	add_settings_field( 'opacity', 'Content Background Opacity', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_content_settings', array( 'setting_name' => 'opacity', 'max_char' => 3, 'follow' => '%', 'help' => 'The Background Opacity Value will only be used if the content <em>overlaps</em> the image or if the content is <em>below</em> or <em>above</em> the carousel' ) );

	
	/*** Section to Configure Image ***/
	add_settings_section( 'featured_carousel_image_settings', 'Image Settings', 'featured_carousel_options_section', 'featured_carousel' );
	
	// Add Image Location Field
	add_settings_field( 'image_loc', 'Image Location', 'featured_carousel_settings_dropdown', 'featured_carousel', 'featured_carousel_image_settings', array( 'setting_name' => 'image_loc', 'options' => $image_locations ) );
	
	// Add Image Background Color Field
	add_settings_field( 'image_color', 'Image Background Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_image_settings', array( 'setting_name' => 'image_color', 'max_char' => 6, 'explain' => 'Color to appear behind the image' ) );
	
	
	/*** Section to Configure Controller ***/
	add_settings_section( 'featured_carousel_controller_settings', 'Controller Settings', 'featured_carousel_options_section', 'featured_carousel' );

	add_settings_field( 'control_visible', 'Controller Visible', 'featured_carousel_settings_checkbox', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'control_visible', 'explain' => 'Check to Show Controller on Carousel' ) );

	add_settings_field( 'control_loc', 'Controller Location', 'featured_carousel_settings_dropdown', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'control_loc', 'options' => $controller_locations ) );
	// TODO: Make Visible once implemented
//	add_settings_field( 'slide_nums', 'Controller Slide Numbers', 'featured_carousel_settings_checkbox', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'slide_nums', 'explain' => 'Check to Show Slide Numbers on Controller' ) ); 
	
	add_settings_field( 'control_opacity', 'Controller Background Opacity', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'control_opacity', 'max_char' => 3 ) );

	add_settings_field( 'control_color', 'Controller Background Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'control_color', 'max_char' => 6 ) );

	add_settings_field( 'active_color', 'Controller Active Selector Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'active_color', 'max_char' => 6 ) );

	add_settings_field( 'active_text', 'Controller Active Text Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'active_text', 'max_char' => 6 ) );

	add_settings_field( 'inactive_color', 'Controller Inactive Selector Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'inactive_color', 'max_char' => 6 ) );

	add_settings_field( 'inactive_text', 'Controller Inactive Text Color', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_controller_settings', array( 'setting_name' => 'inactive_text', 'max_char' => 6 ) );
	

	/*** Section to Configure Transition ***/
	add_settings_section( 'featured_carousel_transition_settings', 'Transition Settings', 'featured_carousel_options_section', 'featured_carousel' );
	
	// Add Transition Effect Field
	add_settings_field( 'effect', 'Transition Effect', 'featured_carousel_settings_dropdown', 'featured_carousel', 'featured_carousel_transition_settings', array( 'setting_name' => 'effect', 'options' => $featured_carousel_effects, 'follow' => "<a href='http://jquery.malsup.com/cycle/browser.html' target='blank'>Demos</a>" ) );
	
	// Add Transition Delay Field
	add_settings_field( 'delay', 'Transition Delay', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_transition_settings', array( 'setting_name' => 'delay', 'max_char' => 1, 'explain' => 'Length of Time Each Slide is Visible', 'follow' => 'second(s)' ) );
	
	// Add Transition Duration Field
	add_settings_field ( 'duration', 'Transition Duration', 'featured_carousel_settings_text_input', 'featured_carousel', 'featured_carousel_transition_settings', array( 'setting_name' => 'duration', 'max_char' => 1, 'explain' => 'Length of Time Each Transition Takes', 'follow' => 'second(s)' ) );
}

##
## Formats the Carousel Options Page
##
function featured_carousel_menu_page(){
	//Scripts
	featured_carousel_options_script();	

	// Top of Page
	print "<div class='wrap' id='featured_carousel_options_page'>
				<h2> Featured Carousel Plugin Options </h2>
				Options and Settings for the Featured Carousel Plugin
				<form action='options.php' method='post'>";
				
	// Call Wordpress Functions to Build Options Page
	settings_fields( 'featured_carousel_settings_group' );
	do_settings_sections( 'featured_carousel' );
	
	// Bottom of Page
	print "		<input name='Submit' type='submit' value='Save Changes'/>
				</form>
			</div>";
}

##
## Displays Information at Top Each Settings Section Based on Section Title
##
function featured_carousel_options_section($args){
	global $featured_carousel_settings;
	switch( $args['title'] ):
		case "Slides":
			print "<p>Edit the slides to be displayed on the Carousel</p>";
			
			if( $featured_carousel_settings['overlap'] or $featured_carousel_settings['content_loc'] == 'below' or $featured_carousel_settings['content_loc'] == 'above' ){
				$img_height = 100;
				$img_width = 100;
			}elseif($featured_carousel_settings['content_loc'] == 'right' or $featured_carousel_settings['content_loc'] == 'left' ){
				$img_width = 100 - $featured_carousel_settings['content_width'];
				$img_height = 100;
			}else{
				$img_height = 100 - $featured_carousel_settings['content_height']; 
				$img_width = 100;
			}
			
			$perfect_width = floor( $featured_carousel_settings['width'] * $img_width / 100 );
			$perfect_height = floor( $featured_carousel_settings['height'] * $img_height / 100 );
			
			print "<p>The Optimum Image Dimensions are: $perfect_width (W) x $perfect_height (H) (or any image with the same relative dimensions eg. ".($perfect_width*2)." x ".($perfect_height*2).")</p>";
			
			break;
		case "Container Settings":
			print "<p>Edit the Carousel Container</p>";
			break;
		case "Content Settings":
			print "<p>Edit the Slide Content Location and Look</p>";
			break;
		case "Image Settings":
			print "<p>Edit the Slide Image Size, Location and Look</p>";
			break;
		case "Controller Settings":
			print "<p>Edit the Slide Controller Size, Location and Look</p>";
			break;
		case "Transition Settings":
			print "<p>Edit the transition settings for the Carousel</p>";
			break;
	endswitch; 
}

##
## Builds the Input to Configure a Slide
## @slide_num - required - Specifies which Slide this Input Corresponds to
##
function featured_carousel_slide_input($args){
	global $featured_carousel_slides;
	
	// Get Which Slide Number this Input Corresponds to
	$slide_num = "s_".$args['slide_num'];
	
	if( !empty( $slide_num ) ){
		// Get Slide Info
		$url = $featured_carousel_slides[$slide_num]['url'];
		$title = $featured_carousel_slides[$slide_num]['title'];
		$text = $featured_carousel_slides[$slide_num]['text'];
		$img = $featured_carousel_slides[$slide_num]['img'];
		$cropped = $featured_carousel_slides[$slide_num]['crop'];
		$title_visible = $featured_carousel_slides[$slide_num]['title_visible'];
		
		// Create Help Strings
		$title_help = "If left empty and the Slide URL is a page in your site, the Title will default to the Post Title. If it is an external page, the Title will default to the Web Page Title defined in the <code>&lt;title&gt;</code> tag.";
		$image_help = "If left empty and the Slide URL is a page in your site, the Image will default to the Post's Featured Image. If it is an external page, the Image will default to the first image found on the webpage.";
		$text_help = "If left empty and the Slide URL is a page in your site, the Text will default to the Post Excerpt. If it is an external page, the Text will be ommitted from the slide.";
		
		// Check if URL is in the Site
		$in_site = ( ($post_id = my_bwp_url_to_postid($url)) !== 0 );
		
		// TODO: Make 'Select Existing Page' Link Visible Once Implemented
		// Add URL Input
		$input = "<span class='slide_info_label'>URL:</span> <input class='slide_url_input' name='featured_carousel_slides[$slide_num][url]' type='url' value='$url'/> <a style='display:none;' href=''>Select Existing Page</a>";
		
		$input .= "<br/>";
		
		// If URL is in The Site
		if( $in_site ){
			// Get Post Info
			$post = get_post($post_id);
			
			$post_title = $post->post_title;
			$excerpt = $post->post_excerpt;
			
			$content = $post->post_content;
			$first_string = featured_carousel_page_intro($post_id);
		}
		
		
		// Add Title Input
		$input .= "<span class='slide_info_label'>Title:</span> <input class='slide_title_input' name='featured_carousel_slides[$slide_num][title]' type='text' placeholder='$post_title' ".( isset($title) ? "value='$title'" : "")."/>".featured_carousel_help_tag($title_help)."<br/>";
		
		// Add Title Display Checkbox
		$input .= "<span class='slide_info_label'>Display Title</span> <input class='slide_title_visible_input' name='featured_carousel_slides[$slide_num][title_visible]' type='checkbox' value='true' ".($title_visible ? 'checked' : '')."/><br/>";
	
		// Add Image Input
		$input .= "<span class='slide_info_label'>Image URL:</span> <input id='image_select' class='slide_url_input' name='featured_carousel_slides[$slide_num][img]' data-slide-num='$slide_num' type='url' ".( isset($img) ? "value='$img'" : "")."/> <input type='button' id='image_select_button' data-slide-num='$slide_num' style='' value='Select Existing Image' >".featured_carousel_help_tag($image_help)."<br/>";
		
		// Add Crop Checkbox
		$input .= "<span class='slide_info_label'>Crop to Fit?</span> <input class='slide_crop_input' name='featured_carousel_slides[$slide_num][crop]' type='checkbox' value='true' ".($cropped ? 'checked' : '')."/><br/>";
		
		// Add Text Input
		$input .= "<span class='slide_info_label'>Text:<br/>".featured_carousel_help_tag($text_help)."</span> <textarea class='slide_text_input' name='featured_carousel_slides[$slide_num][text]' placeholder='".( !empty($excerpt) ? $excerpt : $first_string)."'>".( isset($text) ? $text : "")."</textarea><br/>";
		
		print $input;
	
	}else{
		// Trigger an Error
		featured_carousel_error("Error: Slide Configuration Slide Number Not Found in Args", $args);
	}
	
	
}

##
## Builds a text input field for a Carousel Setting Field
## 	@setting_name 	- required 	- Name of Setting this field corresponds to
##	@explain 		- optional 	- Explanation Text that appears before Field
## 	@max_char 		- optional 	- Max Length of Setting Input String
##	@follow 		- optional 	- Text that Follows the Field
##	@help 			- optional	- Help Text that is displayed when hovering over help bubble
##
function featured_carousel_settings_text_input($args){
	global $featured_carousel_settings;
	
	// Get the Setting Name this Field corresponds to
	$setting_name = $args['setting_name'];
	
	// Get Optional Arguments
	$explanation = $args['explain'];
	$max_char = $args['max_char'];
	$following = $args['follow'];
	$help_text = $args['help'];
	
	// Check if the Setting Name is Set
	if( !empty( $setting_name ) ){
		
		$input = "";
		
		// Add Explanation Text If Set
		if( !empty( $explanation ) ){
			$input .= " <span class='explanation_text'>$explanation</span><br/>";
		}
		
		// Build Input and Print
		$input .= "<input id='$setting_name' name='featured_carousel_settings[$setting_name]'";
		
		if( !empty( $max_char ) ){
			$input .= "size='$max_char' maxlength='$max_char'";
		}
		
		$input .= "type='text' value='$featured_carousel_settings[$setting_name]'/>";
		
		// Add Following Text If Set
		if( !empty( $following ) ){
			$input .= " <span class='follow_text'>$following</span>";
		}
		
		// Add Help Text If Set
		if( !empty( $help_text ) ){
			$input .= featured_carousel_help_tag($help_text);
		}
		
		print $input;
	
	// If Setting Name NOT set
	}else{
		// Trigger an Error
		featured_carousel_error("Error: Text Input Setting Name Not Found in Args", $args);
	}
}

##
## Builds a text input field for a Carousel Setting Field
## 	@setting_name 	- required 	- Name of Setting this field corresponds to
##	@explain 		- optional 	- Explanation Text that appears before Field
##	@follow 		- optional 	- Text that Follows the Field
##	@help 			- optional	- Help Text that is displayed when hovering over help bubble
##
function featured_carousel_settings_checkbox($args){
	global $featured_carousel_settings;
	
	// Get the Setting Name this Field corresponds to
	$setting_name = $args['setting_name'];
	
	// Get Optional Arguments
	$explanation = $args['explain'];
	$following = $args['follow'];
	$help_text = $args['help'];
	
	// Check if the Setting Name is Set
	if( !empty( $setting_name ) ){
		
		$input = "";
		
		// Add Explanation Text If Set
		if( !empty( $explanation ) ){
			$input .= " <span class='explanation_text'>$explanation</span><br/>";
		}
		
		// Build Input and Print
		$input .= "<input id='$setting_name' name='featured_carousel_settings[$setting_name]' type='checkbox' value='true' ".($featured_carousel_settings[$setting_name] ? 'checked': '')."/>";
		
		// Add Following Text If Set
		if( !empty( $following ) ){
			$input .= " <span class='follow_text'>$following</span>";
		}
		
		// Add Help Text If Set
		if( !empty( $help_text ) ){
			$input .= featured_carousel_help_tag($help_text);
		}
		
		print $input;
	
	// If Setting Name NOT set
	}else{
		// Trigger an Error
		featured_carousel_error("Error: Text Input Setting Name Not Found in Args", $args);
	}
}

##
## Builds a dropdown for a Carousel Setting Field
## 	@setting_name 	- required 	- Name of Setting this dropdown corresponds to
##	@options 		- required 	- Array of Options that can be selected
##	@explain 		- optional 	- Explanation Text that appears before dropdown
##	@follow 		- optional 	- Text that Follows the dropdown
##	@help 			- optional	- Help text that is displayed when hovering over help bubble
##
function featured_carousel_settings_dropdown($args){
	global $featured_carousel_settings;
	
	// Get the Setting Name this Dropdown corresponds to
	$setting_name = $args['setting_name'];
	
	// Get Dropdown Options
	$options = $args['options'];
	
	// Get Current Option Selected
	$current_val = $featured_carousel_settings[$setting_name];
	
	// Get Optional Arguments
	$explanation = $args['explain'];
	$following = $args['follow'];
	$help_text = $args['help'];
	
	// Check if the Setting Name is Set
	if( !empty( $setting_name ) ){
		
		$input = "";
		
		// Add Explanation Text If Set
		if( !empty( $explanation ) ){
			$input .= " <span class='explanation_text'>$explanation</span><br/>";
		}
		
		// Build Dropdown
		$input .= "<select id='$setting_name' name='featured_carousel_settings[$setting_name]'>";
		
		foreach( $options as $value => $text ){
			if( is_array( $text ) ){
				$input .= "<optgroup label='$value'>";

				foreach( $text as $array_value => $array_text ){
					$input .= "<option value='$array_value' ".($array_value == $current_val ? "selected" : "").">$array_text</option>";
				}

				$input .= "</optgroup>";
			}else{
				$input .= "<option value='$value' ".($value == $current_val ? "selected" : "").">$text</option>";
			}
		}
		
		$input .= "</select>";
		
		// Add Following Text If Set
		if( !empty( $following ) ){
			$input .= " <span class='follow_text'>$following</span>";
		}
		
		// Add Help Text If Set
		if( !empty( $help_text ) ){
			$input .= featured_carousel_help_tag($help_text);
		}
		
		print $input;
		
	// If Setting Name NOT set
	}else{
		// Trigger an Error
		featured_carousel_error("Error: Dropdown Setting Name Not Found in Args", $args);
	}
}

## 
## Cleans and Fills in Missing Data
##	$input 	- required - auto 	- Data to be cleaned and filled in
##
function sanitize_featured_carousel_slides($input){
	global $featured_carousel_settings;
	
	$i = 0;
	
	foreach( $input as $slide_num => $data ){
		// Check if supposed to have data
		if( $i < $featured_carousel_settings['slide_count'] ){
			// Sanatize the Link URL
			$input[$slide_num]['url'] = esc_url( $data['url'] );
			
			
			$in_site = (($post_id = my_bwp_url_to_postid( $input[$slide_num]['url'] )) != 0);
			
			
			if( $in_site ){
				if( $data['text'] == "" ){
					// If blank text, get page intro
					$input[$slide_num]['text'] = featured_carousel_page_intro( $post_id );
				}else{
					$input[$slide_num]['text'] = sanitize_text_field($data['text']);
				}
				
				
				if( $data['img'] == "" ){
					// If image URL blank, get Post Thumbnail
					if( has_post_thumbnail( $post_id ) ){
						$info = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
						
						$input[$slide_num]['img'] = $info[0];
					}
					
				}else{
					// Otherwise Clean Image URL
					$input[$slide_num]['img'] = esc_url( $data['img'] ); 
				}
				
				
				if( $data['title'] == "" ){
					// If title blank, get post title
					$input[$slide_num]['title'] = get_the_title( $post_id );
				}else{
					$input[$slide_num]['title'] = sanitize_text_field( $data['title'] );
				}
			}elseif( !empty( $input[$slide_num]['url'] ) ){ 
				// Not In Site
				
				// Sanatize Text String
				$input[$slide_num]['text'] = sanitize_text_field( $data['text'] );
				
				$html = file_get_contents( $data['url'] );
				
				$DOM = new DOMDocument;
				$DOM->loadHTML( $html );

				// Sanatize/Fill in Title	
				if( $data['title'] == "" ){
					$objs = $DOM->getElementsByTagName('title');
					
					$input[$slide_num]['title'] =  $objs->item(0)->nodeValue;
				}else{
					$input[$slide_num]['title'] = sanitize_text_field( $data['title'] );
				}

				// Sanatize/Fill in Image URL	
				if( $data['img'] == "" ){
					$body = $DOM->getElementsByTagName('body');

					$objs = $body->item(0)->getElementsByTagName('img');

					$input[$slide_num]['img'] = esc_url( $objs->item(0)->getAttribute('src') );
				}else{
					$input[$slide_num]['img'] = esc_url( $data['img'] );
				}

			}
			
			if( $data['crop'] == 'true' ){
				$input[$slide_num]['crop'] = true;
			}else{
				$input[$slide_num]['crop'] = false;
			}
		}else{
			// If not supposed to have this slide data, remove it
			$input[$slide_num] = 0;
		
		}
		
		// Keep track of which slide this is
		$i++;
		
	}
	
	return $input;
}

function sanitize_featured_carousel_settings($input){
	global $featured_carousel_settings, $featured_carousel_defaults;
	global $image_locations, $content_locations, $controller_locations, $featured_carousel_effects, $title_sizes, $title_locations;

	$settings = $featured_carousel_settings;
	$defaults = $featured_carousel_defaults;

	// Check if Slide Count is Numeric
	if( !is_numeric( $input['slide_count'] ) ){
		// If Not, Set to 0
		$input['slide_count'] = 0;	
	}
	
	// Strip Spaces from Carousel ID
	$input['div'] = str_replace( ' ', '', sanitize_text_field( $input['div'] ) );
	
	// Check if Width is Numeric
	if( !is_numeric( $input['width'] ) ){
		// If Not, don't change
		$input['width'] = $settings['width'];
	}
	
	// Check if Height is Numeric
	if( !is_numeric( $input['height'] ) ){
		// If Not, don't change 
		$input['height'] = $settings['height'];
	}

	// Check if Image Location is Valid Value
	if( !array_key_exists( $input['image_loc'], $image_locations ) ){
		// If not, don't change 
		$input['image_loc'] = $settings['image_loc']; 
	}

	// Check if Image Background is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['image_background'] ) !== 1 ){
		// If not, don't change
		$input['image_background'] = $settings['image_background'];
	}

	// Check if Content Location is Valid Value
	if( !array_key_exists( $input['content_loc'], $content_locations ) ){
		// If not, don't change
		$input['content_loc'] = $settings['content_loc']; 
	}

	// Check if Content Height is Numeric
	if( !is_numeric( $input['content_height'] ) ){
		// If Not, don't change 
		$input['content_height'] = $settings['content_height'];
	}

	// Check if Content Width is Numeric
	if( !is_numeric( $input['content_width'] ) ){
		// If Not, don't change 
		$input['content_width'] = $settings['content_width'];
	}

	// Check if Content Padding is Numeric
	if( !is_numeric( $input['content_pad'] ) ){
		// If Not, don't change 
		$input['content_pad'] = $settings['content_pad'];
	}

	// Check if Content Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['content_color'] ) !== 1 ){
		// If not, don't change
		$input['content_color'] = $settings['content_color'];
	}

	// Check if Content Text Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['text_color'] ) !== 1 ){
		// If not, don't change
		$input['text_color'] = $settings['text_color'];
	}
	
	// Check if Title Size is Valid Value
	if( !array_key_exists( $input['title_size'], $title_sizes ) ){
		// If not, don't change
		$input['title_size'] = $settings['title_size']; 
	}

	// Check if Title Location is Valid Value
	if( !array_key_exists( $input['title_loc'], $title_locations ) ){
		// If not, don't change
		$input['title_loc'] = $settings['title_loc']; 
	}
	
	// Check Overlap is True or False
	if( $input['overlap'] == $defaults['overlap'] ){
		$input['overlap'] = $defaults['overlap'];
	}else{
		$input['overlap'] = !$defaults['overlap'];
	}
	
	// Check if Content Opacity Value is Numeric and less than 100
	if( !is_numeric( $input['opacity'] ) or $input['opacity'] > 100 ){
		// If Not, don't change
		$input['opacity'] = $settings['opacity'];
	}
	
	// Check if Controller is Visible or Not
	if( $input['control_visible'] == $defaults['control_visible'] ){
		$input['control_visible'] == $defaults['control_visible'];
	}else{
		$input['control_visible'] == !$defaults['control_visible'];
	}

	// Check if Controller Location is Valid Value
	if( !array_key_exists( $input['control_loc'], $controller_locations ) ){
		// If not, don't change
		$input['control_loc'] = $settings['control_loc']; 
	}

	// Check if Controller is Visible or Not
	if( $input['slide_nums'] == $defaults['slide_nums'] ){
		$input['slide_nums'] == $defaults['slide_nums'];
	}else{
		$input['slide_nums'] == !$defaults['slide_nums'];
	}

	// Check if Opacity Padding Value is Numeric
	if( !is_numeric( $input['control_padding'] ) ){
		// If Not, don't change
		$input['control_padding'] = $settings['control_padding'];
	}

	// Check if Controller Opacity Values is Numeric and less than 100
	if( !is_numeric( $input['control_opacity'] ) or $input['control_opacity'] > 100 ){
		// If Not, don't change
		$input['control_opacity'] = $settings['control_opacity'];
	}

	// Check if Controller Background Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['control_color'] ) !== 1 ){
		// If not, don't change
		$input['control_color'] = $settings['control_color'];
	}

	// Check if Controller Active Link Background Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['active_color'] ) !== 1 ){
		// If not, don't change
		$input['active_color'] = $settings['active_color'];
	}

	// Check if Controller Active Link Text Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['active_text'] ) !== 1 ){
		// If not, don't change
		$input['active_text'] = $settings['active_text'];
	}

	// Check if Controller Inactive Link Background Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['inactive_color'] ) !== 1 ){
		// If not, don't change
		$input['inactive_color'] = $settings['inactive_color'];
	}

	// Check if Controller Inactive Link Text Color is 6 digit Hex Value
	if( preg_match( '/[a-fA-F0-9]{6}/' , $input['inactive_text'] ) !== 1 ){
		// If not, don't change
		$input['inactive_text'] = $settings['inactive_text'];
	}

	// Check if Transition Effect is Valid Value
	if( !array_key_exists( $input['effect'], $featured_carousel_effects ) ){
		// If not, don't change
		$input['effect'] = $settings['effect']; 
	}

	// Check if Delay Value is Numeric
	if( !is_numeric( $input['delay'] ) ){
		// If Not, don't change
		$input['delay'] = $settings['delay'];
	}

	// Check if Delay Value is Numeric
	if( !is_numeric( $input['duration'] ) ){
		// If Not, don't change
		$input['duration'] = $settings['duration'];
	}

	return $input;
}
/****************************
* CAROUSEL DISPLAY FUNCTION *
*****************************/

##
## Displays the Featured Carousel where this function is called
##
function featured_carousel_display(){
	global $featured_carousel_settings, $featured_carousel_slides;
	
	$settings = $featured_carousel_settings;
	
	// Wrapper: contains carousel and controller
	$carousel = "<div id='carousel_wrap'>";
	
	// Carousel
	$carousel .= "<div id='$featured_carousel_settings[div]' style='height:$settings[height]px;width:$settings[width]px;'>";
	
	$i = 0; 
	
	foreach( $featured_carousel_slides as $slide_num => $data ){
		
		// Only Display Number of Slides Specified in Settings
		if( $i < $settings['slide_count'] ){
			
			// Start Slide
			$carousel .= "<div id='$slide_num' style='height:100%;width:100%;".( $i > 0 ? "display:none;" : "" )."'>";
			
			// Start Link if slide has Link URL
			if( !empty( $data['url'] ) ){
				$carousel .= "<a href='$data[url]' title='$data[title]'>";
			}
			
			// Get Image and Content Style
			$image_style = featured_carousel_image_style($slide_num);
			$content_style = featured_carousel_content_style($slide_num);
			
			// Get Title Size and Location
			$title_tag = $settings['title_size'];
			$title_tag .= " style='display:$settings[title_loc]'";
		
			$title = ( !empty( $data['title'] ) ? $data['title'].':' : "");
	
			// Start Image
			$carousel .= "<div id='slide_image' $image_style>";
			
			if( !$settings['overlap'] ){ // If no Overlap
				$carousel .= "</div>"; // End Image 
			}
			
			// Display Slide Content
			$carousel .= "<div id='slide_content' $content_style>".($data['title_visible'] ? "<$title_tag>$title</$title_tag>" : "")."<p style='display:$settings[title_loc];'> $data[text]</p></div>";
			
			if( $settings['overlap'] ){ // If Overlap
				$carousel .= "</div>"; // End Image 
			}
			
			// End Link if slide has Link URL
			if( !empty( $data['url'] ) ){
				$carousel .= "</a>";
			}
			
			// End Slide
			$carousel .= "</div>";
		}
		$i++;
	}
	
	// End Carousel
	$carousel .= "</div>";
	
	// Display Carousel if Set
	if( $settings['control_visible'] == true ){
		// Get Controller Style
		$controller_style = featured_carousel_controller_style();

		// Place Controller
		if( strpos( $settings['control_loc'], 'middle' ) !== false ){
			$carousel .= "<div id='control_wrap' $controller_style[1]>";
		}

		$carousel .= "<div id='controller' $controller_style[0]></div>";
			
		if( strpos( $settings['control_loc'], 'middle' ) !== false ){
			$carousel .= "</div>";
		}
	}

	// End Carousel Wrapper
	$carousel .= "</div>";
	
	print $carousel;
}

function featured_carousel_image_style($slide_num){
	global $featured_carousel_settings, $featured_carousel_slides;
	
	$settings = $featured_carousel_settings;
	$data = $featured_carousel_slides[$slide_num];
	
	// Determine if there is an image
	$has_image = !empty( $data['img'] );
	
	// Get Image Dimensions
	$img_height = 100;
	$img_width = 100;
	
	// TODO: Add Image Widths for Bottom, Top, Below and Above

	if( $settings['content_width'] != 100 and !$settings['overlap'] ){
		$img_width = 100 - $settings['content_width'];
	}
	if( $settings['content_height'] != 100 and !$settings['overlap'] ){
		$img_height = 100 - $settings['content_height']; 
	}
	
	// Add Image if Exists
	$image_style = "style='".( $has_image ? "background-image:url($data[img]);" : "" );
	
	// Set Image Height and Width
	$image_style .= " height:".$img_height."%; width:".$img_width."%;";

	// Crop Image if Specified
	$image_style .= " background-size:".($data['crop'] ? "100% auto;" : "contain;");
	
	// Set Background Color
	$image_style .= " background-color:#$settings[image_color];";
	
	// Check if Text Overlaps Image
	if( $settings['overlap'] ){
		// DO NOTHING	
	}else{ // Text doesn't overlap image
		if( $settings['content_loc'] == 'left' ){
			$image_style .= " float:right;";
		}elseif( $settings['content_loc'] == 'right' ){
			$image_style .= " float:left;";
		}elseif( $settings['content_loc'] == 'bottom'){
			$content_style .= " position: absolute; top: 0%; ";
		}elseif( $settings['content_loc'] == 'top'){

		}elseif( $settings['content_loc'] == 'below'){

		}elseif( $settings['content_loc'] == 'above'){

		} 

		// TODO: Add Content Location Bottom, Top, Below and Above
	}
	
	// Set Image Location
	$image_style .= " background-position:$settings[image_loc];'";
	
	return $image_style;
}

function featured_carousel_content_style($slide_num){
	global $featured_carousel_settings, $featured_carousel_slides;
	
	$settings = $featured_carousel_settings;
	
	
	// Get Opacity Value	
	if( $settings['overlap'] or $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above'){
		$opacity = $settings['opacity']/100;
	}else{
		$opacity = 1;
	}

	// Create Background Color Value	
	$clr = $settings['content_color'];
	$red = hexdec( $clr[0].$clr[1] );
	$green = hexdec( $clr[2].$clr[3] );
	$blue = hexdec( $clr[4].$clr[5] );

	$bgcolor = "rgba($red, $green, $blue, $opacity)";

	// TODO: Add Content Height and Width for Bottom, Top, Below and Above
		
	// Get Width and Height Values
	if( $settings['content_loc'] == 'left' or $settings['content_loc'] == 'right' ){
		$width = $settings['content_width']."%";
		$height = $settings['content_height']."%";
	}	
	
	// Build Content Style from Settings
	$content_style = "style='width:$width; height:$height; background-color:$bgcolor; color: #$settings[text_color]; padding: $settings[content_pad]px;";
	
	// Make sure Controller doesn't cover Text
	if( $settings['control_visible'] and strpos( $settings['control_loc'], 'content' ) !== false ){
		if( strpos( $settings['control_loc'], 'top' ) !== false ){
			$content_style .= "padding-top: 2.5em;";
		}else{
			$content_style .= "padding-bottom: 2.5em;";
		}
	}
	
	// Check if Text Overlaps Image
	if( $settings['overlap'] ){
		// DO NOTHING	
	}else{ // Text doesn't overlap image
		if( $settings['content_loc'] == 'left' ){
			$content_style .= " float: right;";
		}elseif( $settings['content_loc'] == 'right' ){
			$content_style .= " float: left;";
		}elseif( $settings['content_loc'] == 'bottom'){
			$content_style .= " position: absolute; bottom: 0%; ";
		}elseif( $settings['content_loc'] == 'top'){

		}elseif( $settings['content_loc'] == 'below'){

		}elseif( $settings['content_loc'] == 'above'){

		} 
		// TODO: Add Content Location of Below, Above, Bottom and Top
	}
	
	$content_style .= "'";
	
	return $content_style;
}

function featured_carousel_controller_style(){
	global $featured_carousel_settings;

	$settings = $featured_carousel_settings;
	
	$controller_style = array(0 => "style='", 1 => "style='");
	
	// TODO: Add Number Visibility Option	
	$controller_style[0] .= "z-index: 5; text-align: center;";
	
	// Add Controller Padding 
	$controller_style[0] .= " padding: $settings[control_padding]px;"; 

	// Add Controller Background Color
	$clr = $settings['control_color'];
	$red = hexdec( $clr[0].$clr[1] );
	$green = hexdec( $clr[2].$clr[3] );
	$blue = hexdec( $clr[4].$clr[5] );
	$opacity = $settings['control_opacity'] / 100;

	$background = "rgba($red, $green, $blue, $opacity)";

	$controller_style[0] .= " background-color:$background;";	
	
	$controller_style[0] .= " position: absolute;z-index:20;";

	if( strpos( $settings['control_loc'], 'middle' ) !== false ){
		$controller_style[1] .= " font-size: .75em;";
	}else{
		$controller_style[0] .= " font-size: .75em;";
	}

	$controller_style[0] .= " width: ".($settings['slide_count'] * 2.5)."em;";
	$controller_style[1] .= " width: ".($settings['slide_count'] * 2.5)."em;";

	// TODO: Add Top, Bottom, Above and Below Content Location Styles

	switch( $settings['control_loc'] ){
		case 'content_top_left':
			if( $settings['content_loc'] == 'left'){
				$controller_style[0] .= " top: 0px; left: 0px;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[0] .= " top: 0px;  left:".( 100 - $settings['content_width'] )."%;";
			}elseif( $settings['content_loc'] == 'top' ){
	
			}elseif( $settings['content_loc'] == 'bottom' ){

			}elseif( $settings['content_loc'] == 'above' ){

			}elseif( $settings['content_loc'] == 'below' ){
			
			}
				
			$controller_style[0] .= " border-bottom-right-radius: 6px;";


			break;

		case 'content_top_middle':
			// Centering Element can be done by using two divs inner and outer, both with the width of the controller, center the outer div then use margin -50% on the inner div to center appropriately. :)
			if( $settings['content_loc'] == 'left'){
				$controller_style[1] .= " display: inline-block; position: absolute; top: 0px; right:".( 100 - $settings['content_width']/2 )."%; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " top: 0px; right: -50%;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[1] .= " display: inline-block; position: absolute; top: 0px; right: ".( $settings['content_width']/2 )."%; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " top: 0px; right: -50%; ";
			}elseif( $settings['content_loc'] == 'top' ){
	
			}elseif( $settings['content_loc'] == 'bottom' ){

			}elseif( $settings['content_loc'] == 'above' ){

			}elseif( $settings['content_loc'] == 'below' ){
			
			}

			$controller_style[0] .= " border-bottom-left-radius: 6px; border-bottom-right-radius: 6px;";

			break;

		case 'content_top_right':
			if( $settings['content_loc'] == 'left'){
				$controller_style[0] .= " top: 0px; right:".( 100 - $settings['content_width'] )."%;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[0] .= " top: 0px; right: 0px;";
			}elseif( $settings['content_loc'] == 'top' ){
	
			}elseif( $settings['content_loc'] == 'bottom' ){

			}elseif( $settings['content_loc'] == 'above' ){

			}elseif( $settings['content_loc'] == 'below' ){
			
			}
				
			$controller_style[0] .= " border-bottom-left-radius: 6px;";


			break;

		case 'content_bottom_left':
			if( $settings['content_loc'] == 'left'){
				$controller_style[0] .= " bottom: 0px; left: 0px;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[0] .= " bottom: 0px; left:".( 100 - $settings['content_width'] )."%;";
			}elseif( $settings['content_loc'] == 'top' ){
	
			}elseif( $settings['content_loc'] == 'bottom' ){

			}elseif( $settings['content_loc'] == 'above' ){

			}elseif( $settings['content_loc'] == 'below' ){
			
			}
				
			$controller_style[0] .= " border-top-right-radius: 6px;";


			break;

		case 'content_bottom_middle':
			// Centering Element can be done by using two divs inner and outer, both with the width of the controller, center the outer div then use margin -50% on the inner div to center appropriately. :)
			if( $settings['content_loc'] == 'left'){
				$controller_style[1] .= " display: inline-block; position: absolute; bottom: 0px; right:".( 100 - $settings['content_width']/2 )."%; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " bottom: 0px; right: -50%; ";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[1] .= " display: inline-block; position: absolute; bottom: 0px; right: ".( $settings['content_width']/2 )."%; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " bottom: 0px; right: -50%; ";
			}elseif( $settings['content_loc'] == 'top' ){
	
			}elseif( $settings['content_loc'] == 'bottom' ){

			}elseif( $settings['content_loc'] == 'above' ){

			}elseif( $settings['content_loc'] == 'below' ){
			
			}
			
			$controller_style[0] .= " border-top-right-radius: 6px; border-top-left-radius: 6px;";

			break;

		case 'content_bottom_right':
			if( $settings['content_loc'] == 'left'){
				$controller_style[0] .= " bottom: 0px; right:".( 100 - $settings['content_width'] )."%;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[0] .= " bottom: 0px; right: 0px;";
			}elseif( $settings['content_loc'] == 'top' ){
	
			}elseif( $settings['content_loc'] == 'bottom' ){

			}elseif( $settings['content_loc'] == 'above' ){

			}elseif( $settings['content_loc'] == 'below' ){
			
			}
				
			$controller_style[0] .= " border-top-left-radius: 6px;";

			break;

		case 'image_top_left':
			if( $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above' or $settings['content_loc'] == 'bottom' or $settings['content_loc'] == 'right' or $settings['overlap'] == true ){
				$controller_style[0] .= " top: 0px; left: 0px;";
			}elseif( $settings['content_loc'] == 'left' ){
				$controller_style[0] .= " top: 0px; left: ".$settings['content_width']."%;";
			}elseif( $settings['content_loc'] == 'top' ){
				$controller_style[0] .= " top: ".$settings['content_height']."%; left: 0px;";
			}
			
			$controller_style[0] .= " border-bottom-right-radius: 6px;";

			break;

		case( 'image_top_middle' ):
			if( $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above' or $settings['content_loc'] == 'bottom' or $settings['overlap'] == true ){
				$controller_style[1] .= " position: absolute; top: 0px; left: 50%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " left: -50%; ";

			}elseif( $settings['content_loc'] == 'left' ){
				$controller_style[1] .= " position: absolute; top: 0px; right: ".((100 - $settings['content_width'])/2)."%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " right: -50%; ";

			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[1] .= " position: absolute; top: 0px; left: ".((100 - $settings['content_width'])/2)."%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " left: -50%; ";

			}elseif( $settings['content_loc'] == 'top' ){
				$controller_style[1] .= " position: absolute; top: ".$settings['content_height']."%; left: 50%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " left: -50%; ";

			}

			$controller_style[0] .= " border-bottom-right-radius: 6px; border-bottom-left-radius: 6px;";
			
			break;

		case( 'image_top_right' ):
			if( $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above' or $settings['content_loc'] == 'left' or $settings['content_loc'] == 'bottom' or $settings['overlap'] == true ){
				$controller_style[0] .= " top: 0px; right: 0px;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[0] .= " top: 0px; right: ".$settings['content_width']."%;";
			}elseif( $settings['content_loc'] == 'top' ){
				$controller_style[0] .= " top: ".$settings['content_height']."%; left: 0px;";
			}

			$controller_style[0] .= " border-bottom-left-radius: 6px;";

			break;

		case( 'image_bottom_left' ):
			if( $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above' or $settings['content_loc'] == 'right' or $settings['overlap'] == true ){
				$controller_style[0] .= " bottom: 0px; left: 0px;";
			}elseif( $settings['content_loc'] == 'left' ){
				$controller_style[0] .= " bottom: 0px; left: $settings[content_width]%;";
			}elseif( $settings['content_loc'] == 'bottom' ){
				$controller_style[0] .= " bottom: $settings[content_height]%; left: 0px;";
			}

			$controller_style[0] .= " border-top-right-radius: 6px;";

			break;

		case( 'image_bottom_middle' ):
			if( $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above' or $settings['content_loc'] == 'top' or $settings['overlap'] == true ){
				$controller_style[1] .= " position: absolute; bottom: 0px; left: 50%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " bottom: 0px; left: -50%; ";
				
			}elseif( $settings['content_loc'] == 'left' ){
				$controller_style[1] .= " position: absolute; bottom: 0px; right: ".((100 - $settings['content_width'])/2)."%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " bottom: 0px; right: -50%; ";
				
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[1] .= " position: absolute; bottom: 0px; left: ".((100 - $settings['content_width'])/2)."%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " bottom: 0px; left: -50%; ";
			
			}elseif( $settings['content_loc'] == 'bottom' ){
				$controller_style[1] .= " position: absolute; bottom: ".$settings['content_height']."%; left: 50%; display: inline-block; padding-left: $settings[control_padding]px; padding-right: $settings[control_padding]px;";
				$controller_style[0] .= " bottom: ".$settings['content_height']."%; left: -50%; ";

			}

			$controller_style[0] .= " border-top-right-radius: 6px; border-top-left-radius: 6px;";
	
			break;

		case( 'image_bottom_right' ):
			if( $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above' or $settings['content_loc'] == 'left' or $settings['content_loc'] == 'top' or $settings['overlap'] == true ){
				$controller_style[0] .= " bottom: 0px; right: 0px;";
			}elseif( $settings['content_loc'] == 'right' ){
				$controller_style[0] .= " bottom: 0px; right: $settings[content_width]%;";
			}elseif( $settings['content_loc'] == 'bottom' ){
				$controller_style[0] .= " bottom: $settings[content_height]%; left: 0px;";
			}

			$controller_style[0] .= " border-top-left-radius: 6px;";
			
			break;

	}

	$controller_style[0] .= "'";
	$controller_style[1] .= "'";
	
	

	return $controller_style;
}
/*******************
* HELPER FUNCTIONS *
********************/
##
## Obtains the first ~150 character string of visible text from a post's content
##	$post_id	- required 	- The post id of the post that the string is to be retrieved from
##
function featured_carousel_page_intro($post_id){
	$post = get_post($post_id);
	
	$post_title = $post->post_title;
	$excerpt = $post->post_excerpt;
	
	$content = $post->post_content;
	
	$first_string = substr( strip_tags($content), 0, strpos($content, " ", 150)+1 )."...";
	
	return $first_string;
}

##
## Adds help tags around the string given and returns the new help tag
##	$string 	- required 	- Help string to be displayed in popup box
##
function featured_carousel_help_tag($string){
	$tag = " <a class='help'>Help<span class='help_text'>$string</span></a>";
	
	return $tag;
}

##
## Triggers an Error
## 	$e_string 	- required 	- Error String to Display when Error Triggered
## 	$args 		- optional 	- Array of Variables to be displayed at the end of Error String
##
function featured_carousel_error($e_string, $args){
	
	if( !empty( $args ) ){
	
		// Build a string of the Arguments Passed
		$arg_string = "{";
		
		foreach( $args as $key => $val ){
			$arg_string .= sprintf( "%s => %s", $key, $val );
			
			if( $val != end( $args ) ){
				$arg_string .= ",";
			}
		}
	
	}else{
		$arg_string = "";
	}
	
	$e_string = $e_string." %s";
	
	// Trigger an Error and Display Args Passed
	trigger_error( vsprintf( $e_string, $arg_string ) );
}

## Function Taken From:
## http://betterwp.net/wordpress-tips/url_to_postid-for-custom-post-types/
##
## Converts Post URLs to IDs, supports custom post types 
## 	$url 		- required 	- URL to be converted to Post ID
##
function my_bwp_url_to_postid($url){
    global $wp_rewrite;
 
    $url = apply_filters('url_to_postid', $url);
 
    // First, check to see if there is a 'p=N' or 'page_id=N' to match against
    if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) ){
        $id = absint($values[2]);
        if ( $id ){
            return $id;
		}
    }
 
    // Check to see if we are using rewrite rules
    $rewrite = $wp_rewrite->wp_rewrite_rules();
 
    // Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
    if ( empty($rewrite) ){
        return 0;
	}
 
    // Get rid of the #anchor
    $url_split = explode('#', $url);
    $url = $url_split[0];
 
    // Get rid of URL ?query=string
    $url_split = explode('?', $url);
    $url = $url_split[0];
 
    // Add 'www.' if it is absent and should be there
    if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') ){
        $url = str_replace('://', '://www.', $url);
	}
 
    // Strip 'www.' if it is present and shouldn't be
    if ( false === strpos(home_url(), '://www.') ){
        $url = str_replace('://www.', '://', $url);
	}
	
    // Strip 'index.php/' if we're not using path info permalinks
    if ( !$wp_rewrite->using_index_permalinks() ){
        $url = str_replace('index.php/', '', $url);
	}
	
    if ( false !== strpos($url, home_url()) ){
        // Chop off http://domain.com
        $url = str_replace(home_url(), '', $url);
    }else{
        // Chop off /path/to/blog
        $home_path = parse_url(home_url());
        $home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
        $url = str_replace($home_path, '', $url);
    }
 
    // Trim leading and lagging slashes
    $url = trim($url, '/');
 
    $request = $url;
    // Look for matches.
    $request_match = $request;
    foreach ( (array)$rewrite as $match => $query){
        // If the requesting file is the anchor of the match, prepend it
        // to the path info.
        if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) ){
            $request_match = $url . '/' . $request;
		}
 
        if ( preg_match("!^$match!", $request_match, $matches) ){
            // Got a match.
            // Trim the query of everything up to the '?'.
            $query = preg_replace("!^.+\?!", '', $query);
 
            // Substitute the substring matches into the query.
            $query = addslashes(WP_MatchesMapRegex::apply($query, $matches));
 
            // Filter out non-public query vars
            global $wp;
            parse_str($query, $query_vars);
            $query = array();
            
			foreach ( (array) $query_vars as $key => $value ){
                if ( in_array($key, $wp->public_query_vars) ){
                    $query[$key] = $value;
				}
            }
 
			// Taken from class-wp.php
			foreach ( $GLOBALS['wp_post_types'] as $post_type => $t ){
				if ( $t->query_var ){
					$post_type_query_vars[$t->query_var] = $post_type;
				}
			}
 
			foreach ( $wp->public_query_vars as $wpvar ) {
				if ( isset( $wp->extra_query_vars[$wpvar] ) ){
					$query[$wpvar] = $wp->extra_query_vars[$wpvar];
				}elseif ( isset( $_POST[$wpvar] ) ){
					$query[$wpvar] = $_POST[$wpvar];
				}elseif ( isset( $_GET[$wpvar] ) ){
					$query[$wpvar] = $_GET[$wpvar];
				}elseif ( isset( $query_vars[$wpvar] ) ){
					$query[$wpvar] = $query_vars[$wpvar];
				}
	 
				if ( !empty( $query[$wpvar] ) ) {
					if ( ! is_array( $query[$wpvar] ) ) {
						$query[$wpvar] = (string) $query[$wpvar];
					} else {
						foreach ( $query[$wpvar] as $vkey => $v ) {
							if ( !is_object( $v ) ) {
								$query[$wpvar][$vkey] = (string) $v;
							}
						}
					}
	 
					if ( isset($post_type_query_vars[$wpvar] ) ) {
						$query['post_type'] = $post_type_query_vars[$wpvar];
						$query['name'] = $query[$wpvar];
					}
				}
			}
 
            // Do the query
            $query = new WP_Query($query);
            if ( !empty($query->posts) && $query->is_singular ){
                return $query->post->ID;
            }else{
                return 0;
			}
        }
    }
    return 0;
}


?>
