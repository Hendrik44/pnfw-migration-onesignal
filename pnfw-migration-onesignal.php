<?php
/**
 * Plugin Name: PNFW Migration OneSignal
 * Plugin URI:  https://www.jg-bits.de
 * Description: Easy migrate your Push-Users from Pushnotifications for WordPress-Plugin to OneSingal using register/unregister-Route and redirect to OneSingal
 * Version:     0.1.0
 * Author:      JG-Bits UG (haftungsbeschränkt)
 * Author URI:  https://www.jg-bits.de
 * Donate link: https://www.jg-bits.de
 * License:     MIT
 * Text Domain: pnfw-migration-onesignal
 * Domain Path: /languages
 *
 * @link    https://www.jg-bits.de
 *
 * @package PNFW_Migration_OneSignal
 * @version 0.1.0
 *
 */

/**
 * Copyright (c) 2017 JG-Bits UG (haftungsbeschränkt) (email : info@jg-bits.de)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Autoloads files with classes when needed.
 *
 * @since  0.1.0
 * @param  string $class_name Name of the class being requested.
 */
function pnfw_migration_onesignal_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'PNFWMOS_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'PNFWMOS_' ) ) ) );

	// Include our file.
	PNFW_Migration_OneSignal::include_file( 'includes/class-' . $filename );
}
spl_autoload_register( 'pnfw_migration_onesignal_autoload_classes' );

/**
 * Main initiation class.
 *
 * @since  0.1.0
 */
final class PNFW_Migration_OneSignal {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.1.0
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    PNFW_Migration_OneSignal
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	protected static $oneSingal_app_id = "YOUR-ONESIGNAL-APP-ID";

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.1.0
	 * @return  PNFW_Migration_OneSignal A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 */
	public function plugin_classes() {
		// $this->plugin_class = new PNFWMOS_Plugin_Class( $this );

	} // END OF PLUGIN CLASSES FUNCTION

	
	/**
	 * add rewrite rules/url mapping for register and unregister route.
	 *
	 * @since  0.1.0
	 * 
	 * @access public
	 * @return void
	 */
	function manage_routes() {
  		add_rewrite_rule('pnfw/([^/]+)/?$', 'index.php?control_action=$matches[1]', 'top');
  		flush_rewrite_rules();
 	}
 	
 	/**
 	 * manage_routes_query_vars function.
 	 *
	 * @since  0.1.0
 	 * 
 	 * @access public
 	 * @param mixed $query_vars
 	 * @return void
 	 */
 	function manage_routes_query_vars($query_vars) {
  		array_push($query_vars, 'control_action');
  		return $query_vars;
 	}
 	
 	/**
 	 * managing the register/unregister routes and the action for this routes.
 	 *
	 * @since  0.1.0
 	 * 
 	 * @access public
 	 * @return void
 	 */
 	function front_controller() {
  		global $wp_query;

  		$control_action = isset($wp_query->query_vars['control_action']) ? $wp_query->query_vars['control_action'] : '';

  		$res = strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ? $_POST : $_GET;

		if (empty($res) || empty($res["token"]) || !isset($res["os"]) || ($res["os"] != "iOS" && $res["os"] != "android") ) {
			header('HTTP/1.1 500 Internal Server Error');
			$response = array(
   				'error' => "parameter in reguest missing or wrong",
   				'data' => $res
  			);
  			echo json_encode($response);
  			exit;
		}

		switch ($control_action) {
			case 'register':
				$this->register($res);
				exit;
			break;
			case 'unregister':
				$result = $this->register($res);
				if ($result->success == true) {
					$this->unregister($result->id, $res->token);
				}
				exit;
			break;
		}
	}
	
	/**
	 * register/subscribe devive at onesingal push service.
	 *
	 * @since  0.1.0
	 * 
	 * @access public
	 * @param mixed $request_data
	 * @return void
	 */
	public function register($request_data) {
		$fields = array( 
			'app_id' => self::$oneSingal_app_id,
			'identifier' => $request_data["token"], 
			"device_type" => 0
			); 

		$fields = json_encode($fields); 

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/players"); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_POST, TRUE); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

		$response = curl_exec($ch); 
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$return = json_decode( $response );

		if ( $httpcode != 200 ) {
			header('HTTP/1.1 500 Internal Server Error');
			$response = array(
   				'error' => "wrong statuscode register device",
   				'data' => $return
  			);
  			echo json_encode( $response );
  			exit;
		} else {
			return $return;   
		}
	}

	/**
	 * unregister/unsubscribe device from push service/onesignal.
	 *
	 * @since  0.1.0
	 * 
	 * @access public
	 * @param mixed $playerID
	 * @param mixed $token
	 * @return void
	 */
	public function unregister($playerID, $token) {

		$fields = array( 
			'app_id' => self::$oneSingal_app_id,
			"identifier" => $token,
			"notification_types" => -2
		); 
		$fields = json_encode($fields); 

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/players/'.$playerID); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HEADER, TRUE); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  		curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
		
		$response = curl_exec($ch); 
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch); 

		$resultData = json_decode( $response );
		
		if ( $httpcode != 200 ) {
			header('HTTP/1.1 500 Internal Server Error');
			$response = array(
   				'error' => "wrong statuscode unregister device",
   				'data' => $resultData
  			);
  			echo json_encode($response);
  			exit;
		} else {
			return $resultData;
		}
	}

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {
		add_filter('query_vars', array($this, 'manage_routes_query_vars'));
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action('template_redirect', array($this, 'front_controller'));
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  0.1.0
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.1.0
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
		flush_rewrite_rules();
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'pnfw-migration-onesignal', false, dirname( $this->basename ) . '/languages/' );

		// Initialize plugin classes.
		$this->plugin_classes();

		$this->manage_routes();
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.1.0
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.1.0
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		$default_message = sprintf( __( 'PNFW Migration OneSignal is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'pnfw-migration-onesignal' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the PNFW_Migration_OneSignal object and return it.
 * Wrapper for PNFW_Migration_OneSignal::get_instance().
 *
 * @since  0.1.0
 * @return PNFW_Migration_OneSignal  Singleton instance of plugin class.
 */
function pnfw_migration_onesignal() {
	return PNFW_Migration_OneSignal::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( pnfw_migration_onesignal(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( pnfw_migration_onesignal(), '_activate' ) );
register_deactivation_hook( __FILE__, array( pnfw_migration_onesignal(), '_deactivate' ) );
