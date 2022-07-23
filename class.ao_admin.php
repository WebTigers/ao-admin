<?php

class AOAdmin {

    public static $default_categories = [
        'ao-gallery'   => 'AO Gallery',
        'ao-document'  => 'AO Document',
        'ao-media'     => 'AO Media',
        'ao-image'     => 'AO Image',
        'ao-audio'     => 'AO Audio',
        'ao-video'     => 'AO Video',
        'ao-headshot'  => 'AO Headshot',
    ];

    public static $response;

    public static function init ( ) {

        self::setupZendDB();

        # Add Action Hooks #
        add_action( 'admin_menu', [ 'AOAdmin', 'ao_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ 'AOAdmin', 'register_ao_plugin_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ 'AOAdmin', 'load_ao_plugin_scripts' ] );

        # Ajax-specific Action Hooks #
        add_action( 'wp_ajax_ao_api', [ 'AOAdmin', 'ao_api' ]);

        # Add Filter Hooks #
        add_filter( 'admin_body_class', [ 'AOAdmin', 'ao_admin_body_classes' ] );

        register_taxonomy_for_object_type( 'category', 'attachment' );
        register_taxonomy_for_object_type( 'post_tag', 'attachment' );

    }

    public static function plugin_activation ( ) {

        self::createDefaultAOCategories();
        if ( is_admin() ) {
            new AOPluginUpdate( __FILE__, 'myGitHubUsername', 'Repo-Name' );
        }

    }

    public static function createDefaultAOCategories ( ) {

        /** Returns an array of WP_Term objects. */
        $existingCategories = wp_get_post_categories();

        /**
        object (WP_Term) (11) {
            ["term_id"]=>           // int
            ["name"]=>              // string
            ["slug"]=>              // string
            ["term_group"]=>        // int
            ["term_taxonomy_id"]=>  // int
            ["taxonomy"]=>          // string
            ["description"]=>       // string
            ["parent"]=>            // int
            ["count"]=>             // int
            ["filter"] =            // string
            ["meta"] = []           // an array of meta fields.
        }
        */

        foreach ( self::$default_categories as $slug => $name ) {

            if ( ! get_category_by_slug( $slug ) ) {
                $response = wp_insert_category([
                    'taxonomy'              => 'category',
                    'cat_name'              => $name,
                    'category_nicename'     => $slug,
                    'category_description'  => 'Do not remove. This is a 3AO category used by the 3AO themes and plugins.',
                    'category_parent'       => '',
                ], true );

                if ( is_wp_error( $response ) ) {
                    pr( $response->get_error_messages() );
                }

            }

        }

    }

    public static function plugin_deactivation ( ) {

    }

    public static function setupZendDB ( ) {

        # Sets the Zend_Db_Adapter so we can use Zend_Db_Table for DB calls. #
        $db = Zend_Db::factory('Pdo_Mysql', [
            'host'             => DB_HOST,
            'username'         => DB_USER,
            'password'         => DB_PASSWORD,
            'dbname'           => DB_NAME,
            // 'adapterNamespace' => 'Zend_Db'
        ]);
        Zend_Registry::set('db', $db);

        /**
        try{

            $tableModel = new AOModelUserMeta([
                'db' => 'db',
                'name'=> $GLOBALS['wpdb']->prefix . '_usermeta',
                'primary' => 'umeta_id'
            ]);
            $tableModel->getAdapter()->getConnection();

        }
        catch ( Exception | Error $e ) {

            pr( $e->getMessage() );

        }
        */

    }

    public static function view ( $name, array $args = [] ) {

        $args = apply_filters( 'ao_admin_view_arguments', $args, $name );

        foreach ( $args AS $key => $val ) {
            $$key = $val;
        }

        load_plugin_textdomain( 'ao-admin' );

        $file = AO_ADMIN_PLUGIN_DIR . 'views/'. $name . '.phtml';

        include( $file );

    }

