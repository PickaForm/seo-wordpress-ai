<?php
/*
Plugin Name: SEO Wordpress AI | By Team DnL
Plugin URI: https://seowordpress.ai
Description: This plugin uses OpenAI API to auto-fill the SEO fields of your Yoast plugin. Save a huge amount of time building SEO for you or your customers!
Version: 1.0
Author: Team DnL
Doc: https://seowordpress.ai/documentation
Author URI: https://seowordpress.ai
*/


/**
 * 
 * Global UI library (KissJS)
 * 
 */
function kissjs_scripts() {
    wp_register_script('kissjs', 'https://kissjs.net/resources/lib/kissjs/kissjs.min.js', array(), '1.0', true);
    wp_enqueue_script('kissjs');
}
add_action('admin_enqueue_scripts', 'kissjs_scripts');


/**
 * 
 * Global plugin JS & CSS
 * 
 */
function swa_styles() {
    // KissJS CSS
    wp_enqueue_style( 'kissjs-styles', 'https://kissjs.net/resources/lib/kissjs/kissjs.css' );
    wp_enqueue_style( 'kissjs-geometry', 'https://kissjs.net/resources/lib/kissjs/styles/geometry/default.css' );
    wp_enqueue_style( 'kissjs-colors', 'https://kissjs.net/resources/lib/kissjs/styles/colors/light.css' );
    wp_enqueue_style( 'kissjs-webfonts', 'https://kissjs.net/resources/lib/kissjs/webfonts/fontawesome-all.min.css' );

    // Plugin CSS
    wp_enqueue_style( 'swa-styles', plugins_url( './seo-wordpress-ai.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'swa_styles' );


/**
 * 
 * Global plugin script for buttons
 * 
 */
function swa_add_seo_fill_button_script() {
    wp_register_script('swa-script', plugins_url( './seo-wordpress-ai.js', __FILE__ ), array(), '1.0', true);
    wp_enqueue_script('swa-script');
}
add_action('admin_enqueue_scripts', 'swa_add_seo_fill_button_script');


/**
 * 
 * Utility function to display log in the Php console
 * 
 */
function swa_log($msg) {
    $debug = true;

    if ($debug != true) return;
    error_log($msg);
}


/**
 * 
 * Check if plugins Yoast SEO or Yoast SEO Premium are enabled
 * If not: disable the plugin and display an error message
 * 
 */
function my_plugin_activation_hook() {
   if (
        !is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')
        && !defined('WPSEO_PREMIUM_FILE')
        && !is_plugin_active('wordpress-seo/wp-seo.php')
        && !defined('WPSEO_FILE')
    ) {
       deactivate_plugins(plugin_basename(__FILE__));
       wp_die('Your plugin require Yoast SEO or Yoast SEO Premium. Please install and enable Yoast to be able to use our SEO Wordpress AI plugin.');
   }
}
register_activation_hook(__FILE__, 'my_plugin_activation_hook');


/**
 * 
 * Function to declare the admin page
 * 
 */
function swa_add_options_page() {
    add_options_page( 
        __('SEO Wordpress AI', 'swa'),
        __('SEO Wordpress AI', 'swa'),
        'manage_options',
        'swa',
        'swa_options_page'
    );
}
add_action( 'admin_menu', 'swa_add_options_page' );


/**
 * 
 * Function to output HTML for the settings page
 * 
 */
function swa_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_GET['settings-updated'])) {
        add_settings_error('swa_messages', 'swa_message', __('Congratulations! Your OpenAI key has been securely saved, you can start using the plugin!', 'swa'), 'updated');
    }

    settings_errors('swa_messages');

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('swa');
            do_settings_sections('swa');

            submit_button('💾 &nbsp;&nbsp;&nbsp; Encrypt and save Open API key', 'swa-submit-button', 'submit', false, array(
                'class' => 'swa-submit-button',
                'id' => 'swa-submit',
                'name' => 'swa-submit'
            ));            
            ?>
        </form>
    </div>
    <?php
}


/**
 * 
 * Init fields for the Settings page
 * 
 */
