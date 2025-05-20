<?php
/**
 * Plugin Name: Enhanced Custom Structured Pages
 * Description: Stores rich page content in a custom table and exposes it through the WP REST API with extended functionality.
 * Version:     1.0.0
 * Author:      You
 */

if (!defined('ABSPATH')) {
    exit;
}

class Enhanced_CSP_Plugin {

    const VERSION     = '1.0.0';
    const TABLE       = 'enhanced_csp_pages';
    const NAMESPACE   = 'customcms/v1';

    public function __construct() {
    register_activation_hook(__FILE__, [$this, 'activate']);
    
    // Add this line to check and update the database schema on every page load
    add_action('plugins_loaded', [$this, 'update_database_schema']);
    
    add_action('rest_api_init', [$this, 'register_routes']);
    
    // Add this line to register the enhanced category endpoint
    add_action('rest_api_init', [$this, 'fix_category_rest_endpoint'], 20);
    
    // Add admin menu for the CMS
    add_action('admin_menu', [$this, 'add_admin_menu']);
    
    // Add admin assets
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    
    // Include front-end template functionality
    require_once plugin_dir_path(__FILE__) . 'includes/front-end-template.php';
}


/**
 * On plugin activation: create / update the table.
 */
public function activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // First, get the correct table names with proper prefixes
    $table = $wpdb->prefix . self::TABLE;
    $category_table = $wpdb->prefix . self::TABLE . '_categories';
    
    // Log the actual table names being created for debugging
    error_log('Creating tables: ' . $table . ' and ' . $category_table);

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Main pages table - No comments in SQL to avoid dbDelta parsing issues
    $sql = "CREATE TABLE $table (
        id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        slug            VARCHAR(191)    NOT NULL,
        status          VARCHAR(20)     NOT NULL DEFAULT 'draft',
        meta_title      VARCHAR(255)    DEFAULT NULL,
        meta_desc       TEXT            DEFAULT NULL,
        og_title        VARCHAR(255)    DEFAULT NULL,
        og_desc         TEXT            DEFAULT NULL,
        og_image        VARCHAR(255)    DEFAULT NULL,
        canonical       VARCHAR(255)    DEFAULT NULL,
        robots          VARCHAR(100)    DEFAULT NULL,
        in_sitemap      TINYINT(1)      DEFAULT 1,
        h1              VARCHAR(255)    DEFAULT NULL,
        hero_image      VARCHAR(255)    DEFAULT NULL,
        intro_text      MEDIUMTEXT      DEFAULT NULL,
        sections        LONGTEXT        DEFAULT NULL,
        conclusion_heading VARCHAR(255) DEFAULT NULL,
        conclusion_content MEDIUMTEXT   DEFAULT NULL,
        banner_heading  VARCHAR(255)    DEFAULT NULL,
        banner_description TEXT          DEFAULT NULL,
        banner_service  VARCHAR(100)    DEFAULT NULL,
        faq_items       LONGTEXT        DEFAULT NULL,
        video_components LONGTEXT       DEFAULT NULL,
        breadcrumbs     LONGTEXT        DEFAULT NULL,
        region          VARCHAR(100)    DEFAULT NULL,
        service         VARCHAR(100)    DEFAULT NULL,
        sub_service     VARCHAR(100)    DEFAULT NULL,
        content_type    VARCHAR(100)    DEFAULT NULL,
        category_id     BIGINT UNSIGNED DEFAULT NULL,
        in_header_menu  TINYINT(1)      DEFAULT 0,
        author_name     VARCHAR(100)    DEFAULT NULL,
        author_bio      MEDIUMTEXT      DEFAULT NULL,
        author_image    VARCHAR(255)    DEFAULT NULL,
        author_social_links LONGTEXT    DEFAULT NULL,
        editor_name     VARCHAR(100)    DEFAULT NULL,
        created         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published       DATETIME        DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";

    // Try dbDelta first
    $result1 = dbDelta($sql);
    error_log('Main table creation result: ' . print_r($result1, true));
    
    // Category table creation
    $category_sql = "CREATE TABLE $category_table (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name        VARCHAR(191)    NOT NULL,
        slug        VARCHAR(191)    NOT NULL,
        description TEXT            DEFAULT NULL,
        parent_id   BIGINT UNSIGNED DEFAULT NULL,
        created     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";

    // Try dbDelta first
    $result2 = dbDelta($category_sql);
    error_log('Category table creation result: ' . print_r($result2, true));
    