    public static function ajaxResponse () {

        // print( json_encode( self::$response, JSON_HEX_QUOT ) );
        header('Content-Type: application/json; charset=utf-8');
        echo  str_replace( 'u0022', '\u0022', json_encode( self::$response ) );
        die();

    }

    public static function ao_admin_menu() {

        add_menu_page(
            __( 'Dashboard', 'ao-textdomain' ),
            __( '3AO Dashboard', 'ao-textdomain' ),
            'manage_options',
            '3ao-dashboard',
            ['AOAdmin', 'ao_admin_page_contents'],
            'dashicons-schedule',
            1
        );

    }

    public static function ao_admin_page_contents() {

        AOAdmin::view('admin', [ 'test' => 'Welcome to the 3AO Dashboard.'] );

    }

    public static function register_ao_plugin_scripts() {

        # Add Bootstrap 5.2.x #
        wp_register_style( 'ao-bootstrap-css', plugins_url( 'ao-admin/assets/vendor/bootstrap/css/bootstrap.min.css' ) );

        # Template Styles #
        wp_register_style( 'ao-google-font-poppins-css','https://fonts.googleapis.com/css?family=Lato:300,400,400i,700|Poppins:300,400,500,600,700|PT+Serif:400,400i&display=swap' );
        wp_register_style( 'ao-style-css', plugins_url( 'ao-admin/assets/css/style.css' ) );
        wp_register_style( 'ao-dark-css', plugins_url( 'ao-admin/assets/css/dark.css' ) );
        wp_register_style( 'ao-font-icons-css', plugins_url( 'ao-admin/assets/css/font-icons.css' ) );
        wp_register_style( 'ao-animate-css', plugins_url( 'ao-admin/assets/css/animate.css' ) );
        wp_register_style( 'ao-magnific-popup-css', plugins_url( 'ao-admin/assets/css/magnific-popup.css' ) );
        wp_register_style( 'ao-summernote-css', plugins_url( 'ao-admin/assets/vendor/summernote/summernote.css' ) );
        wp_register_style( 'ao-pretty-photo-css', plugins_url( 'ao-admin/assets/vendor/prettyPhoto/css/prettyPhoto.css' ) );
        wp_register_style( 'ao-dropzone-css', plugins_url( 'ao-admin/assets/vendor/dropzone/dropzone.min.css' ) );

        # Scripts #
        wp_register_script( 'ao-bootstrap-js', plugins_url( 'ao-admin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js' ) );
        wp_register_script( 'ao-plugins-js', plugins_url( 'ao-admin/assets/js/plugins.js' ) );
        wp_register_script( 'ao-functions-js', plugins_url( 'ao-admin/assets/js/functions.js' ) );
        wp_register_script( 'ao-cookie-js', plugins_url( 'ao-admin/assets/vendor/js-cookie/js-cookie.min.js' ) );
        wp_register_script( 'ao-color-picker-js', plugins_url( 'ao-admin/assets/js/js.colorpicker.min.js' ) );
        wp_register_script( 'ao-tigerDOM-js', plugins_url( 'ao-admin/assets/js/tigerDOM.js' ) );
        wp_register_script( 'ao-summernote-js', plugins_url( 'ao-admin/assets/vendor/summernote/summernote.js' ) );
        wp_register_script( 'ao-pretty-photo-js', plugins_url( 'ao-admin/assets/vendor/prettyPhoto/js/jquery.prettyPhoto.js' ) );
        wp_register_script( 'ao-clipboard-js', plugins_url( 'ao-admin/assets/js/clipboard.min.js' ) );
        wp_register_script( 'ao-moment-js', plugins_url( 'ao-admin/assets/js/moment.min.js' ) );
        wp_register_script( 'ao-dropzone-js', plugins_url( 'ao-admin/assets/vendor/dropzone/dropzone.min.js' ) );
        wp_register_script( 'ao-media-admin-gallery-js', plugins_url( 'ao-admin/assets/js/mediaAdminGallery.js' ) );
        wp_register_script( 'ao-media-admin-documents-js', plugins_url( 'ao-admin/assets/js/mediaAdminDocuments.js' ) );
        wp_register_script( 'ao-media-admin-media-js', plugins_url( 'ao-admin/assets/js/mediaAdminMedia.js' ) );

        # Add 3AO Custom CSS
        wp_register_style( 'ao-colors-css', plugins_url( 'ao-admin/assets/css/colors.css' ) );
        wp_register_style( 'ao-admin-css', plugins_url( 'ao-admin/assets/css/admin.css' ) );

        # Add 3AO Custom JS
        wp_register_script( 'ao-admin-js', plugins_url( 'ao-admin/assets/js/admin.js' ) );

    }