function swa_settings_init() {
    register_setting('swa', 'swa_api_key');
    register_setting('swa', 'swa_encryption_key');
   
    // Register a new admin section
    add_settings_section(
        'swa_section',
        __('API Settings', 'swa'),
        'swa_section_callback',
        'swa'
    );

    // Register a field to encrypt the OpenAI key
    add_settings_field(
        'swa_encryption_key',
        __('1. Enter a secret phrase', 'swa'),
        'swa_encryption_key_callback',
        'swa',
        'swa_section'
    );

    // Register a new field in the "swa_section" section, inside the "swa" page
    add_settings_field(
        'swa_api_key', 
        __('2. Enter OpenAI API key', 'swa'), 
        'swa_api_key_callback', 
        'swa', 
        'swa_section'
    );

}
add_action('admin_init', 'swa_settings_init');


/**
 * 
 * User instructions
 * 
 */
function swa_section_callback() {
    _e('<div class="swa-instructions">
        Please:
        <br>1. Choose a secret phrase
        <br>2. Enter your OpenAI key
        <br>
        <br>The key will be encrypted using your custom secret phrase before being saved to your Wordpress database.
        <br>
        <br>To create your OpenAI account and get your Open AI key, please check the following tutorial:
        <br><a href="https://seowordpress.ai/documentation" target="_new">How do I get my Open API key?</a>
        <br>
        <br>If you like our work, please, consider helping us building the next version of it:
        <br><a href="https://seowordpress.ai/#download" target="_new">Help us make this plugin great!</a>
        <br>
        <br>By supporting us ❤, you can help us build a plugin that will automatically check 100% of SEO constraints in 1 click 👍.
    </div>
    ', 'swa');
}


/**
 * 
 * Add "Settings" link to the plugin
 * 
 */
function swa_plugin_action_links($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=swa')) . '">' . __('Settings', 'swa') . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'swa_plugin_action_links', 10, 2);


/**
 * 
 * Add "Documentation" link to the plugin
 * 
 */
function add_plugin_documentation_link($plugin_meta, $plugin_file, $plugin_data, $status) {
    if ($plugin_file === plugin_basename(__FILE__)) {
        $documentation_link = 'https://seowordpress.ai/documentation';
        $plugin_meta[] = '<a href="' . esc_url($documentation_link) . '">Documentation</a>';
    }
    return $plugin_meta;
}
add_filter('plugin_row_meta', 'add_plugin_documentation_link', 10, 4);



/**
 * 
 * Callback after OpenAI key is saved
 * 
 */
function swa_api_key_callback() {
    $encrypted_key = get_option('swa_api_key');
    $encryption_key = get_option('swa_encryption_key');
    $decrypted_key = swa_decrypt_api_key_with_key($encrypted_key, $encryption_key);
    echo "<input type='password' id='swa_api_key' name='swa_api_key' value='" . esc_attr($decrypted_key) . "'>";
}


/**
 * 
 * Callback function to save encryption key + encrypted OpenAI key
 * 
 */
function swa_encryption_key_callback() {
    $encryption_key = get_option('swa_encryption_key');

    if (empty($encryption_key)) {
        $encryption_key = wp_generate_password(32, true, true);
        update_option('swa_encryption_key', $encryption_key);
    }
    else {
        update_option('swa_encryption_key', $encryption_key);
    }

    $encrypted_key = get_option('swa_api_key');
    $decrypted_key = swa_decrypt_api_key_with_key($encrypted_key, $encryption_key);
    $encrypted_api_key = swa_encrypt_api_key_with_key($decrypted_key, $encryption_key);
    update_option('swa_api_key', $encrypted_api_key);

    echo '<input type="text" id="swa_encryption_key" name="swa_encryption_key" value="' . esc_attr($encryption_key) . '" />';
}


/**
 * 
 * Encrypt the API key using the encryption key
 * 
 */
function swa_encrypt_api_key($api_key) {
    $encryption_key = get_option('swa_encryption_key');
    $encrypted_api_key = swa_encrypt_api_key_with_key($api_key, $encryption_key);
    return $encrypted_api_key;
}

function swa_encrypt_api_key_with_key($api_key, $encryption_key) {
    $cipher = 'AES-256-CBC';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($api_key, $cipher, $encryption_key, $options=0, $iv);
    return base64_encode($encrypted) . '::' . base64_encode($iv);
}
add_action('admin_init', 'swa_decrypt_api_key');


/**
 * 
 * Decrypt the API key
 * 
 */
function swa_decrypt_api_key() {
    $encrypted_api_key = get_option('swa_api_key');
    $encryption_key = get_option('swa_encryption_key');
    $decrypted_api_key = swa_decrypt_api_key_with_key($encrypted_api_key, $encryption_key);
    return $decrypted_api_key;
}

function swa_decrypt_api_key_with_key($encrypted_api_key, $encryption_key) {
    $cipher = 'AES-256-CBC';
    $parts = explode('::', $encrypted_api_key, 2);
    if (count($parts) !== 2) {
        return $encrypted_api_key;
    }
    list($encrypted_data, $iv) = explode('::', $encrypted_api_key, 2);
    $encrypted_data = base64_decode($encrypted_data);
    $iv = base64_decode($iv);
    $decrypted = openssl_decrypt($encrypted_data, $cipher, $encryption_key, $options=0, $iv);
    return $decrypted !== false ? $decrypted : $encrypted_api_key;
}


/**
 * 
 * Action to fill SEO fields
 * 
 */
function swa_fill_seo_fields() {
    swa_log("... Starting SEO Wordpress AI");

    // Check permissions
    swa_log("... Checking permissions:");
    if (!current_user_can('edit_post', $_POST['post_ID'])) {
        return;
    }
    swa_log("Permissions OK!");

    // Generate SEO description and focuskw
    $current_post_id = $_POST['post_ID'];
    $post_title = get_post_field('post_title', $current_post_id);
    $post_content = get_post_field('post_content', $current_post_id);
    $seo_data = swa_generate_seo_data($post_title, $post_content);
    swa_log("... OpenAI response OK: starting to fill SEO fields:");

    // Check authentication error
    if (isset($seo_data['error']) && $seo_data['error'] == 401) {
        wp_send_json_error('401');
        exit();
    }

    // Check quota / rate limit error
    if (isset($seo_data['error']) && $seo_data['error'] == 429) {
        wp_send_json_error('429');
        exit();
    }

    // Set Yoast SEO description
    if (isset($seo_data['description']) && $seo_data['description'] !== 'Error') {
        swa_log("... <Description> retrieved!");
        update_post_meta($_POST['post_ID'], '_yoast_wpseo_metadesc', sanitize_text_field($seo_data['description']));
    }
    else {
        wp_send_json_error('An error occurred while generating the SEO description.');
    }

    // Set Yoast SEO focuskw
    if (isset($seo_data['focuskw']) && $seo_data['focuskw'] !== 'Error') {
        swa_log("... <Focuskw> field retrieved!");
        update_post_meta($_POST['post_ID'], '_yoast_wpseo_focuskw', sanitize_text_field($seo_data['focuskw']));
    }
    else {
        wp_send_json_error('An error occurred while generating the SEO key phrase.');
    }
    
    // Redirect to the edit page
    swa_log("... Process complete! Redirecting to post edition.");
    wp_redirect(admin_url('post.php?post=' . $_POST['post_ID'] . '&action=edit'));

    swa_log("Done!");
    exit();
}
add_action('wp_ajax_swa_fill_seo_fields', 'swa_fill_seo_fields');


/**
 * 
 * Action to fill slug from new focus keyphrase
 * 
 */
function swa_update_post_slug() {
    swa_log("... Starting slug update");

    // Check permissions
    swa_log("... Checking permissions:");
    if (!current_user_can('edit_post', $_POST['post_ID'])) {
        wp_send_json_error('Unauthorized');
        return;
    }
    swa_log("Permissions OK!");

    $new_slug = sanitize_title_with_dashes($_POST['new_slug']);
    $new_focuskw = sanitize_text_field($_POST['new_focuskw']);
    $post_id = $_POST['post_ID'];

    // Update slug
    $updated_post = array(
        'ID' => $post_id,
        'post_name' => $new_slug
    );
    wp_update_post($updated_post, true);

    // Update focus keyphrase
    update_post_meta($_POST['post_ID'], '_yoast_wpseo_focuskw', $new_focuskw);

    // Redirect to the edit page
    swa_log("... Process complete! Redirecting to post edition.");
    wp_redirect(admin_url('post.php?post=' . $_POST['post_ID'] . '&action=edit'));

    swa_log("Done!");
    exit();
}
add_action('wp_ajax_swa_update_post_slug', 'swa_update_post_slug');


/**
 * 
 * Generates SEO data from OpenAI and inject result in Yoast SEO fields
 * 
 * @param {string} $post_title
 * @param {string} $post_content
 * @returns {object} Object with SEO data generated by OpenAI
 * 
 */
function swa_generate_seo_data($post_title, $post_content) {
    // Decrypt the API key
    $encrypted_openai_api_key = get_option('swa_api_key');
    $openai_api_key = swa_decrypt_api_key_with_key($encrypted_openai_api_key, get_option('swa_encryption_key'));

    // Limit the post content to avoid OpenAI limit of 4097 tokens
    // We'll remove this limitation in the next version of the plugin
    $post_content = substr($post_content, 0, 6000);

    // Prepare API request payload
    swa_log("... Requesting OpenAI API:");
    $payload = [
        'openaikey' => $openai_api_key,
        'title' => $post_title,
        'content' => $post_content
    ];

    // Set headers for the API request
    $headers = [
        'Content-Type' => 'application/json'
    ];

    // Define the gateway to use to transmit data to OpenAI
    $seo_gateway_url = 'https://app.seowordpress.ai/swa';
    $response = wp_remote_post($seo_gateway_url, [
        'headers' => $headers,
        'timeout' => 180, // 3mn timeout because OpenAI can take a long time to answer
        'body' => json_encode($payload),
    ]);

    swa_log("OpenAI response received!");
    
    // If there is an error, log it and return
    swa_log("... Analazing OpenAI response:");
    
    if(is_wp_error($response)) {
        swa_log("OpenAI API request failed: " . $response->get_error_message());
        return [
            "description" => "Error",
            "focuskw" => "Error",
            "synonyms" => "Error"
        ];
    }

    // Authentication error
    if (wp_remote_retrieve_response_code($response) == 401) {
        swa_log("OpenAI authentication error");
        return ["error" => 401];
    }

    // Rate limit error
    if (wp_remote_retrieve_response_code($response) == 429) {
        swa_log("OpenAI rate limit error");
        return ["error" => 429];
    }

    // Undefined errors
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        swa_log("OpenAI API response: " . wp_remote_retrieve_body($response));
        return ["error" => $response_code];
    }

    // Decode the response body
    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body);
    // swa_log(print_r($body, true));

    // OpenAI sends a 200 but with an arror (example: maximum context length)
    if (isset($body->error)) {
        swa_log("OpenAI error");
        return [
            "description" => "Error",
            "focuskw" => "Error",
            "synonyms" => "Error"
        ];
    }

    $description = "Error";
    $focuskw = "Error";
    $synonyms = "Error";

    // Check if "choices" and "text" are present in the response
    if (isset($body->description)) {
        $description = $body->description;
    }

    if (isset($body->focuskw)) {
        $focuskw = $body->focuskw;
    }

    if (isset($body->synonyms)) {
        $synonyms = $body->synonyms;
    }

    if ($description == "Error" || $focuskw == "Error" ||$synonyms == "Error") {
        swa_log("API response is not usable as this - Wrong format received, try again!");
    }
    else {
        error_log("Description: " . $description);
        error_log("Focus key phrase: " . $focuskw);
        error_log("Synonyms: " . $synonyms);
    }

    $seo_data = [
        "description" => $description,
        "focuskw" => $focuskw,
        "synonyms" => $synonyms
    ];
    
    return $seo_data;
}


/**
 * 
 * Generates a button to auto-fill the SEO fields using OpenAI
 * 
 */
function swa_add_seo_fill_button() {
    $screen = get_current_screen();
    if ( $screen->base != 'post' ) return; // Exit if we are not editing a post or a page
     
    global $post;
    echo '<input type="button"
            id="swa_seo_fill_button"    
            style="display:none; margin: 10px 16px 10px 16px; width: 170px;"
            class="button button-primary"
            value="⚡ SEO Wordpress AI"
            data-post-id="' . $post->ID . '"
        >
    ';
}
add_action('admin_footer', 'swa_add_seo_fill_button');


/**
 * 
 * Generates a button to regen the slug from the focus keyphrase
 * 
 */
function swa_slug_button() {
    $screen = get_current_screen();
    if ( $screen->base != 'post' ) return; // Exit if we are not editing a post or a page
     
    global $post;
    echo '<input type="button"
            id="swa_slug_button"    
            style="display:none; margin: 10px 16px 20px 16px; width: 248px;"
            class="button button-primary"
            value="⚡ Replace slug using focus keyphrase"
            data-post-id="' . $post->ID . '"
        >
    ';
}
add_action('admin_footer', 'swa_slug_button');