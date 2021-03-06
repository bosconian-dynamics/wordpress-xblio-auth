<?php
/**
 * ReduxFramework Sample Config File
 * For full documentation, please visit: http://docs.reduxframework.com/
 */

if ( ! class_exists( 'Redux' ) ) {
    return;
}

$opt_name = 'bd_xblio_auth';

/**
 * ---> SET ARGUMENTS
 * All the possible arguments for Redux.
 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
 * */
Redux::set_args(
    $opt_name,
    [
        // STANDARD
        'opt_name'             => $opt_name,               // This is where your data is stored in the database and also becomes your global variable name.
        'display_name'         => 'XBL.io Authentication', // Name that appears at the top of your panel
        'display_version'      => '0.0.1',                 // Version that appears at the top of your panel
        'menu_type'            => 'submenu',               // Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
        'allow_sub_menu'       => false,                   // Show the sections below the admin menu item or not
        'menu_title'           => 'XBL.io Auth',
        'page_title'           => 'XBL.io Authentication',
        'admin_bar'            => false,                   // Show the panel pages on the admin bar
        //'admin_bar_icon'       => 'dashicons-controller',  // Choose an icon for the admin bar menu
        'admin_bar_priority'   => 10,                      // Choose an priority for the admin bar menu
        //'global_variable'      => 'bd_xblio_auth',         // Set a different name for your global variable other than the opt_name
        'dev_mode'             => defined('WP_DEBUG') && WP_DEBUG,                    // Show the time the page took to load, etc
        'update_notice'        => true,                    // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
        'customizer'           => false,                   // Enable basic customizer support
        //'open_expanded'        => true,                    // Allow you to start the panel in an expanded way initially.
        //'disable_save_warn'    => true,                    // Disable the save warning when a user changes a field

        // OPTIONAL -> Give you extra features
        'page_priority'        => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
        'page_parent'          => 'options-general.php',   // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
        'page_permissions'     => 'manage_options',        // Permissions needed to access the options panel.
        'menu_icon'            => '',                      // Specify a custom URL to an icon
        'last_tab'             => '',                      // Force your panel to always open to a specific tab (by id)
        'page_icon'            => 'icon-controller',       // Icon displayed in the admin panel next to your menu_title
        //'page_slug'            => 'redux_demo',          // Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
        'save_defaults'        => true,                    // On load save the defaults to DB before user clicks save or not
        'default_show'         => false,                   // If true, shows the default value next to each field that is not the default value.
        'default_mark'         => '*',                     // What to print by the field's title if the value shown is default. Suggested: *
        'show_import_export'   => true,                    // Shows the Import/Export panel when not used as a field.

        // CAREFUL -> These options are for advanced use only
        'transient_time'       => 60 * MINUTE_IN_SECONDS,
        'output'               => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
        'output_tag'           => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
        // 'footer_credit'     => '',                      // Disable the footer credit of Redux. Please leave if you can help it.

        // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
        'database'             => '',                      // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
        'use_cdn'              => true,                    // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.
    ]
);

//TODO: Global Args: removed hints, intro text, footer text - revisit later

//TODO: removed Help panels

/*
    *
    * ---> START SECTIONS
    *
    */

// -> START Basic Fields
Redux::set_section( $opt_name, [
    'title'  => 'General',
    'id'     => 'general',
    //'desc'   => __( 'These are really basic fields!', 'redux-framework-demo' ),
    'icon'   => 'el el-home',
    'fields' => [
        [
            'id'       => 'public_key',
            'type'     => 'text',
            'title'    => __( 'Public Key', 'redux-framework-demo' ),
            'subtitle' => __( 'From xbl.io custom app', 'redux-framework-demo' ),
            //'desc'     => __( 'Field Description', 'redux-framework-demo' ),
            'default'  => ''
        ],
        [
            'id'       => 'force_xbl_avatar',
            'type'     => 'checkbox',
            'title'    => __( 'Force Xbox Live Avatar', 'redux-framework-demo' ),
            'subtitle' => __( 'Replace user avatars with Xbox Live avatar if available', 'redux-framework-demo' ),
            'default'  => '1'
        ],
        [
            'id'       => 'auth_success_redirect',
            'type'     => 'text',
            'title'    => __( 'Auth Success Redirect', 'redux-framework-demo' ),
            'subtitle' => __( 'URL to direct a user to on successful authentication', 'redux-framework-demo' ),
            //'desc'     => __( 'Field Description', 'redux-framework-demo' ),
            'default'  => '/'
        ]
    ]
 ] );


