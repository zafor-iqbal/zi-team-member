<?php
/**
 * Plugin Name: ZI Team Member
 * Plugin URI: http://zaforiqbal.com/
 * Description: Registers a custom post type for Team Members, a taxonomy for Member Types, and provides a shortcode to display them.
 * Version: 1.0
 * Author: Zafor Iqbal
 * Author URI: http://zaforiqbal.com/
 */

//  [team_members number="5" image_position="top" display_bio="true"] to display 5 team members with images on top and bios displayed.
//  [team_members number="3" image_position="bottom" display_bio="false"] to display 3 team members with images at the bottom without displaying bios.

 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class Team_Members_Plugin {

    private $post_type_name;
    private $post_type_slug;

    public function __construct() {
        
        add_action('init', array($this, 'register_team_member_post_type'));
        add_action('init', array($this, 'register_member_type_taxonomy'));
        add_action('pre_get_posts', array($this, 'modify_team_member_query'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        // add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('updated_option', array($this, 'on_option_update'), 10, 3);
        add_action('add_meta_boxes', array($this, 'add_position_meta_box'));
        add_action('save_post', array($this, 'save_position_meta_box_data'));
        
        add_shortcode('team_members', array($this, 'display_team_members'));
        
        $this->include_template_files();
        $this->post_type_name = get_option('team_members_post_type_name', 'Team Members');
        $this->post_type_slug = get_option('team_members_post_type_slug', 'team-member');

    }

    public function register_team_member_post_type() {
        $labels = array(
            'name'                  => _x($this->post_type_name, 'Post type general name', 'textdomain'),
            'singular_name'         => _x('Team Member', 'Post type singular name', 'textdomain'),
            'menu_name'             => _x('Team Members', 'Admin Menu text', 'textdomain'),
            'name_admin_bar'        => _x('Team Member', 'Add New on Toolbar', 'textdomain'),
            'add_new'               => __('Add New', 'textdomain'),
            'add_new_item'          => __('Add New Team Member', 'textdomain'),
            'new_item'              => __('New Team Member', 'textdomain'),
            'edit_item'             => __('Edit Team Member', 'textdomain'),
            'view_item'             => __('View Team Member', 'textdomain'),
            'all_items'             => __('All Team Members', 'textdomain'),
            'search_items'          => __('Search Team Members', 'textdomain'),
            'parent_item_colon'     => __('Parent Team Members:', 'textdomain'),
            'not_found'             => __('No team members found.', 'textdomain'),
            'not_found_in_trash'    => __('No team members found in Trash.', 'textdomain'),
            'featured_image'        => _x('Team Member Picture', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
            'set_featured_image'    => _x('Set team member picture', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'remove_featured_image' => _x('Remove team member picture', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'use_featured_image'    => _x('Use as team member picture', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'archives'              => _x('Team Member archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
            'insert_into_item'      => _x('Insert into team member', 'Overrides the “Insert into post”/“Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this team member', 'Overrides the “Uploaded to this post”/“Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
            'filter_items_list'     => _x('Filter team members list', 'Screen reader text for the filter links heading on the post type listing screen. Added in 4.4', 'textdomain'),
            'items_list_navigation' => _x('Team Members list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Added in 4.4', 'textdomain'),
            'items_list'            => _x('Team Members list', 'Screen reader text for the items list heading on the post type listing screen. Added in 4.4', 'textdomain'),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => $this->post_type_slug),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail'),
            'show_in_rest'       => true, // This enables the Gutenberg editor for the custom post type
        );
    
        register_post_type('team_member', $args);

    }

    public function register_member_type_taxonomy() {
        $labels = array(
            'name'              => _x('Member Types', 'taxonomy general name', 'textdomain'),
            'singular_name'     => _x('Member Type', 'taxonomy singular name', 'textdomain'),
            'search_items'      => __('Search Member Types', 'textdomain'),
            'all_items'         => __('All Member Types', 'textdomain'),
            'parent_item'       => __('Parent Member Type', 'textdomain'),
            'parent_item_colon' => __('Parent Member Type:', 'textdomain'),
            'edit_item'         => __('Edit Member Type', 'textdomain'),
            'update_item'       => __('Update Member Type', 'textdomain'),
            'add_new_item'      => __('Add New Member Type', 'textdomain'),
            'new_item_name'     => __('New Member Type Name', 'textdomain'),
            'menu_name'         => __('Member Type', 'textdomain'),
        );
    
        $args = array(
            'hierarchical'      => true, // Set to true to make taxonomy hierarchical like categories
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'member-type'), // Use a custom slug for permalinks
            'show_in_rest'      => true, // To enable Gutenberg support in taxonomy
        );
    
        register_taxonomy('member_type', array('team_member'), $args);
    }


    public function add_position_meta_box() {
        add_meta_box(
            'team_member_position',          // ID of the meta box
            __('Position', 'textdomain'),    // Title of the meta box
            array($this, 'position_meta_box_callback'),  // Callback function
            'team_member',                   // The screen or post type where the meta box should be displayed
            'normal',                        // Context where the box will show ('normal', 'side', 'advanced')
            'high'                           // Priority within the context where the boxes should show
        );
    }
    
    public function position_meta_box_callback($post) {
        // Add a nonce field for security
        wp_nonce_field('save_position_data', 'position_meta_box_nonce');
    
        // Retrieve the current value of the 'Position' field, if any
        $position = get_post_meta($post->ID, '_team_member_position', true);
    
        // Meta box HTML
        echo '<label for="team_member_position">' . __('Position', 'textdomain') . '</label>';
        echo '<input type="text" id="team_member_position" name="team_member_position" value="' . esc_attr($position) . '" size="25" />';
    }
    
    public function save_position_meta_box_data($post_id) {
        // Check if our nonce is set and verify it.
        if (!isset($_POST['position_meta_box_nonce']) || !wp_verify_nonce($_POST['position_meta_box_nonce'], 'save_position_data')) {
            return;
        }
    
        // Check the user's permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    
        // Check if the 'Position' field is set and sanitize it.
        if (isset($_POST['team_member_position'])) {
            $sanitized_position = sanitize_text_field($_POST['team_member_position']);
            update_post_meta($post_id, '_team_member_position', $sanitized_position);
        }
    }
    
    


    public function add_admin_menu() {
        add_options_page(
            'Team Members Settings', 
            'Team Members', 
            'manage_options', 
            'team_members_settings', 
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('team_members_settings', 'team_members_post_type_name');
        register_setting('team_members_settings', 'team_members_post_type_slug');
    }

    public function enqueue_styles() {
        // Enqueue Bootstrap from CDN for front-end
        wp_enqueue_style('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
        // Enqueue your custom stylesheet for front-end
        wp_enqueue_style('team-members-style', plugins_url('css/team-members.css', __FILE__));
    }

    // public function enqueue_admin_styles() {
    //     // Enqueue Bootstrap from CDN for admin
    //     wp_enqueue_style('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    //     // Enqueue your custom stylesheet for admin
    //     wp_enqueue_style('team-members-admin-style', plugins_url('css/team-members-admin.css', __FILE__));
    // }

    public function on_option_update($option_name, $old_value, $value) {
        if ($option_name === 'team_members_post_type_slug') {
            flush_rewrite_rules();
        }
    }



    public function settings_page() {
        ?>
<div class="wrap">
    <h2>Team Members Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('team_members_settings'); ?>
        <?php do_settings_sections('team_members_settings'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Post Type Name</th>
                <td><input type="text" name="team_members_post_type_name"
                        value="<?php echo esc_attr(get_option('team_members_post_type_name', 'Team Members')); ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">URL Slug</th>
                <td><input type="text" name="team_members_post_type_slug"
                        value="<?php echo esc_attr(get_option('team_members_post_type_slug', 'team-member')); ?>" />
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
<?php
    }

     // Flush rewrite rules on settings save
     public function flush_rewrite_on_save() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            flush_rewrite_rules();
        }
    }

    public function display_team_members($atts) {
        $attributes = shortcode_atts(array(
            'number' => -1,
            'image_position' => 'top',
            'display_bio' => 'true',
            'show_button' => 'true',
        ), $atts);

        $args = array(
            'post_type' => 'team_member',
            'posts_per_page' => intval($attributes['number']),
        );

        $query = new WP_Query($args);
        ob_start();

        if ($query->have_posts()) {
            echo '<div class="team-members">';
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $position = get_post_meta($post_id, '_team_member_position', true);
                $post_link = get_permalink($post_id);

                echo '<div class="team-member">';

                if ($attributes['image_position'] == 'top') {
                    echo '<a href="' . esc_url($post_link) . '">';
                    $this->display_image();
                    echo '</a>';
                }

                echo '<h3><a href="' . esc_url($post_link) . '">' . get_the_title() . '</a></h3>';

                if ($position) {
                    echo '<p class="member-position">' . esc_html($position) . '</p>';
                }

                if ($attributes['display_bio'] === 'true') {
                    echo '<div class="member-bio">' . get_the_content() . '</div>';
                }

                if ($attributes['image_position'] == 'bottom') {
                    echo '<a href="' . esc_url($post_link) . '">';
                    $this->display_image();
                    echo '</a>';
                }

                echo '</div>';
            }
            echo '</div>';

            if ($attributes['show_button'] === 'true') {
                $archive_url = get_post_type_archive_link('team_member');
                echo '<div class="team-members-button">';
                echo '<a href="' . esc_url($archive_url) . '" class="btn btn-primary" role="button">See All</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No team members found.</p>';
        }

        wp_reset_postdata();
        $output = ob_get_clean();
        return $output;
    }

    public function modify_team_member_query($query) {
        if (!is_admin() && $query->is_main_query() && is_post_type_archive('team_member')) {
            // Define the number of team members per page
            $query->set('posts_per_page', 10);

            // Check if the 'paged' parameter is set
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $query->set('paged', $paged);
        }
    }

    private function display_image() {
        // Helper function to display the image
        if (has_post_thumbnail()) {
            the_post_thumbnail('team-member-thumb', array('class' => 'team-member-image'));
        }
    }

    public function include_template_files() {
        add_filter('template_include', array($this, 'template_loader'));
        add_filter('archive_template', array($this, 'load_archive_template'));
    }
    
    public function template_loader($template) {
        if (is_singular('team_member') && !$this->locate_template('single-team_member.php', true)) {
            // If no template is found in the theme directory, load the one from the plugin
            $template = plugin_dir_path(__FILE__) . 'templates/single-team_member.php';
        }
        return $template;
    }

    public function load_archive_template($template) {
        if (is_post_type_archive('team_member')) {
            $plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-team_member.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }
    
    private function locate_template($template_name, $load = false) {
        if ($theme_file = locate_template(array($template_name))) {
            if ($load) {
                load_template($theme_file);
            }
            return $theme_file;
        }
        return false;
    }

    
}

// Initialize the plugin
function run_team_members_plugin() {
    $team_members_plugin = new Team_Members_Plugin();
    // Flush rewrite rules on settings save
    add_action('admin_init', array($team_members_plugin, 'flush_rewrite_on_save'));
}
run_team_members_plugin();

// Pagination function for archive page
function team_member_pagination($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('team_member')) {
        $query->set('posts_per_page', 5); // Set the number of team members per page
    }
}
add_action('pre_get_posts', 'team_member_pagination');


// Flush rewrite rules on plugin activation
function team_members_plugin_activation() {
    // flush_rewrite_rules();
    // force rewrite rules to be recreated at the right time
	delete_option( 'rewrite_rules' );
}
register_activation_hook(__FILE__, 'team_members_plugin_activation');

// Flush rewrite rules on plugin deactivation
function team_members_plugin_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'team_members_plugin_deactivation');


// Flush rewrite rules on settings save
if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
    flush_rewrite_rules();
}