    // Add rewrite rules
    $this->add_rewrite_rules();
    flush_rewrite_rules();
    
    // Verify tables were created
    $this->ensure_tables_exist();
}

/**
 * Ensure both tables exist, create them if they don't
 */
public function ensure_tables_exist() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Main pages table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}enhanced_csp_pages (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        slug VARCHAR(191) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'draft',
        meta_title VARCHAR(255) DEFAULT NULL,
        meta_desc TEXT DEFAULT NULL,
        og_title VARCHAR(255) DEFAULT NULL,
        og_desc TEXT DEFAULT NULL,
        og_image VARCHAR(255) DEFAULT NULL,
        canonical VARCHAR(255) DEFAULT NULL,
        robots VARCHAR(100) DEFAULT 'index, follow',
        in_sitemap TINYINT(1) DEFAULT 1,
        h1 VARCHAR(255) DEFAULT NULL,
        hero_image VARCHAR(255) DEFAULT NULL,
        intro_text MEDIUMTEXT DEFAULT NULL,
        sections LONGTEXT DEFAULT NULL,
        conclusion_heading VARCHAR(255) DEFAULT NULL,
        conclusion_content MEDIUMTEXT DEFAULT NULL,
        banner_heading VARCHAR(255) DEFAULT NULL,
        banner_description TEXT DEFAULT NULL,
        banner_service VARCHAR(100) DEFAULT NULL,
        faq_items LONGTEXT DEFAULT NULL,
        video_components LONGTEXT DEFAULT NULL,
        breadcrumbs LONGTEXT DEFAULT NULL,
        region VARCHAR(100) DEFAULT NULL,
        service VARCHAR(100) DEFAULT NULL,
        sub_service VARCHAR(100) DEFAULT NULL,
        content_type VARCHAR(100) DEFAULT NULL,
        category_id BIGINT(20) UNSIGNED DEFAULT NULL,
        in_header_menu TINYINT(1) DEFAULT 0,
        author_name VARCHAR(100) DEFAULT NULL,
        author_bio MEDIUMTEXT DEFAULT NULL,
        author_image VARCHAR(255) DEFAULT NULL,
        author_social_links LONGTEXT DEFAULT NULL,
        editor_name VARCHAR(100) DEFAULT NULL,
        created DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published DATETIME DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY slug (slug),
        KEY category_id (category_id),
        KEY status (status),
        KEY content_type (content_type)
    ) $charset_collate;";

    // Categories table
    $categories_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}enhanced_csp_pages_categories (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(191) NOT NULL,
        slug VARCHAR(191) NOT NULL,
        description TEXT DEFAULT NULL,
        parent_id BIGINT(20) UNSIGNED DEFAULT NULL,
        created DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY slug (slug),
        KEY parent_id (parent_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Execute the SQL with dbDelta
    dbDelta($sql);
    dbDelta($categories_sql);

    // Check if tables exist
    $main_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}enhanced_csp_pages'") === $wpdb->prefix . 'enhanced_csp_pages';
    $categories_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}enhanced_csp_pages_categories'") === $wpdb->prefix . 'enhanced_csp_pages_categories';

    // Update schema if needed
    if ($main_table_exists) {
        $this->update_database_schema();
    }

    return $main_table_exists && $categories_table_exists;
}

/**
 * Update database schema for author fields
 */
/**
 * Update database schema for basic author fields
 */
public function update_author_fields_schema() {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE;
    
    // Check if author fields exist
    $author_bio_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'author_bio'");
    $author_image_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'author_image'");
    $author_social_links_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'author_social_links'");
    
    // Add author_bio column if it doesn't exist
    if (empty($author_bio_exists)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN author_bio MEDIUMTEXT DEFAULT NULL AFTER author_name");
        error_log('Added missing author_bio column to ' . $table);
    }
    
    // Add author_image column if it doesn't exist
    if (empty($author_image_exists)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN author_image VARCHAR(255) DEFAULT NULL AFTER author_bio");
        error_log('Added missing author_image column to ' . $table);
    }
    
    // Add author_social_links column if it doesn't exist
    if (empty($author_social_links_exists)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN author_social_links LONGTEXT DEFAULT NULL AFTER author_image");
        error_log('Added missing author_social_links column to ' . $table);
    }
}

/**
 * Update the database schema if needed
 */
