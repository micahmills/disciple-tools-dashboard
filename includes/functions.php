<?php
/**
 * Functions class
 */


class DT_Dashboard_Plugin_Functions {
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    private $version = 1;
    private $context = "dt-dashboard";
    private $namespace;

    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_filter( 'dt_front_page', [ $this, 'front_page' ] );

        add_filter( 'desktop_navbar_menu_options', [ $this, 'nav_menu' ], 10, 1 );
        add_filter( 'off_canvas_menu_options', [ $this, 'nav_menu' ] );

        $url_path = dt_get_url_path();

        add_action( "template_redirect", [ $this, 'my_theme_redirect' ] );
        if ( strpos( $url_path, 'dashboard' ) !== false ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
        }
    }

    public function my_theme_redirect() {
        $url = dt_get_url_path();
        if ( strpos( $url, "dashboard" ) !== false ) {
            $plugin_dir = dirname( __FILE__ );
            $path = $plugin_dir . '/template-dashboard.php';
            status_header( 200 );
            include( $path );
            die();
        }
    }

    public function scripts() {
        wp_enqueue_style( 'dashboard-css', plugin_dir_url( __FILE__ ) . '/style.css', [], filemtime( plugin_dir_path( __FILE__ ) . 'style.css' ) );
        wp_enqueue_style( 'font-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], 1 );
        wp_enqueue_style( 'switchery', 'https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.css', [], '0.8.2' );
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, '4' );
        wp_register_script( 'switchery', 'https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js', [], '0.8.2' );
        wp_enqueue_script( 'dt-dashboard-plugin', plugin_dir_url( __FILE__ ) . 'plugin.js', [ 'switchery', ], filemtime( plugin_dir_path( __FILE__ ) . '/plugin.js' ) );
        wp_register_script( 'smooth-scrollbar', 'https://cdnjs.cloudflare.com/ajax/libs/smooth-scrollbar/8.6.3/smooth-scrollbar.js', false, '4' );
        wp_register_script( 'smooth-scrollbar', 'https://cdnjs.cloudflare.com/ajax/libs/smooth-scrollbar/8.6.3/smooth-scrollbar.js', false, '4' );
        wp_localize_script(
            'dt-dashboard-plugin', 'wpApiDashboard', [
                'root'                    => esc_url_raw( rest_url() ),
                'site_url'                => get_site_url(),
                'nonce'                   => wp_create_nonce( 'wp_rest' ),
                'current_user_login'      => wp_get_current_user()->user_login,
                'current_user_id'         => get_current_user_id(),
                'template_dir'            => get_template_directory_uri(),
                'translations'            => DT_Dashboard_Plugin_Endpoints::instance()->translations(),
                'data'                    => DT_Dashboard_Plugin_Endpoints::instance()->get_data(),
                'workload_status'         => get_user_option( 'workload_status', get_current_user_id() ),
                'workload_status_options' => dt_get_site_custom_lists()["user_workload_status"] ?? [],
                'tiles'                   => DT_Dashboard_Plugin_Tiles::instance()->all()
            ]
        );
        wp_enqueue_script( 'dt-dashboard', plugin_dir_url( __FILE__ ) . 'dashboard.js', [
            'dt-dashboard-plugin',
            'jquery',
            'jquery-ui',
            'lodash',
            'amcharts-core',
            'amcharts-charts',
            'amcharts-animated',
            'moment',
            'smooth-scrollbar'
        ], filemtime( plugin_dir_path( __FILE__ ) . '/dashboard.js' ), true );
    }

    public function front_page( $page ) {
        return site_url( '/dashboard/' );
    }

    public function nav_menu( $tabs ) {
        $tabs['dashboard'] = [
            "link"  => site_url( '/dashboard/' ),
            "label" => __( "Dashboard", "disciple-tools-dashboard" )
        ];
        return $tabs;

    }
}