if ( file_exists( dirname( __FILE__ ) . '/../README.md' ) ) {
    $section = array(
        'icon'   => 'el el-list-alt',
        'title'  => __( 'Documentation', 'redux-framework-demo' ),
        'fields' => array(
            array(
                'id'       => '17',
                'type'     => 'raw',
                'markdown' => true,
                'content_path' => dirname( __FILE__ ) . '/../README.md', // FULL PATH, not relative please
                //'content' => 'Raw content here',
            ),
        ),
    );
    Redux::set_section( $opt_name, $section );
}
/*
    * <--- END SECTIONS
    */


/*
    *
    * YOU MUST PREFIX THE FUNCTIONS BELOW AND ACTION FUNCTION CALLS OR ANY OTHER CONFIG MAY OVERRIDE YOUR CODE.
    *
    */

/*
*
* --> Action hook examples
*
*/

// If Redux is running as a plugin, this will remove the demo notice and links
//add_action( 'redux/loaded', 'remove_demo' );

// Function to test the compiler hook and demo CSS output.
// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
//add_filter('redux/options/' . $opt_name . '/compiler', 'compiler_action', 10, 3);

// Change the arguments after they've been declared, but before the panel is created
//add_filter('redux/options/' . $opt_name . '/args', 'change_arguments' );

// Change the default value of a field after it's been set, but before it's been useds
//add_filter('redux/options/' . $opt_name . '/defaults', 'change_defaults' );

// Dynamically add a section. Can be also used to modify sections/fields
//add_filter('redux/options/' . $opt_name . '/sections', 'dynamic_section');

/**
 * This is a test function that will let you see when the compiler hook occurs.
 * It only runs if a field    set with compiler=>true is changed.
 * */
if ( ! function_exists( 'compiler_action' ) ) {
    function compiler_action( $options, $css, $changed_values ) {
        echo '<h1>The compiler hook has run!</h1>';
        echo "<pre>";
        print_r( $changed_values ); // Values that have changed since the last save
        echo "</pre>";
        //print_r($options); //Option values
        //print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )
    }
}

/**
 * Custom function for the callback validation referenced above
 * */
if ( ! function_exists( 'redux_validate_callback_function' ) ) {
    function redux_validate_callback_function( $field, $value, $existing_value ) {
        $error   = false;
        $warning = false;

        //do your validation
        if ( $value == 1 ) {
            $error = true;
            $value = $existing_value;
        } elseif ( $value == 2 ) {
            $warning = true;
            $value   = $existing_value;
        }

        $return['value'] = $value;

        if ( $error == true ) {
            $field['msg']    = 'your custom error message';
            $return['error'] = $field;
        }

        if ( $warning == true ) {
            $field['msg']      = 'your custom warning message';
            $return['warning'] = $field;
        }

        return $return;
    }
}

/**
 * Custom function for the callback referenced above
 */
if ( ! function_exists( 'redux_my_custom_field' ) ) {
    function redux_my_custom_field( $field, $value ) {
        print_r( $field );
        echo '<br/>';
        print_r( $value );
    }
}

/**
 * Custom function for filtering the sections array. Good for child themes to override or add to the sections.
 * Simply include this function in the child themes functions.php file.
 * NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
 * so you must use get_template_directory_uri() if you want to use any of the built in icons
 * */
if ( ! function_exists( 'dynamic_section' ) ) {
    function dynamic_section( $sections ) {
        //$sections = array();
        $sections[] = array(
            'title'  => __( 'Section via hook', 'redux-framework-demo' ),
            'desc'   => __( '<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo' ),
            'icon'   => 'el el-paper-clip',
            // Leave this as a blank section, no options just some intro text set above.
            'fields' => array()
        );

        return $sections;
    }
}

/**
 * Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.
 * */
if ( ! function_exists( 'change_arguments' ) ) {
    function change_arguments( $args ) {
        //$args['dev_mode'] = true;

        return $args;
    }
}

/**
 * Filter hook for filtering the default value of any given field. Very useful in development mode.
 * */
if ( ! function_exists( 'change_defaults' ) ) {
    function change_defaults( $defaults ) {
        $defaults['str_replace'] = 'Testing filter hook!';

        return $defaults;
    }
}

/**
 * Removes the demo link and the notice of integrated demo from the redux-framework plugin
 */
if ( ! function_exists( 'remove_demo' ) ) {
    function remove_demo() {
        // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
        if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
            remove_filter( 'plugin_row_meta', array(
                ReduxFrameworkPlugin::instance(),
                'plugin_metalinks'
            ), null, 2 );

            // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
            remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
        }
    }
}