    public static function load_ao_plugin_scripts( $hook ) {

        // Load only on ?page=3ao-dashboard
        if( $hook !== 'toplevel_page_3ao-dashboard' ) {
            return;
        }

        # Load Styles #
        wp_enqueue_style( 'ao-bootstrap-css' );
        wp_enqueue_style( 'ao-google-font-poppins-css' );
        wp_enqueue_style( 'ao-style-css' );
        wp_enqueue_style( 'ao-dark-css' );
        wp_enqueue_style( 'ao-font-icons-css' );
        wp_enqueue_style( 'ao-animate-css' );
        wp_enqueue_style( 'ao-magnific-popup-css' );
        wp_enqueue_style( 'ao-summernote-css' );
        wp_enqueue_style( 'ao-pretty-photo-css' );
        wp_enqueue_style( 'ao-dropzone-css' );

        # 3AO Custom Admin #
        wp_enqueue_style( 'ao-colors-css' );
        wp_enqueue_style( 'ao-admin-css' );

        # Load Scripts #
        wp_enqueue_script( 'ao-bootstrap-js' );
        wp_enqueue_script( 'ao-plugins-js' );
        wp_enqueue_script( 'ao-functions-js' );
        wp_enqueue_script( 'ao-cookie-js' );
        wp_enqueue_script( 'ao-color-picker-js' );
        wp_enqueue_script( 'ao-tigerDOM-js' );
        wp_enqueue_script( 'ao-summernote-js' );
        wp_enqueue_script( 'ao-pretty-photo-js' );
        wp_enqueue_script( 'ao-clipboard-js' );
        wp_enqueue_script( 'ao-moment-js' );
        wp_enqueue_script( 'ao-dropzone-js' );
        wp_enqueue_script( 'ao-media-admin-gallery-js' );
        wp_enqueue_script( 'ao-media-admin-documents-js' );
        wp_enqueue_script( 'ao-media-admin-media-js' );

        wp_enqueue_script( 'ao-admin-js' );

        // add_action('wp_head', 'ao_meta_tags');

    }

    public static function ao_meta_tags() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';
    }

    public static function ao_admin_body_classes ( $classes ) {

        $classes .= ' stretched invisible';

        if ( ! empty( $_SESSION['themeStyle'] ) ) {
            $classes .= ' ' . $_SESSION['themeStyle'];
        }

        return $classes;

    }

    public static function get_current_admin_url() {

        return admin_url ( sprintf ( basename( $_SERVER['REQUEST_URI'] ) ) );

    }

    public static function get_admin_page_url( $page ) {
        // https://3ao-beaudev.com/wp-admin/admin.php?page=3ao-dashboard
        return admin_url() . 'admin.php?page=' . $page;
    }

    public static function ao_api ( ) {

        /** Route the request to the right service class and method. */
        if (
            ! empty( $_REQUEST['service'] ) &&
            ! empty( $_REQUEST['method'] ) &&
            class_exists( $_REQUEST['service'] ) &&
            method_exists( $_REQUEST['service'], $_REQUEST['method'] )
        ) {

            $service = new $_REQUEST['service']( $_REQUEST );
            self::$response = $service->getResponse();
            self::ajaxResponse();

        }

        exit();

    }

    public static function ao_upload() {



    }

}