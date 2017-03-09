<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Name: BuddyBlog Map Field
 * Version: 1.0.0-alpha
 * Plugin URI: https://buddydev.com
 * Author: Brajesh Singh
 * Author URI: https://buddydev.com
 * Description: Allow adding/displaying location on the single post
 */

class BBlog_Map_Field_Helper {

	public function __construct() {
		$this->setup();
	}


	private function setup() {

		add_action( 'bp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_filter( 'buddyblog_post_form_settings', array( $this, 'modify_settings' ) );

		add_action( 'bsfep_before_taxonomy_terms', array( $this, 'add_gmap_on_edit' ) );

		//after buddyblog single post
		//add_action( 'buddyblog_after_blog_post', array( $this, 'add_map_details' ) );
		add_filter( 'the_content', array( $this, 'add_map_details' ) );

	}


	/**
     * Add our map fields as meta key
     *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function modify_settings( $settings ) {

		$custom_fields = isset( $settings['custom_fields'] ) ? $settings['custom_fields'] : array();
		//hidden meta keys(using _)
		$custom_fields['_location'] = array(
			'type'    => 'hidden',
			'label'   => 'Location',
			'default' => '',
		);

		$custom_fields['_geo_lat'] = array(
			'type'    => 'hidden',
			'label'   => 'Latitude',
			'default' => '',
		);

		$custom_fields['_geo_lng'] = array(
			'type'    => 'hidden',
			'label'   => 'Longitude',
			'default' => '',
		);

		$settings['custom_fields'] = $custom_fields;

		return $settings;

	}


	//container for the google map
	public function add_gmap_on_edit() { ?>
        <h3>Location</h3>
        <!--for google map -->
        <input id="pac-input" class="controls" type="text" placeholder="Search Box">
        <div id="bblog-map-edit-canvas">

        </div>
        <style type="text/css">
            #bblog-map-edit-canvas {
                width:100%;
                min-height: 400px;
            }
        </style>
	<?php }


	public function load_assets() {
        $load = false;
	    if ( bp_is_user() && bp_is_current_component( 'buddyblog' )
             || function_exists( 'buddyblog_is_buddyblog_post' ) && is_singular() && buddyblog_is_buddyblog_post( get_queried_object_id() ) ) {
	        $load = true;
        }

		if (  $load ) {
			$this->url = plugin_dir_url( __FILE__ );

			//Please use your own API Key
			$api_key = 'YOUR GOOGLE API KEY';//'Google Map API key';//Please update it

			$gmap_url = "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places";
			//load google map lib
			wp_enqueue_script( 'gmap-js', $gmap_url );

			wp_register_script( 'bblog-map-field-js' , $this->url. 'assets/bblog-map-field.js', array( 'jquery' ) );
			wp_enqueue_script( 'bblog-map-field-js'  );

		}

	}

    //inject it somewhere in the post
    //we can use output buffer and add it to the_content too
	public function add_map_details( $content ) {
	    if ( ! function_exists('buddyblog_is_buddyblog_post' ) || ! buddyblog_is_buddyblog_post( get_the_ID() ) ) {
	        return $content;
        }
	    $geo = array();//we are using array as we may want to allow listing of maps on teh archive page too
        $geo[] = $this->get_geo_info( get_the_ID() );
        ob_start();
	    ?>
        <div id="bblog-map-canvas-<?php the_ID(); ?>" class="bblog-map-canvas">

        </div>

	    <script type="text/javascript">
            bblog_posts_geo = '<?php echo json_encode( $geo ); ?>';
        </script>
        <style type="text/css">
            .bblog-map-canvas {
                width: 100%;
                height: 400px;
            }
        </style>

<?php
		$content = $content . ob_get_clean();
        return $content;
    }

	public function get_geo_info( $post_id ) {

		$location = get_post_meta( $post_id, '_location', true );
		$lat = get_post_meta( $post_id, '_geo_lat', true );
		$lng = get_post_meta( $post_id, '_geo_lng', true );
		$id = $post_id;
		if ( empty( $location ) ) {
			return '';
		}

		return compact( 'id',  'location', 'lat', 'lng' );

	}
}

new BBlog_Map_Field_Helper();