public function update_database_schema() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enhanced_csp_pages';
    
    // Get current schema version
    $current_version = get_option('enhanced_csp_schema_version', '0');
    
    // Only update if version is different
    if ($current_version !== self::VERSION) {
        // Update column types to ensure consistency
        $column_updates = [
            'id' => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
            'slug' => 'VARCHAR(191) NOT NULL',
            'status' => 'VARCHAR(20) NOT NULL DEFAULT "draft"',
            'meta_title' => 'VARCHAR(255) DEFAULT NULL',
            'meta_desc' => 'TEXT DEFAULT NULL',
            'og_title' => 'VARCHAR(255) DEFAULT NULL',
            'og_desc' => 'TEXT DEFAULT NULL',
            'og_image' => 'VARCHAR(255) DEFAULT NULL',
            'canonical' => 'VARCHAR(255) DEFAULT NULL',
            'robots' => 'VARCHAR(100) DEFAULT "index, follow"',
            'in_sitemap' => 'TINYINT(1) DEFAULT 1',
            'h1' => 'VARCHAR(255) DEFAULT NULL',
            'hero_image' => 'VARCHAR(255) DEFAULT NULL',
            'intro_text' => 'MEDIUMTEXT DEFAULT NULL',
            'sections' => 'LONGTEXT DEFAULT NULL',
            'conclusion_heading' => 'VARCHAR(255) DEFAULT NULL',
            'conclusion_content' => 'MEDIUMTEXT DEFAULT NULL',
            'banner_heading' => 'VARCHAR(255) DEFAULT NULL',
            'banner_description' => 'TEXT DEFAULT NULL',
            'banner_service' => 'VARCHAR(100) DEFAULT NULL',
            'faq_items' => 'LONGTEXT DEFAULT NULL',
            'video_components' => 'LONGTEXT DEFAULT NULL',
            'breadcrumbs' => 'LONGTEXT DEFAULT NULL',
            'region' => 'VARCHAR(100) DEFAULT NULL',
            'service' => 'VARCHAR(100) DEFAULT NULL',
            'sub_service' => 'VARCHAR(100) DEFAULT NULL',
            'content_type' => 'VARCHAR(100) DEFAULT NULL',
            'category_id' => 'BIGINT(20) UNSIGNED DEFAULT NULL',
            'in_header_menu' => 'TINYINT(1) DEFAULT 0',
            'author_name' => 'VARCHAR(100) DEFAULT NULL',
            'author_bio' => 'MEDIUMTEXT DEFAULT NULL',
            'author_image' => 'VARCHAR(255) DEFAULT NULL',
            'author_social_links' => 'LONGTEXT DEFAULT NULL',
            'editor_name' => 'VARCHAR(100) DEFAULT NULL',
            'created' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated' => 'DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'published' => 'DATETIME DEFAULT NULL'
        ];

        foreach ($column_updates as $column => $definition) {
            $wpdb->query("ALTER TABLE `{$table_name}` MODIFY COLUMN `{$column}` {$definition}");
        }

        // Update schema version
        update_option('enhanced_csp_schema_version', self::VERSION);
    }
}
    
/**
 * Add rewrite rules for custom pages
 */
public function add_rewrite_rules() {
    add_rewrite_rule(
        '^([^/]+)/?$',
        'index.php?custom_page=$matches[1]',
        'top'
    );
    
    // Add preview rule
    add_rewrite_rule(
        '^preview/([^/]+)/?$',
        'index.php?custom_page=$matches[1]&preview=1',
        'top'
    );
    
    // Register query vars
    add_filter('query_vars', function($vars) {
        $vars[] = 'custom_page';
        $vars[] = 'preview';
        return $vars;
    });
}

/**
 * Add admin menu
 */
public function add_admin_menu() {
    add_menu_page(
        'Custom CMS',
        'Custom CMS',
        'manage_options',
        'custom-cms',
        [$this, 'render_admin_page'],
        'dashicons-database',
        25
    );
    
    add_submenu_page(
        'custom-cms',
        'All Pages',
        'All Pages',
        'manage_options',
        'custom-cms',
        [$this, 'render_admin_page']
    );
    
    add_submenu_page(
        'custom-cms',
        'Add New Page',
        'Add New Page',
        'manage_options',
        'custom-cms-new',
        [$this, 'render_new_page']
    );
    
    add_submenu_page(
        'custom-cms',
        'Categories',
        'Categories',
        'manage_options',
        'custom-cms-categories',
        [$this, 'render_categories_page']
    );
    
    add_submenu_page(
        'custom-cms',
        'API Documentation',
        'API Documentation',
        'manage_options',
        'custom-cms-api',
        [$this, 'render_api_docs']
    );
}

/**
 * Enqueue admin assets
 */
