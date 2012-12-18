<?php
/*
Plugin Name: Home Carousel
Plugin URI: 
Description: This plugin creates an image carousel from images that are in your gallery and text that is specified on the options page, and/or from the featured image and excerpt of posts on your site. The carousel is created using the jQuery Cycle plugin. You can set what is displayed in the carousel via the options page that is added to the admin menu. You can include the carousel on your homepage by using the <code>home_carousel_display();</code> template tag, which will generate all the necessary HTML for the carousel.
Version: 0.1
Author: Devlin Junker
Author URI: 
Last Updated: 12/11/2012

This plugin inherits the GPL license from it's parent system, WordPress.
*/


/*****************************************
* DEFINE VARIABLES TO BE USED FOR PLUGIN *
******************************************/
// Effects Array
$home_carousel_effects = array(
	'none'			=> 'None',
	'fade'			=> 'Fade',
	'cover' 		=> 'Cover',
	'toss'			=> 'Toss',
	'uncover'		=> 'Uncover',
	'wipe'			=> 'Wipe',
	'blindX' 		=> 'Blind X',
	'blindY' 		=> 'Blind Y',
	'scrollUp'		=> 'Scroll Up',
	'scrollDown'	=> 'Scroll Down',
	'scrollLeft'	=> 'Scroll Left',
	'scrollRight'	=> 'Scroll Right',
	'slideX'		=> 'Slide X',
	'slideY'		=> 'Slide Y',
);

$content_locations = array(
	'left' 			=> 'Left', 
	'right' 		=> 'Right',
	//'below' 		=> 'Below',
	//'above' 		=> 'Above'
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
	'b'				=> 'Bolded',
	'p'				=> 'Font Size',
);

$title_locs = array(
	'block' 		=> 'Seperate Line',
	'inline'		=> 'Same Line'
);

// Defaults settings for Carousel
$home_carousel_settings_defaults = apply_filters('home_carousel_settings_defaults', array(
	'slide_count' 	=> 2,
	
	// Container Settings
	'div' 			=> 'carousel',
	'height' 		=> 250,
	'width' 		=> 938,
	
	// Image Settings
	'image_loc'		=> 'center',
	'image_color' 	=> 'FFFFFF',
	
	// Content Settings
	'content_loc'	=> 'left',
	'content_height'=> 100,
	'content_width' => 25,
	'content_color' => '000000',
	'text_color' 	=> 'FFFFFF',
	'title_size'	=> 'h2',
	'title_location'=> 'block',
	'overlap' 		=> false,
	'opacity'		=> '40',
	
	// Controller Settings
	'control_loc'	=> 'top_right',
	'slide_nums'	=> true,
	'control_opacity'=>'100',
	'control_color' => '000000',
	'active_color'	=> '000000',
	'active_text'	=> 'FFFFFF',
	'inactive_color'=> 'FFFFFF',
	'inactive_text' => '000000',
	
	// Transition Settings
	'effect' 		=> 'fade',
	'delay' 		=> 3,
	'duration' 		=> 1,
));

// Defaults for Slides (no images and placeholder text)
$home_carousel_slides_defaults = apply_filters ('home_carousel_slides_defaults', array(
	's_1' => array(
				'url' 	=> Null,
				'title' => "PlaceHolder Title",
				'text' 	=> "This is the Home Carousel Plugin, you can edit the content of the carousel from the Home Carousel Tab in the Admin Menu",
				'img' 	=> Null,
				'crop' 	=> false
				),
	's_2' => array(
				'url' 	=> Null,
				'title' => "PlaceHolder Title",
				'text' 	=> "This is the Home Carousel Plugin, you can edit the content of the carousel from the Home Carousel Tab in the Admin Menu",
				'img' 	=> Null,
				'crop' 	=> false
				)
));

//	Pull the User Defined Settings from the DB
$home_carousel_settings = get_option('home_carousel_settings');
$home_carousel_slides = get_option('home_carousel_slides');

