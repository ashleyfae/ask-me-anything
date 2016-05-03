<?php
/*
 * Plugin Name: Ask Me Anything
 * Plugin URI: https://www.nosegraze.com
 * Description: Allow your readers to submit questions.
 * Version: 1.0
 * Author: Nose Graze
 * Author URI: https://www.nosegraze.com
 * License: GPL2
 * 
 * Ask Me Anything is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Ask Me Anything is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ask Me Anything. If not, see <http://www.gnu.org/licenses/>.
 *
 * Thanks to Easy Digital Downloads for serving as a great code base
 * and resource, which a lot of Ask Me Anything's structure is based on.
 * Easy Digital Downloads is made by Pippin Williamson and licensed
 * under GPL2+.
 * 
 * @package ask-me-anything
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license GPL2+
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ask_Me_Anything' ) ) :

	class Ask_Me_Anything {

		/**
		 * Ask_Me_Anything object
		 *
		 * @var Ask_Me_Anything Instance of the Ask_Me_Anything class.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Roles Object
		 *
		 * @var Ask_Me_Anything_Roles
		 * @since 1.0.0
		 */
		public $roles;

		/**
		 * Ask_Me_Anything instance.
		 *
		 * Insures that only one instance of Ask_Me_Anything exists at any one time.
		 *
		 * @uses   Ask_Me_Anything::setup_constants() Set up the plugin constants.
		 * @uses   Ask_Me_Anything::includes() Include any required files.
		 * @uses   Ask_Me_Anything::load_textdomain() Load the language files.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return Ask_Me_Anything Instance of Ask_Me_Anything class
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! self::$instance instanceof Ask_Me_Anything ) {
				self::$instance = new Ask_Me_Anything;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();
				self::$instance->roles = new Ask_Me_Anything_Roles();

				add_action( 'init', array( self::$instance, 'license' ) );
			}

			return self::$instance;

		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @access protected
		 * @since  1.0.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ask-me-anything' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access protected
		 * @since  1.0.0
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ask-me-anything' ), '1.0.0' );
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since  1.0.0
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'ASK_ME_ANYTHING_VERSION' ) ) {
				define( 'ASK_ME_ANYTHING_VERSION', '1.0.0' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'ASK_ME_ANYTHING_PLUGIN_DIR' ) ) {
				define( 'ASK_ME_ANYTHING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'ASK_ME_ANYTHING_PLUGIN_URL' ) ) {
				define( 'ASK_ME_ANYTHING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'ASK_ME_ANYTHING_PLUGIN_FILE' ) ) {
				define( 'ASK_ME_ANYTHING_PLUGIN_FILE', __FILE__ );
			}

			// Store URL where plugin was purchased.
			if ( ! defined( 'NOSE_GRAZE_STORE_URL' ) ) {
				define( 'NOSE_GRAZE_STORE_URL', 'https://shop.nosegraze.com' );
			}

		}

		/**
		 * Include Required Files
		 *
		 * @access private
		 * @since  1.0.0
		 * @return void
		 */
		private function includes() {

			global $ask_me_anything_options;

			// Settings
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
			if ( empty( $ask_me_anything_options ) ) {
				$ask_me_anything_options = ask_me_anything_get_settings();
			}

			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/ajax.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/assets.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/class-ama-question.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/class-ask-me-anything-license.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/class-ask-me-anything-roles.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/post-statuses.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/post-types.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/question-functions.php';
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/template-functions.php';

			if ( is_admin() ) {
				require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/admin-pages.php';
				require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/class-ask-me-anything-notices.php';
				require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
				require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/question/columns.php';
				require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/question/meta-box.php';
				require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/admin/question/sanitize.php';
			}

			// Install
			require_once ASK_ME_ANYTHING_PLUGIN_DIR . 'includes/install.php';

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			$lang_dir = dirname( plugin_basename( ASK_ME_ANYTHING_PLUGIN_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'ask-me-anything/languages-directory', $lang_dir );
			load_plugin_textdomain( 'ask-me-anything', false, $lang_dir );

		}

		/**
		 * Set Up License
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function license() {

			if ( ! class_exists( 'Ask_Me_Anything_License' ) ) {
				return;
			}

			$ama_license = new Ask_Me_Anything_License( __FILE__, 'Ask Me Anything', ASK_ME_ANYTHING_VERSION, 'Nose Graze', 'ask_me_anything_license_key' );

		}

	}

endif;

/**
 * Get Ask_Me_Anything up and running.
 *
 * This function returns an instance of the Ask_Me_Anything class.
 *
 * @since 1.0.0
 * @return Ask_Me_Anything
 */
function Ask_Me_Anything() {
	return Ask_Me_Anything::instance();
}

Ask_Me_Anything();