public function enqueue_admin_assets($hook) {
    if (strpos($hook, 'custom-cms') === false) {
        return;
    }
    
    // Enqueue WordPress media uploader scripts
    wp_enqueue_media();
    
    // Add jQuery and Handlebars
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'handlebars',
        'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.7/handlebars.min.js',
        [],
        '4.7.7',
        true
    );
    
    // Add our custom JS
    wp_enqueue_script(
        'custom-cms-admin',
        plugin_dir_url(__FILE__) . 'admin/js/admin.js',
        ['jquery', 'handlebars', 'wp-api'],
        self::VERSION,
        true
    );
    
    // Add styles
    wp_enqueue_style(
        'custom-cms-admin',
        plugin_dir_url(__FILE__) . 'admin/css/admin.css',
        [],
        self::VERSION
    );
    
    // Add the REST API nonce
    wp_localize_script('custom-cms-admin', 'customCmsData', [
        'restUrl' => esc_url_raw(rest_url(self::NAMESPACE)),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}

/**
 * Render admin page
 */
public function render_admin_page() {
    include plugin_dir_path(__FILE__) . 'admin/pages-list.php';
}

/**
 * Render new page
 */
public function render_new_page() {
    include plugin_dir_path(__FILE__) . 'admin/page-edit.php';
}

/**
 * Render categories page
 */
public function render_categories_page() {
    include plugin_dir_path(__FILE__) . 'admin/categories.php';
}

/**
 * Render API documentation page
 */
public function render_api_docs() {
    include plugin_dir_path(__FILE__) . 'admin/api-docs.php';
}

/**
 * Register REST routes
 */
public function register_routes() {
    // GET collection
    register_rest_route(self::NAMESPACE, '/pages', [
        'methods'  => 'GET',
        'callback' => [$this, 'get_pages'],
        'permission_callback' => '__return_true',
    ]);

    // GET single
    register_rest_route(self::NAMESPACE, '/pages/(?P<key>[\d\w\-]+)', [
        'methods'  => 'GET',
        'callback' => [$this, 'get_single'],
        'permission_callback' => '__return_true',
        'args' => [
            'key' => [
                'description' => 'ID or slug',
                'type' => 'string',
            ],
        ],
    ]);

    // POST create / update
    register_rest_route(self::NAMESPACE, '/pages', [
        'methods'  => 'POST',
        'callback' => [$this, 'save_page'],
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => $this->get_endpoint_args_for_item_schema(),
    ]);
    
    // PUT/PATCH update status
    register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)/status', [
        'methods'  => ['PUT', 'PATCH'],
        'callback' => [$this, 'update_status'],
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'id' => [
                'description' => 'Page ID',
                'type' => 'integer',
                'required' => true,
            ],
            'status' => [
                'description' => 'New status (draft or published)',
                'type' => 'string',
                'required' => true,
                'enum' => ['draft', 'published'],
            ],
        ],
    ]);
    
    // DELETE page
    register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)', [
        'methods'  => 'DELETE',
        'callback' => [$this, 'delete_page'],
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'id' => [
                'description' => 'Page ID',
                'type' => 'integer',
                'required' => true,
            ],
        ],
    ]);
    
    // Category routes
    // GET all categories
    register_rest_route(self::NAMESPACE, '/categories', [
        'methods'  => 'GET',
        'callback' => [$this, 'get_categories'],
        'permission_callback' => '__return_true',
    ]);

    // GET single category
    register_rest_route(self::NAMESPACE, '/categories/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => [$this, 'get_single_category'],
        'permission_callback' => '__return_true',
        'args' => [
            'id' => [
                'description' => 'Category ID',
                'type' => 'integer',
                'required' => true,
            ],
        ],
    ]);

    // POST create/update category
    register_rest_route(self::NAMESPACE, '/categories', [
        'methods'  => 'POST',
        'callback' => [$this, 'save_category'],
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'id' => ['type' => 'integer', 'required' => false],
            'name' => ['type' => 'string', 'required' => true],
            'slug' => ['type' => 'string', 'required' => true],
            'description' => ['type' => 'string', 'required' => false],
            'parent_id' => ['type' => 'integer', 'required' => false],
        ],
    ]);

    // DELETE category
    register_rest_route(self::NAMESPACE, '/categories/(?P<id>\d+)', [
        'methods'  => 'DELETE',
        'callback' => [$this, 'delete_category'],
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'id' => [
                'description' => 'Category ID',
                'type' => 'integer',
                'required' => true,
            ],
        ],
    ]);
}