//	If there are no User Defined Settings, use Defaults
$home_carousel_settings = wp_parse_args($home_carousel_settings, $home_carousel_settings_defaults);
$home_carousel_slides = wp_parse_args($home_carousel_slides, $home_carousel_slides_defaults);


if( is_admin() ){
	add_action( 'admin_menu', 'add_home_carousel_menu' );
	add_action( 'admin_init', 'register_home_carousel_settings' );
}else{
	add_action( 'wp_enqueue_scripts', 'register_home_carousel_files' );
	add_action( 'wp_head', 'home_carousel_header_script' );
}

function register_home_carousel_files(){
	if( is_home() ){
		wp_register_style( 'home_carousel_styles', plugins_url('carousel.css', __FILE__) );
		wp_register_script( 'jquery_cycle', plugins_url('jquery.cycle.all.min.js', __FILE__) );
		wp_enqueue_style( 'home_carousel_styles' );
		wp_enqueue_script( 'jquery_cycle' );
	}
}

function home_carousel_header_script(){
	global $home_carousel_settings;
	
	print "<script type='text/javascript'>
	jQuery(document).ready(function($) {
		$('#$home_carousel_settings[div]').cycle({ 
			fx: '$home_carousel_settings[effect]',
			timeout: ".($home_carousel_settings['delay'] * 1000).",
			speed: ".($home_carousel_settings['duration'] * 1000).",
			pause: 1,
			fit: 1,
			pager: '#controller'
		});
	});
	</script>";
}

/********************************
* CAROUSEL OPTIONS AND SETTINGS *
*********************************/


##
## Adds the Home Carousel Tab to the Admin Menu
## 
function add_home_carousel_menu(){
	// Add Page to Menu
	$page = add_menu_page( 'Home Carousel', 'Home Carousel', 'manage_options', 'home_carousel', 'home_carousel_menu_page', "", "40.5");
	
	// Add Stylesheet to Page
	add_action( 'admin_print_styles-'.$page , 'home_carousel_options_styles'); 
}

##
## Adds Options Stylesheet to Plugin Options Page
##
function home_carousel_options_styles(){
	wp_enqueue_style('home_carousel_options_styles');
}