/**
 * Define the arguments schema for the POST endpoint
 */
public function get_endpoint_args_for_item_schema() {
    return [
        'id' => ['type' => 'integer', 'required' => false],
        'slug' => ['type' => 'string', 'required' => true],
        'status' => ['type' => 'string', 'required' => false, 'enum' => ['draft', 'published']],
        
        // SEO Fields
        'meta_title' => ['type' => 'string', 'required' => false],
        'meta_desc' => ['type' => 'string', 'required' => false],
        'og_title' => ['type' => 'string', 'required' => false],
        'og_desc' => ['type' => 'string', 'required' => false],
        'og_image' => ['type' => 'string', 'required' => false],
        'canonical' => ['type' => 'string', 'required' => false],
        'robots' => ['type' => 'string', 'required' => false],
        'in_sitemap' => ['type' => 'boolean', 'required' => false],
        
        // Content Fields
        'h1' => ['type' => 'string', 'required' => false],
        'hero_image' => ['type' => 'string', 'required' => false],
        'intro_text' => ['type' => 'string', 'required' => false],
        
        // Structured Content
        'sections' => ['type' => 'array', 'required' => false, 'sanitize_callback' => null],
        'conclusion_heading' => ['type' => 'string', 'required' => false],
        'conclusion_content' => ['type' => 'string', 'required' => false],
        'banner_heading' => ['type' => 'string', 'required' => false],
        'banner_description' => ['type' => 'string', 'required' => false],
        'banner_service' => ['type' => 'string', 'required' => false],
        'faq_items' => ['type' => 'array', 'required' => false, 'sanitize_callback' => null],
        'video_components' => ['type' => 'array', 'required' => false, 'sanitize_callback' => null],
        'breadcrumbs' => ['type' => 'array', 'required' => false, 'sanitize_callback' => null],
        
        // Structuring Fields
        'region' => ['type' => 'string', 'required' => false],
        'service' => ['type' => 'string', 'required' => false],
        'sub_service' => ['type' => 'string', 'required' => false],
        'content_type' => ['type' => 'string', 'required' => false],
        'category_id' => ['type' => 'integer', 'required' => false],
        'in_header_menu' => ['type' => 'boolean', 'required' => false],
        
        // Author Fields
        'author_name' => ['type' => 'string', 'required' => false],
        'author_bio' => ['type' => 'string', 'required' => false],
        'author_image' => ['type' => 'string', 'required' => false],
        'author_social_links' => ['type' => 'array', 'required' => false, 'sanitize_callback' => null],
        'editor_name' => ['type' => 'string', 'required' => false],
    ];
}

/* ==========  ROUTE CALLBACKS  ========== */

public function get_pages(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE;
    
    $page = isset($req['page']) ? intval($req['page']) : 1;
    $per_page = isset($req['per_page']) ? intval($req['per_page']) : 10;
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    
    // Get results with pagination
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table ORDER BY updated DESC LIMIT %d OFFSET %d", 
            $per_page, 
            $offset
        ), 
        ARRAY_A
    );
    
    $response = new WP_REST_Response(array_map([$this, 'transform_row'], $results), 200);
    
    // Add pagination headers
    $response->header('X-WP-Total', $total);
    $response->header('X-WP-TotalPages', ceil($total / $per_page));
    
    return $response;
}

public function get_single(WP_REST_Request $req) {
    global $wpdb;
    $key = sanitize_text_field($req['key']);
    $table = $wpdb->prefix . self::TABLE;

    if (is_numeric($key)) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $key), ARRAY_A);
    } else {
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE slug = %s", $key), ARRAY_A);
    }

    if (!$row) {
        return new WP_Error('not_found', 'Page not found', ['status' => 404]);
    }

    return new WP_REST_Response($this->transform_row($row), 200);
}

public function save_page(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE;
    
    $params = $req->get_params();
    
    // Extract basic data
    $data = [
        'slug' => sanitize_title($params['slug']),
        'status' => isset($params['status']) ? sanitize_text_field($params['status']) : 'draft',
        
        // SEO Fields
        'meta_title' => isset($params['meta_title']) ? sanitize_text_field($params['meta_title']) : null,
        'meta_desc' => isset($params['meta_desc']) ? sanitize_textarea_field($params['meta_desc']) : null,
        'og_title' => isset($params['og_title']) ? sanitize_text_field($params['og_title']) : null,
        'og_desc' => isset($params['og_desc']) ? sanitize_textarea_field($params['og_desc']) : null,
        'og_image' => isset($params['og_image']) ? esc_url_raw($params['og_image']) : null,
        'canonical' => isset($params['canonical']) ? esc_url_raw($params['canonical']) : null,
        'robots' => isset($params['robots']) ? sanitize_text_field($params['robots']) : null,
        'in_sitemap' => isset($params['in_sitemap']) ? (bool)$params['in_sitemap'] : true,
        
        // Content Fields
        'h1' => isset($params['h1']) ? sanitize_text_field($params['h1']) : null,
        'hero_image' => isset($params['hero_image']) ? esc_url_raw($params['hero_image']) : null,
        'intro_text' => isset($params['intro_text']) ? wp_kses_post($params['intro_text']) : null,
        
        // Structured Content
        'sections' => isset($params['sections']) ? wp_json_encode($params['sections'], JSON_UNESCAPED_UNICODE) : null,
        'conclusion_heading' => isset($params['conclusion_heading']) ? sanitize_text_field($params['conclusion_heading']) : null,
        'conclusion_content' => isset($params['conclusion_content']) ? wp_kses_post($params['conclusion_content']) : null,
        'banner_heading' => isset($params['banner_heading']) ? sanitize_text_field($params['banner_heading']) : null,
        'banner_description' => isset($params['banner_description']) ? wp_kses_post($params['banner_description']) : null,
        'banner_service' => isset($params['banner_service']) ? sanitize_text_field($params['banner_service']) : null,
        'faq_items' => isset($params['faq_items']) ? wp_json_encode($params['faq_items'], JSON_UNESCAPED_UNICODE) : null,
        'video_components' => isset($params['video_components']) ? wp_json_encode($params['video_components'], JSON_UNESCAPED_UNICODE) : null,
        'breadcrumbs' => isset($params['breadcrumbs']) ? wp_json_encode($params['breadcrumbs'], JSON_UNESCAPED_UNICODE) : null,
        
        // Structuring Fields
        'region' => isset($params['region']) ? sanitize_text_field($params['region']) : null,
        'service' => isset($params['service']) ? sanitize_text_field($params['service']) : null,
        'sub_service' => isset($params['sub_service']) ? sanitize_text_field($params['sub_service']) : null,
        'content_type' => isset($params['content_type']) ? sanitize_text_field($params['content_type']) : null,
        'category_id' => isset($params['category_id']) && !empty($params['category_id']) ? intval($params['category_id']) : null,
        'in_header_menu' => isset($params['in_header_menu']) ? (bool)$params['in_header_menu'] : false,
        
        // Authoring Fields - Updated with new fields
        'author_name' => isset($params['author_name']) ? sanitize_text_field($params['author_name']) : null,
        'author_bio' => isset($params['author_bio']) ? wp_kses_post($params['author_bio']) : null,
        'author_image' => isset($params['author_image']) ? esc_url_raw($params['author_image']) : null,
        'author_social_links' => isset($params['author_social_links']) ? wp_json_encode($params['author_social_links'], JSON_UNESCAPED_UNICODE) : null,
        'editor_name' => isset($params['editor_name']) ? sanitize_text_field($params['editor_name']) : null,
        
        'updated' => current_time('mysql'),
    ];
    
    // If status is being changed to published, update the published timestamp
    if ($data['status'] === 'published') {
        // If it's a new publish, set the published date
        if (empty($params['id']) || $this->get_current_status($params['id']) !== 'published') {
            $data['published'] = current_time('mysql');
        }
    }

    // Check for duplicate slug if inserting
    if (empty($params['id'])) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug = %s", $data['slug']));
        if ($exists) {
            // Add a unique identifier to the slug
            $data['slug'] = $data['slug'] . '-' . uniqid();
        }
    }

    // Insert or update
    $id = 0;
    if (empty($params['id'])) {
        $data['created'] = current_time('mysql');
        $wpdb->insert($table, $data);
        $id = $wpdb->insert_id;
    } else {
        $id = intval($params['id']);
        $wpdb->update($table, $data, ['id' => $id]);
    }

    // Get the updated record directly from the database
    $updated_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$updated_row) {
        return new WP_Error('save_failed', 'Failed to save page', ['status' => 500]);
    }
    
    return new WP_REST_Response($this->transform_row($updated_row), 200);
}

/**
 * Update the status of a page
 */