##
## Registers the Carousel Settings so Wordpress will handle the options page
##
function register_home_carousel_settings(){
	global $home_carousel_settings;
	
	global $home_carousel_effects, $content_locations, $image_locations, $title_sizes, $title_locs;
	
	// Register Stylesheets
	wp_register_style( 'home_carousel_options_styles', plugins_url('options.css', __FILE__) );
	
	//TODO: ADD SANATIZE FUNCTION
	register_setting( 'home_carousel_settings_group', 'home_carousel_settings');
	register_setting( 'home_carousel_settings_group', 'home_carousel_slides', 'sanatize_home_carousel_slides'); 
	
	
	/*** Add Section to Configure Slides ***/
	add_settings_section( 'home_carousel_slides', 'Slides', 'home_carousel_options_section', 'home_carousel' );
	
	// Add Field to Change Number of Slides
	add_settings_field( 'slide_count', 'Number of Slides', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_slides', array( 'setting_name' => 'slide_count', 'max_char' => 1, 'help' => 'Save Changes to Edit New Slides' ) );
	
	// Add Fields to Manipulate Slide Content (Adds the Number Specified in Settings)
	for($i = 1; $i <= $home_carousel_settings['slide_count']; $i++){
		add_settings_field ("slide_$i", "Slide $i", 'home_carousel_slide_input', 'home_carousel', 'home_carousel_slides', array( 'slide_num' => $i ) );
	}
	
	
	/*** Add Section to Configure Carousel Container ***/
	add_settings_section( 'home_carousel_container_settings', 'Container Settings', 'home_carousel_options_section', 'home_carousel' );
	
	// Add Container ID Field
	add_settings_field ( 'div', 'Container ID', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_container_settings', array( 'setting_name' => 'div' ) );
	
	// Add Container Width Field
	add_settings_field ( 'width', 'Container Width', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_container_settings', array( 'setting_name' => 'width', 'max_char' => 3, 'follow' => 'pixels' ) );
	
	// Add Container Height Field
	add_settings_field ( 'height', 'Container Height', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_container_settings', array( 'setting_name' => 'height', 'max_char' => 3, 'follow' => 'pixels' ) );
	
	
	/*** Add Section to Configure Content ***/
	add_settings_section( 'home_carousel_content_settings', 'Content Settings', 'home_carousel_options_section', 'home_carousel' );
	
	// Add Content Location
	add_settings_field( 'content_loc', 'Content Location', 'home_carousel_settings_dropdown', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'content_loc', 'options' => $content_locations ) );
	
	// Add Content Width Field
	add_settings_field ( 'content_width', 'Content Width', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'content_width', 'max_char' => 3, 'follow' => '%', 'explain' => 'Relative to Carousel Container Size' ) );
	
	// Add Content Height Field
	add_settings_field ( 'content_height', 'Content Height', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'content_height', 'max_char' => 3, 'follow' => '%', 'explain' => 'Relative to Carousel Container Size' ) );
	
	// Add Content Background Color Field
	add_settings_field( 'content_color', 'Content Background Color', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'content_color', 'max_char' => 6 ) );
	
	// Add Content Background Color Field
	add_settings_field( 'text_color', 'Content Text Color', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'text_color', 'max_char' => 6 ) );
	
	// Add Title Size Field
	add_settings_field( 'title_size', 'Title Size', 'home_carousel_settings_dropdown', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'title_size', 'options' => $title_sizes ) );
	
	// Add Title Location Field
	add_settings_field( 'title_loc', 'Title Location', 'home_carousel_settings_dropdown', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'title_loc', 'options' => $title_locs ) );
	
	// Add Content Overlap Field
	add_settings_field( 'overlap', 'Content Overlap', 'home_carousel_settings_checkbox', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'overlap', 'explain' => 'Check if content should overlap image' ) );
	
	// Add Content Opacity Field
	add_settings_field( 'opacity', 'Content Background Opacity', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_content_settings', array( 'setting_name' => 'opacity', 'max_char' => 3, 'follow' => '%', 'help' => 'The Background Opacity Value will only be used if the content <em>overlaps</em> the image or if the content is <em>below</em> or <em>above</em> the carousel' ) );
	
	/*** Add Section to Configure Image ***/
	add_settings_section( 'home_carousel_image_settings', 'Image Settings', 'home_carousel_options_section', 'home_carousel' );
	
	// Add Image Location Field
	add_settings_field( 'image_loc', 'Image Location', 'home_carousel_settings_dropdown', 'home_carousel', 'home_carousel_image_settings', array( 'setting_name' => 'image_loc', 'options' => $image_locations ) );
	
	// Add Image Background Color Field
	add_settings_field( 'image_color', 'Image Background Color', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_image_settings', array( 'setting_name' => 'image_color', 'max_char' => 6, 'explain' => 'Color to appear behind the image' ) );
	
	
	/*** Add Section to Configure Controller ***/
	add_settings_section( 'home_carousel_controller_settings', 'Controller Settings', 'home_carousel_options_section', 'home_carousel' );
	
	
	/*** Add Section to Configure Transition ***/
	add_settings_section( 'home_carousel_transition_settings', 'Transition Settings', 'home_carousel_options_section', 'home_carousel' );
	
	// Add Transition Effect Field
	add_settings_field( 'effect', 'Transition Effect', 'home_carousel_settings_dropdown', 'home_carousel', 'home_carousel_transition_settings', array( 'setting_name' => 'effect', 'options' => $home_carousel_effects, 'follow' => "<a href='http://jquery.malsup.com/cycle/browser.html' target='blank'>Demos</a>" ) );
	
	// Add Transition Delay Field
	add_settings_field( 'delay', 'Transition Delay', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_transition_settings', array( 'setting_name' => 'delay', 'max_char' => 1, 'explain' => 'Length of Time Each Slide is Visible', 'follow' => 'second(s)' ) );
	
	// Add Transition Duration Field
	add_settings_field ( 'duration', 'Transition Duration', 'home_carousel_settings_text_input', 'home_carousel', 'home_carousel_transition_settings', array( 'setting_name' => 'duration', 'max_char' => 1, 'explain' => 'Length of Time Each Transition Takes', 'follow' => 'second(s)' ) );
}

##
## Formats the Carousel Options Page
##
function home_carousel_menu_page(){
	// Top of Page
	print "<div class='wrap' id='home_carousel_options_page'>
				<h2> Home Carousel Plugin Options </h2>
				Options and Settings for the Home Carousel Plugin
				<form action='options.php' method='post'>";
				
	// Call Wordpress Functions to Build Options Page
	settings_fields( 'home_carousel_settings_group' );
	do_settings_sections( 'home_carousel' );
	
	// Bottom of Page
	print "		<input name='Submit' type='submit' value='Save Changes'/>
				</form>
			</div>";
}

##
## Displays Information at Top Each Settings Section Based on Section Title
##
function home_carousel_options_section($args){
	global $home_carousel_settings;
	switch( $args['title'] ):
		case "Slides":
			print "<p>Edit the slides to be displayed on the Carousel</p>";
			
			if( $home_carousel_settings['overlap'] or $home_carousel_settings['content_loc'] == 'below' or $home_carousel_settings['content_loc'] == 'above' ){
				$img_height = 100;
				$img_width = 100;
			}elseif($home_carousel_settings['content_loc'] == 'right' or $home_carousel_settings['content_loc'] == 'left' ){
				$img_width = 100 - $home_carousel_settings['content_width'];
				$img_height = 100;
			}else{
				$img_height = 100 - $home_carousel_settings['content_height']; 
				$img_width = 100;
			}
			
			$perfect_width = floor( $home_carousel_settings['width'] * $img_width / 100 );
			$perfect_height = floor( $home_carousel_settings['height'] * $img_height / 100 );
			
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
function home_carousel_slide_input($args){
	global $home_carousel_slides;
	
	// Get Which Slide Number this Input Corresponds to
	$slide_num = "s_".$args['slide_num'];
	
	if( !empty( $slide_num ) ){
		// Get Slide Info
		$url = $home_carousel_slides[$slide_num]['url'];
		$title = $home_carousel_slides[$slide_num]['title'];
		$text = $home_carousel_slides[$slide_num]['text'];
		$img = $home_carousel_slides[$slide_num]['img'];
		$cropped = $home_carousel_slides[$slide_num]['crop'];
		
		// Create Help Strings
		$title_help = "If left empty and the Slide URL is a page in your site, the Title will default to the Post Title. If it is an external page, the Title will default to the Web Page Title defined in the <code>&lt;title&gt;</code> tag.";
		$image_help = "If left empty and the Slide URL is a page in your site, the Image will default to the Post's Featured Image. If it is an external page, the Image will default to the first image found on the webpage.";
		$text_help = "If left empty and the Slide URL is a page in your site, the Text will default to the Post Excerpt. If it is an external page, the Text will be ommitted from the slide.";
		
		// Check if URL is in the Site
		$in_site = ( ($post_id = my_bwp_url_to_postid($url)) !== 0 );
		
		// TODO: Make 'Select Existing Page' Link Visible Once Implemented
		// Add URL Input
		$input = "<span class='slide_info_label'>URL:</span> <input class='slide_url_input' name='home_carousel_slides[$slide_num][url]' type='url' value='$url'/> <a style='display:none;' href=''>Select Existing Page</a>";
		
		$input .= "<br/>";
		
		// If URL is in The Site
		if( $in_site ){
			// Get Post Info
			$post = get_post($post_id);
			
			$post_title = $post->post_title;
			$excerpt = $post->post_excerpt;
			
			$content = $post->post_content;
			$first_string = home_carousel_page_intro($post_id);
		}
		
		
		// Add Title Input
		$input .= "<span class='slide_info_label'>Title:</span> <input class='slide_title_input' name='home_carousel_slides[$slide_num][title]' type='text' placeholder='$post_title' ".( isset($title) ? "value='$title'" : "")."/>".home_carousel_help_tag($title_help)."<br/>";
		
		// TODO: Make 'Select Existing Image' Link Visible Once Implemented
		// Add Image Input
		$input .= "<span class='slide_info_label'>Image URL:</span> <input class='slide_url_input' name='home_carousel_slides[$slide_num][img]' type='url' ".( isset($img) ? "value='$img'" : "")."/> <a style='display:none;' href=''>Select Existing Image</a>".home_carousel_help_tag($image_help)."<br/>";
		
		// Add Crop Checkbox
		$input .= "<span class='slide_info_label'>Crop?</span> <input class='slide_crop_input' name='home_carousel_slides[$slide_num][crop]' type='checkbox' value='true' ".($cropped ? 'checked' : '')."/><br/>";
		
		// Add Text Input
		$input .= "<span class='slide_info_label'>Text:<br/>".home_carousel_help_tag($text_help)."</span> <textarea class='slide_text_input' name='home_carousel_slides[$slide_num][text]' placeholder='".( !empty($excerpt) ? $excerpt : $first_string)."'>".( isset($text) ? $text : "")."</textarea><br/>";
		
		print $input;
	
	}else{
		// Trigger an Error
		home_carousel_error("Error: Slide Configuration Slide Number Not Found in Args", $args);
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
function home_carousel_settings_text_input($args){
	global $home_carousel_settings;
	
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
		$input .= "<input id='$setting_name' name='home_carousel_settings[$setting_name]'";
		
		if( !empty( $max_char ) ){
			$input .= "size='$max_char' maxlength='$max_char'";
		}
		
		$input .= "type='text' value='$home_carousel_settings[$setting_name]'/>";
		
		// Add Following Text If Set
		if( !empty( $following ) ){
			$input .= " <span class='follow_text'>$following</span>";
		}
		
		// Add Help Text If Set
		if( !empty( $help_text ) ){
			$input .= home_carousel_help_tag($help_text);
		}
		
		print $input;
	
	// If Setting Name NOT set
	}else{
		// Trigger an Error
		home_carousel_error("Error: Text Input Setting Name Not Found in Args", $args);
	}
}

##
## Builds a text input field for a Carousel Setting Field
## 	@setting_name 	- required 	- Name of Setting this field corresponds to
##	@explain 		- optional 	- Explanation Text that appears before Field
##	@follow 		- optional 	- Text that Follows the Field
##	@help 			- optional	- Help Text that is displayed when hovering over help bubble
##
function home_carousel_settings_checkbox($args){
	global $home_carousel_settings;
	
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
		$input .= "<input id='$setting_name' name='home_carousel_settings[$setting_name]' type='checkbox' value='true' ".($home_carousel_settings[$setting_name] ? 'checked': '')."/>";
		
		// Add Following Text If Set
		if( !empty( $following ) ){
			$input .= " <span class='follow_text'>$following</span>";
		}
		
		// Add Help Text If Set
		if( !empty( $help_text ) ){
			$input .= home_carousel_help_tag($help_text);
		}
		
		print $input;
	
	// If Setting Name NOT set
	}else{
		// Trigger an Error
		home_carousel_error("Error: Text Input Setting Name Not Found in Args", $args);
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
function home_carousel_settings_dropdown($args){
	global $home_carousel_settings;
	
	// Get the Setting Name this Dropdown corresponds to
	$setting_name = $args['setting_name'];
	
	// Get Dropdown Options
	$options = $args['options'];
	
	// Get Current Option Selected
	$current_val = $home_carousel_settings[$setting_name];
	
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
		$input .= "<select id='$setting_name' name='home_carousel_settings[$setting_name]'>";
		
		foreach( $options as $value => $text ){
			$input .= "<option value='$value' ".($value == $current_val ? "selected" : "").">$text</option>";
		}
		
		$input .= "</select>";
		
		// Add Following Text If Set
		if( !empty( $following ) ){
			$input .= " <span class='follow_text'>$following</span>";
		}
		
		// Add Help Text If Set
		if( !empty( $help_text ) ){
			$input .= home_carousel_help_tag($help_text);
		}
		
		print $input;
		
	// If Setting Name NOT set
	}else{
		// Trigger an Error
		home_carousel_error("Error: Dropdown Setting Name Not Found in Args", $args);
	}
}

## 
## Cleans and Fills in Missing Data
##	$input 	- required - auto 	- Data to be cleaned and filled in
##
function sanatize_home_carousel_slides($input){
	global $home_carousel_settings;
	
	$i = 0;
	
	foreach( $input as $slide_num => $data ){
		// Check if supposed to have data
		if( $i < $home_carousel_settings['slide_count'] ){
			// Sanatize the Link URL
			$input[$slide_num]['url'] = clean_url( $data['url'] );
			
			
			$in_site = (($post_id = my_bwp_url_to_postid( $input[$slide_num]['url'] )) != 0);
			
			
			if( $in_site ){
				if( $data['text'] == "" ){
					// If blank text, get page intro
					$input[$slide_num]['text'] = home_carousel_page_intro( $post_id );
				}else{
					// TODO: SANATIZE STRING
				}
				
				
				if( $data['img'] == "" ){
					// If image URL blank, get Post Thumbnail
					if( has_post_thumbnail( $post_id ) ){
						$info = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
						
						$input[$slide_num]['img'] = $info[0];
					}else{
						// TODO: DO SOMETHING IF NO THUMBNAIL
					}
					
				}else{
					// Otherwise Clean Image URL
					$input[$slide_num]['img'] = clean_url( $data['img'] ); 
				}
				
				
				if( $data['title'] == "" ){
					// If title blank, get post title
					$input[$slide_num]['title'] = get_the_title( $post_id );
				}else{
					// TODO: SANATIZE STRING
				}
			}else{ 
				// Not In Site
				// TODO: SANATIZE/FILL IN NON-SITE DATA
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

/****************************
* CAROUSEL DISPLAY FUNCTION *
*****************************/

##
## Displays the Home Carousel where this function is called
##
function home_carousel_display(){
	global $home_carousel_settings, $home_carousel_slides;
	
	$settings = $home_carousel_settings;
	
	// Wrapper: contains carousel and controller
	$carousel = "<div id='carousel_wrap'>";
	
	// Carousel
	$carousel .= "<div id='$home_carousel_settings[div]' style='height:$settings[height]px;width:$settings[width]px;'>";
	
	$i = 0; 
	
	foreach( $home_carousel_slides as $slide_num => $data ){
		
		// Only Display Number of Slides Specified in Settings
		if( $i < $settings['slide_count'] ){
			
			// Start Slide
			$carousel .= "<div id='$slide_num' style='height:100%;width:100%;'>";
			
			// Start Link if slide has Link URL
			if( !empty( $data['url'] ) ){
				$carousel .= "<a href='$data[url]' title='$data[title]'>";
			}
			
			// Get Image and Content Style
			$image_style = home_carousel_image_style($slide_num);
			$content_style = home_carousel_content_style($slide_num);
			
			// Get Title Size and Location
			$title_tag = $settings['title_size'];
			
			$title_tag .= " style='display:$settings[title_loc]'";
			
			// Start Image
			$carousel .= "<div id='slide_image' $image_style>";
			
			if( !$settings['overlap'] ){ // If no Overlap
				$carousel .= "</div>"; // End Image 
			}
			
			// Display Slide Content
			$carousel .= "<div id='slide_content' $content_style><$title_tag>$data[title]:</$title_tag><p style='display:$settings[title_loc];'> $data[text]</p></div>";
			
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
	
	// Place Controller
	$carousel .= "<div id='controller'></div>";
	
	// End Carousel Wrapper
	$carousel .= "</div>";
	
	print $carousel;
}

function home_carousel_image_style($slide_num){
	global $home_carousel_settings, $home_carousel_slides;
	
	$settings = $home_carousel_settings;
	$data = $home_carousel_slides[$slide_num];
	
	// Determine if there is an image
	$has_image = !empty( $data['img'] );
	
	// Get Image Dimensions
	if( $settings['overlap'] ){
		$img_height = 100;
		$img_width = 100;
	}else{
		if( $settings['content-width'] != 100 ){
			$img_width = 100 - $settings['content_width'];
		}elseif( $settings['content-height'] != 100 ){
			$img_height = 100 - $settings['content_height']; 
		}
	}
	
	// Add Image if Exists
	$image_style = "style='".( $has_image ? "background-image:url($data[img]);" : "" );
	
	// Crop Image if Specified
	$image_style .= "background-size:".($data['crop'] ? "100% auto;" : "contain;");
	
	// Set Background Color
	$image_style .= "background-color:$settings[image_color];";
	
	// Check if Text Overlaps Image
	if( $settings['overlap'] ){
		
	}else{ // Text doesn't overlap image
		if( $settings['content_loc'] == 'left' ){
			$image_style .= " float:right;";
		}elseif( $settings['content_loc'] == 'right' ){
			$image_style .= " float:left;";
		}
	}
	
	// Set Image Location
	$image_style .= " background-position:$settings[image_loc];";
	
	// Set Image Height and Width
	$image_style .= " height:".$img_height."px; width:".$img_width."%;'";
	
	return $image_style;
}

function home_carousel_content_style($slide_num){
	global $home_carousel_settings, $home_carousel_slides;
	
	$settings = $home_carousel_settings;
	
	$clr = $settings['content_color'];
	
	if( $settings['overlap'] or $settings['content_loc'] == 'below' or $settings['content_loc'] == 'above'){
		$opacity = $settings['opacity']/100;
	}else{
		$opacity = 1;
	}
	
	
	// Build Content Style from Settings
	$content_style = "style='width:$settings[content_width]%; height:$settings[content_height]%; background-color:rgba($clr[0]$clr[1], $clr[2]$clr[3], $clr[4]$clr[5], $opacity); color: #$settings[text_color]; padding: 20px;";
	
	// Check if Text Overlaps Image
	if( $settings['overlap'] ){
		
	}else{ // Text doesn't overlap image
		if( $settings['content_loc'] == 'left' ){
			$content_style .= " float:right;";
		}elseif( $settings['content_loc'] == 'right' ){
			$content_style .= " float:left;";
		}
	}
	
	$content_style .= "'";
	
	return $content_style;
}

/*******************
* HELPER FUNCTIONS *
********************/
##
## Obtains the first ~150 character string of visible text from a post's content
##	$post_id	- required 	- The post id of the post that the string is to be retrieved from
##
function home_carousel_page_intro($post_id){
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
function home_carousel_help_tag($string){
	$tag = " <a class='help'>Help<span class='help_text'>$string</span></a>";
	
	return $tag;
}

##
## Triggers an Error
## 	$e_string 	- required 	- Error String to Display when Error Triggered
## 	$args 		- optional 	- Array of Variables to be displayed at the end of Error String
##
function home_carousel_error($e_string, $args){
	
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

##
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