public function update_status(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE;
    $id = intval($req['id']);
    $status = sanitize_text_field($req['status']);
    
    $data = [
        'status' => $status,
        'updated' => current_time('mysql'),
    ];
    
    // If publishing, update published timestamp
    if ($status === 'published' && $this->get_current_status($id) !== 'published') {
        $data['published'] = current_time('mysql');
    }
    
    $wpdb->update($table, $data, ['id' => $id]);
    
    // Get the updated record
    $updated_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$updated_row) {
        return new WP_Error('update_failed', 'Failed to update page status', ['status' => 500]);
    }
    
    return new WP_REST_Response($this->transform_row($updated_row), 200);
}

/**
 * Delete a page
 */
public function delete_page(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE;
    $id = intval($req['id']);
    
    // Get the page before deleting
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$row) {
        return new WP_Error('not_found', 'Page not found', ['status' => 404]);
    }
    
    // Delete the page
    $result = $wpdb->delete($table, ['id' => $id], ['%d']);
    
    if (!$result) {
        return new WP_Error('delete_failed', 'Failed to delete page', ['status' => 500]);
    }
    
    // Return the deleted page data
    return new WP_REST_Response($this->transform_row($row), 200);
}

/**
 * Get all categories
 */
public function get_categories(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE . '_categories';
    
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC", ARRAY_A);
    
    // Transform results
      $categories = array_map(function($row) {
        $row['id'] = intval($row['id']);
        if (!empty($row['parent_id'])) {
            $row['parent_id'] = intval($row['parent_id']);
        }
        $row['created'] = mysql2date('c', $row['created']);
        $row['updated'] = mysql2date('c', $row['updated']);
        
        return $row;
    }, $results);
    
    return new WP_REST_Response($categories, 200);
}

/**
 * Get a single category
 */
public function get_single_category(WP_REST_Request $req) {
    global $wpdb;
    $id = intval($req['id']);
    $table = $wpdb->prefix . self::TABLE . '_categories';
    
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$row) {
        return new WP_Error('not_found', 'Category not found', ['status' => 404]);
    }
    
    // Transform row
    $row['id'] = intval($row['id']);
    if (!empty($row['parent_id'])) {
        $row['parent_id'] = intval($row['parent_id']);
    }
    $row['created'] = mysql2date('c', $row['created']);
    $row['updated'] = mysql2date('c', $row['updated']);
    
    return new WP_REST_Response($row, 200);
}

/**
 * Save a category (create or update)
 */
public function save_category(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE . '_categories';
    
    $params = $req->get_params();
    
    $data = [
        'name' => sanitize_text_field($params['name']),
        'slug' => sanitize_title($params['slug']),
        'description' => isset($params['description']) ? sanitize_textarea_field($params['description']) : null,
        'parent_id' => !empty($params['parent_id']) ? intval($params['parent_id']) : null,
        'updated' => current_time('mysql'),
    ];
    
    // Check for duplicate slug if inserting
    if (empty($params['id'])) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug = %s", $data['slug']));
        if ($exists) {
            // Add a unique identifier to the slug
            $data['slug'] = $data['slug'] . '-' . uniqid();
        }
    }
    
    // Insert or update
    if (empty($params['id'])) {
        $data['created'] = current_time('mysql');
        $wpdb->insert($table, $data);
        $id = $wpdb->insert_id;
    } else {
        $id = intval($params['id']);
        $wpdb->update($table, $data, ['id' => $id]);
    }
    
    // Get the saved category
    $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$category) {
        return new WP_Error('save_failed', 'Failed to save category', ['status' => 500]);
    }
    
    // Transform row
    $category['id'] = intval($category['id']);
    if (!empty($category['parent_id'])) {
        $category['parent_id'] = intval($category['parent_id']);
    }
    $category['created'] = mysql2date('c', $category['created']);
    $category['updated'] = mysql2date('c', $category['updated']);
    
    return new WP_REST_Response($category, 200);
}

/**
 * Delete a category
 */
public function delete_category(WP_REST_Request $req) {
    global $wpdb;
    $id = intval($req['id']);
    $table = $wpdb->prefix . self::TABLE . '_categories';
    
    // Get the category before deleting
    $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$category) {
        return new WP_Error('not_found', 'Category not found', ['status' => 404]);
    }
    
    // Delete the category
    $result = $wpdb->delete($table, ['id' => $id], ['%d']);
    
    if (!$result) {
        return new WP_Error('delete_failed', 'Failed to delete category', ['status' => 500]);
    }
    
    // Transform row for response
    $category['id'] = intval($category['id']);
    if (!empty($category['parent_id'])) {
        $category['parent_id'] = intval($category['parent_id']);
    }
    $category['created'] = mysql2date('c', $category['created']);
    $category['updated'] = mysql2date('c', $category['updated']);
    
    return new WP_REST_Response($category, 200);
}

/* ==========  HELPERS  ========== */

/**
 * Get the current status of a page
 */
private function get_current_status($id) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE;
    return $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id = %d", $id));
}

/**
 * Transform database row for API response
 */
private function transform_row($row) {
    // Cast types and decode JSON for API response
    $row['id'] = intval($row['id']);
    $row['in_sitemap'] = (bool)$row['in_sitemap'];
    $row['in_header_menu'] = (bool)$row['in_header_menu'];
    
    if (!empty($row['category_id'])) {
        $row['category_id'] = intval($row['category_id']);
    }
    
    // JSON fields
    $json_fields = [
        'sections', 
        'faq_items', 
        'video_components', 
        'breadcrumbs',
        'author_social_links'
    ];
    
    foreach ($json_fields as $field) {
        if (!empty($row[$field])) {
            $row[$field] = json_decode($row[$field], true);
        } else {
            $row[$field] = [];
        }
    }
    
    // Format dates
    $date_fields = ['created', 'updated', 'published'];
    foreach ($date_fields as $field) {
        if (!empty($row[$field])) {
            $row[$field] = mysql2date('c', $row[$field]);
        }
    }
    
    return $row;
}

/**
 * Fix Category REST API Endpoint
 * This function should be called in the main plugin class
 */
public function fix_category_rest_endpoint() {
    // Re-register the REST route with improved handling
    register_rest_route(self::NAMESPACE, '/categories', [
        'methods'  => 'POST',
        'callback' => [$this, 'save_category_enhanced'],
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'id' => ['type' => 'integer', 'required' => false],
            'name' => ['type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field'],
            'slug' => ['type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_title'],
            'description' => ['type' => 'string', 'required' => false, 'sanitize_callback' => 'sanitize_textarea_field'],
            'parent_id' => ['type' => 'integer', 'required' => false],
        ],
    ]);
}

/**
 * Enhanced version of save_category method with better error handling
 */
public function save_category_enhanced(WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . self::TABLE . '_categories';
    
    $params = $req->get_params();
    
    // Validate required fields
    if (empty($params['name'])) {
        return new WP_Error('name_required', 'Category name is required', ['status' => 400]);
    }
    
    if (empty($params['slug'])) {
        return new WP_Error('slug_required', 'Category slug is required', ['status' => 400]);
    }
    
    // Check for existing category with the same slug
    $existing_category = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE slug = %s AND id != %d", 
            sanitize_title($params['slug']), 
            !empty($params['id']) ? intval($params['id']) : 0
        )
    );
    
    if ($existing_category) {
        return new WP_Error(
            'duplicate_category',
            'A category with this slug already exists',
            ['status' => 400]
        );
    }
    
    // Prepare data for database
    $data = [
        'name' => sanitize_text_field($params['name']),
        'slug' => sanitize_title($params['slug']),
        'description' => isset($params['description']) ? sanitize_textarea_field($params['description']) : null,
        'parent_id' => !empty($params['parent_id']) ? intval($params['parent_id']) : null,
        'updated' => current_time('mysql'),
    ];
    
    // Debug info
    error_log('Saving category with data: ' . print_r($data, true));
    
    // Insert or update
    $result = false;
    if (empty($params['id'])) {
        $data['created'] = current_time('mysql');
        $result = $wpdb->insert($table, $data);
        $id = $wpdb->insert_id;
        
        if ($result === false) {
            return new WP_Error(
                'db_insert_error', 
                'Could not insert category: ' . $wpdb->last_error, 
                ['status' => 500]
            );
        }
    } else {
        $id = intval($params['id']);
        $result = $wpdb->update($table, $data, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error(
                'db_update_error', 
                'Could not update category: ' . $wpdb->last_error, 
                ['status' => 500]
            );
        }
    }
    
    // Get the saved category
    $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if (!$category) {
        return new WP_Error(
            'retrieve_error', 
            'Category was saved but could not be retrieved', 
            ['status' => 500]
        );
    }
    
    // Transform row
    $category['id'] = intval($category['id']);
    if (!empty($category['parent_id'])) {
        $category['parent_id'] = intval($category['parent_id']);
    }
    $category['created'] = mysql2date('c', $category['created']);
    $category['updated'] = mysql2date('c', $category['updated']);
    
    return new WP_REST_Response($category, 200);
}
}

new Enhanced_CSP_Plugin();