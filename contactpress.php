<?php
/* /
  Plugin Name: ContactPress
  Plugin URI: http://fantasticplugins.com/shop/contactpress
  Description: ContactPress is the Ajax Based Responsive Form With User Management
  Version: 1.1
  Author: Fantastic Plugins
  Author URI: http://fantasticplugins.com
  License: GPLv2
  / */

require_once('inc/admin.php');
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ContactPress extends WP_List_Table {

    function __construct() {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'contact',
            'plural' => 'contacts',
        ));

        global $contact_form_db_version;
        $contact_form_db_version = "1.0";
    }

    function column_default($item, $column_name) {
        return $item[$column_name];
    }

    function column_name($item) {
        $actions = array(
            'view' => sprintf('<a href="?page=view_contact_form&id=%s">%s</a>', $item['id'], __('View')),
            'edit' => sprintf('<a href="?page=contact_forms-1&id=%s">%s</a>', $item['id'], __('Edit')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete')),
            'send_message' => sprintf('<a href="?page=compose_mail&id=%s">%s</a>', $item['id'], __('Send Message')),
            'reply' => sprintf('<a href="?page=reply_mail&id=%s">%s</a>', $item['id'], __('Reply')),
        );

        return sprintf('%s %s', $item['name'], $this->row_actions($actions)
        );
    }

    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="id[]" value="%s" />', $item['id']
        );
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'name' => __('Name'),
            'email' => __('E-Mail'),
            'url' => __('URL'),
            'subject' => __('Subject'),
            'message' => __('Message'),
            'time' => __('Time'),
            'ip' => __('IP'),
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array('name', true),
            'email' => array('email', true),
            'url' => array('url', true),
            'subject' => array('subject', false),
            'message' => array('message', true),
            'time' => array('time', true),
            'ip' => array('ip', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete',
            'send_all' => 'Send All'
        );
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contactpress'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids))
                $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
        if ('send_all' === $this->current_action()) {
            $id_send = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($id_send))
                $id_send = implode(',', $id_send);
            if (!empty($id_send)) {
                $result_mail = $wpdb->get_results("SELECT email FROM $table_name WHERE id IN($id_send)");
                foreach ($result_mail as $results) {
                    $admin_email1 = get_option('admin_email');
                    $bulk_subject = get_option('bulk_subject');
                    $bulk_messages = get_option('bulk_messages');
                    $to_mail = $results->email;
                    $subject = "$bulk_subject";
                    $message1 = "$bulk_messages";
                    $blogname = get_option('blogname');
                    $header = 'MIME-Version: 1.0' . "\r\n";
                    $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $header .= "From:" . $blogname . "<$admin_email1>";
                    $send = mail($to_mail, $subject, $message1, $header);
                    echo "<br/>";
                }
            }
        }
    }

    function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contactpress'; // do not forget about tables prefix

        $per_page = 10; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

    function contact_form_admin_menu() {

        add_menu_page('ContactPress', 'ContactPress', IC_MYPLUGIN_PERMISSIONS, "fpcontact", "ic_myplugin_settings");
        add_submenu_page('fpcontact', 'ContactPress', 'ContactPress', 'manage_options', 'contacts', 'contactform_aplus_admin_pages');
        add_submenu_page('fpcontact', 'User Management', 'User Management', 'manage_options', 'contact_form_a_plus', 'contact_form_management');
        add_submenu_page('fpcontact', 'Add New Contact', 'Add New Contact', 'manage_options', 'contact_forms-1', 'contact_form_managements');
        add_submenu_page('fact', 'Fantastic Contact Form', 'Fantastic Contact Form', 'manage_options', 'compose_mail', 'contact_form_compose');
        add_submenu_page('fact', 'Fantastic Contact Form', 'Fantastic Contact Form', 'manage_options', 'reply_mail', 'contact_form_reply');
        add_submenu_page('fact', 'Fantastic Contact Form', 'Fantastic Contact Form', 'manage_options', 'view_contact_form', 'view_contact_form');
    }

    function contact_admin() {
        add_submenu_page('options-general.php', 'Fantastic Contact Form', 'Fantastic Contact Form', 'manage_options', 'contact_form', 'fantasticcontact_admin_pages');
    }

    function update_contact_admin() {
        register_setting('contact_form_a_plus', 'label_name');
        register_setting('contact_form_a_plus', 'label_email');
        register_setting('contact_form_a_plus', 'label_url');
        register_setting('contact_form_a_plus', 'label_subject');
        register_setting('contact_form_a_plus_thanks', 'thanks_contact');
        register_setting('contact_form_a_plus', 'label_comment');
        register_setting('contact_form_a_plus', 'enable_responsiveness');
        register_setting('contact_form_a_plus_error', 'error_name');
        register_setting('contact_form_a_plus_error', 'error_empty_email');
        register_setting('contact_form_a_plus_error', 'error_email');
        register_setting('contact_form_a_plus_error', 'error_subject');
        register_setting('contact_form_a_plus_error', 'error_msg');
        register_setting('contact_form_a_plus_error', 'error_empty_captcha');
        register_setting('contact_form_a_plus_error', 'error_responsiveness');
        register_setting('contact_form_a_plus_bulk', 'bulk_subject');
        register_setting('contact_form_a_plus_bulk', 'bulk_messages');
        register_setting('contactpress_credit_link', 'credit_link_contactpress');
    }

    function reset_contact_admin() {
        delete_option('label_name');
        delete_option('label_email');
        delete_option('label_url');
        delete_option('label_subject');
        delete_option('label_comment');
        delete_option('enable_responsiveness');
        add_option('label_comment', '1');
        add_option('enable_responsiveness', '1');
        add_option('label_name', 'Full Name');
        add_option('label_email', 'Email here');
        add_option('label_url', 'Optional');
        add_option('label_subject', 'Subject Here');
    }

    function reset_contact_bulk_setting() {
        delete_option('bulk_subject');
        delete_option('bulk_messages');
        add_option('bulk_subject', 'Thanks for contacting us');
        add_option('bulk_messages', 'Thanks for contacting us we will get back to you soon');
    }

    function reset_contact_thankyou_setting() {
        delete_option('thanks_contact');
        add_option('thanks_contact', 'Thanks for contacting us we will get back you soon');
    }

    function credit_link_activation() {
        delete_option('credit_link_contactpress');
        $input = array("WordPress Plugins", "WordPress Plugin", "WordPress Plugins", "WordPress Plugin", "Premium WordPress Plugins", "Premium WordPress Plugin", "Premium WordPress Plugins", "Premium WordPress Plugin", "Fantastic Plugins", "Fantastic Plugin", "WordPress Premium Plugins", "WordPress Premium Plugin", "WP Plugins", "WP Plugin", "Premium WP Plugins", "Premium WP Plugin", "WP Premium Plugins", "WP Premium Plugin", "Plugins", "Plugin");
        $rand_keys = rand(0, 19);
        $input_text = array("ContactPress Sponsor", "Plugin Sponsor", "Plugin Supporter", "Plugin Engineered By", "ContactPress Supported By", "ContactPress Engineered By", "Supporter of ContactPress", "Plugin Support By", "Plugin Sponsor", "Plugin Sponsor Credit To");
        $random_text = rand(0, 9);
        add_option('credits_text', $input_text[$random_text]);
        add_option('credits_name', $input[$rand_keys]);
        add_option('credits_defaults', 'http://fantasticplugins.com');
        $input_nofollow = array("nofollow", "dofollow");
        $random = rand(0, 100);
        $nofollow_key = 1;
        if ($random <= 90) {
            $nofollow_key = 1;
        } else {
            $nofollow_key = 0;
        }
        add_option('credits_nofollow', $input_nofollow[$nofollow_key]);
        add_option('credit_link_contactpress', '1');
    }

    function reset_contact_error_setting() {
        delete_option('error_name');
        delete_option('error_empty_email');
        delete_option('error_email');
        delete_option('error_subject');
        delete_option('error_msg');
        delete_option('error_empty_captcha');
        delete_option('error_captcha');
        add_option('error_name', 'Please Enter your Name');
        add_option('error_empty_email', 'Email Address is Required');
        add_option('error_email', 'Please Enter Valid Email Address');
        add_option('error_subject', 'Please Enter Subject');
        add_option('error_msg', 'Message Field is Required');
        add_option('error_empty_captcha', 'Please Enter captcha');
        add_option('error_captcha', 'Captcha Not Match Try Again');
    }

    function db_install() {
        global $wpdb;
        global $contact_form_db_version;
        global $table_name;
        $table_name = $wpdb->prefix . "contactpress";

        $sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  name tinytext NOT NULL,
  email text NOT NULL,
  subject text NOT NULL,
  url VARCHAR(55) DEFAULT '' NOT NULL,
  ip VARCHAR(40),
  message text NOT NULL,
  UNIQUE KEY id (id)
    );";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);

        add_option("contact_form_db_version", $contact_form_db_version);
    }

    function db_install_data() {
        require_once( ABSPATH . 'wp-config.php' );
        require_once( ABSPATH . 'wp-load.php' );
        global $wpdb;
        global $table_name;
        global $welcome_name;
        $welcome_name = $_POST['fname'];
        $_POST['fname'];
        global $welcome_text;
        $welcome_text = $_POST['msg'];
        global $welcome_url;
        $welcome_url = $_POST['url'];
        global $welcome_subject;
        $welcome_subject = $_POST['subject'];
        global $welcome_mail;
        $welcome_mail = $_POST['email'];
        $admin_mail = get_option('admin_email');
        global $ip;
        $ip = getenv('REMOTE_ADDR');
        $table_name = $wpdb->prefix . "contactpress";
        $header = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $header .= "From:" . $welcome_name . "<$welcome_mail>";
        $rows_affected = $wpdb->insert($table_name, array('time' => current_time('mysql'), 'name' => $welcome_name, 'email' => $welcome_mail, 'url' => $welcome_url, 'ip' => $ip, 'message' => $welcome_text, 'subject' => $welcome_subject));
        if ($rows_affected) {
            $send_contact = mail($admin_mail, $welcome_subject, $welcome_text, $header);
            if ($send_contact) {
                echo get_option('thanks_contact');
            } else {
                echo "There was an Error Occur Please Try again Later";
                $wpdb->print_error();
            }
        } else {
            $wpdb->print_error();
        }
        die();
    }

    function contactformui() {
        ?>
        <style type="text/css">
            ul {
                list-style-type:none;
            }
            table#table_form
            {
                border: none !important;
            }
            table#table_form tr
            {
                border: none !important;
            }
            table#table_form td
            {
                border: none !important;
            }

            label.error{
                color:red;
                display:block;

            }
            label.error input:focus {
                background-color:#0099FF;
            }
            #contact-form {
                width:500px;
            }
            .button-primary {
                margin-left:-231px;
                border-radius:0.05em;
            }
            .button-secondary {
                border-radius:0.05em;
            }
        </style>

        <style type="text/css">
            #message {
                background: none repeat scroll 0 0 pink;
                border: 0px solid green;
            }
        </style>
        <?php if (get_option('enable_responsiveness') == '1') { ?>
            <style type="text/css">
                @media screen and (min-width: 0px) and (max-width: 480px)  {
                    table#table_form
                    {
                        max-width:480px;
                        min-width: 0px;
                    }
                    table#table_form td#fnames {
                        width:358px;
                    }
                    table#table_form td #submit {
                        margin-left:-308px;
                    }
                    input[type="text"]#fname,#email,#subject,#url,#captcha {
                        width:103px;
                    }
                    textarea#msg {
                        width:100px;
                        height:40px;
                    }
                }
                @media screen and (min-width: 480px) and (max-width: 635px)  {
                    table#table_form
                    {
                        max-width:635px;
                        min-width:480px;
                    }
                    table#table_form td#fnames {

                    }
                    table#table_form td #submit {
                        margin-left:-231px;
                    }
                    input[type="text"]#fname,#email,#subject,#url,#captcha {
                        width:155px;
                    }
                    textarea#msg {
                        width:150px;
                        height:80px;
                    }
                }
            </style>
        <?php } ?>
        <form action="" method="post" name="newform" id="contact-form" >
            <div id="message"></div>
            <table id="table_form">
                <tr>
                    <td>
                        <label>Name*:</label></td>
                    <td id="fnames">
                        <input  type="text" name="fname" id="fname" placeholder="<?php echo get_option('label_name'); ?>" value= "" /></td></tr>
                <tr>
                    <td>
                        <label>Email*:</label></td>
                    <td>
                        <input type="text" name="email" id="email" placeholder="<?php echo get_option('label_email'); ?>" value="" /></td>
                </tr>
                <tr>
                    <td>
                        <label>Subject*:</label></td>
                    <td>
                        <input type="text" name="subject" id="subject" placeholder="<?php echo get_option('label_subject'); ?>" value="" /></td>
                </tr>
                <tr>
                    <td>
                        <label>Website:</label></td>
                    <td>
                        <input type="text" name="url" id="url" value="" placeholder="<?php echo get_option('label_url'); ?>" /></td>
                </tr>
                <tr>
                    <td>
                        <label>Your Message*:</label></td>
                    <td>
                        <textarea id="msg" rows="15" cols="45" name="message"> </textarea></td>
                </tr>

                <tr>
                    <td>
                        <label> 7 + 4 = </label>
                    </td>
                    <td>
                        <input type="text" id="captcha" name="captcha"  value=""/>
                    </td>
                </tr>

                <tr>
                    <td>
                    </td>
                    <td>
                        <input type="reset" value="clear" name="reset" class="button-secondary"/>
                    </td>
                    <td id="submits">
                        <input type="submit" value="submit" id="submit" name="submit" class="button-primary"/><img src="<?php echo admin_url(); ?>images/wpspin_light.gif" alt="" class="ajaxsave" style="display: none;" />
                    </td>
                </tr>
            </table>

        </form>
        <style type="text/css">
            .focus {
                border: 2px solid #AA88FF;
                background-color: #FFEEAA;
            }
            .error {
                border: 2px solid #ff0000;
                background-color: #FFEEAA;
            }

        </style>

        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('input[type="text"]').focus(function() {
                    jQuery(this).addClass("focus");
                });

                jQuery('input[type="text"]').blur(function() {
                    jQuery(this).removeClass("focus");
                });

                jQuery('textarea').focus(function() {
                    jQuery(this).addClass('focus');
                });

                jQuery('textarea').blur(function() {
                    jQuery(this).removeClass('focus');
                });

                jQuery("#contact-form").submit(function() {
                    var fname = jQuery("#fname").val();
                    if (jQuery("#fname").val() === "") {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_name'); ?>");
                        //jQuery("#fname").focus();
                        jQuery('#fname').focus();
                        jQuery('#fname').addClass("error");
                        return false;
                    }
                    if (jQuery("#fname").val() !== "") {
                        jQuery('#fname').removeClass("error");

                    }
                    if (jQuery("#email").val() === "") {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_empty_email'); ?>");
                        jQuery('#email').focus();
                        jQuery('#email').addClass("error");
                        return false;
                    } else {
                        jQuery("#email").removeClass("error");
                    }
                    var email = jQuery('#email').val();
                    if (email.indexOf("@") === -1 || email.indexOf(".") === -1) {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_email'); ?>");
                        jQuery('#email').focus();
                        jQuery('#email').addClass("error");
                        return false;
                    } else {
                        jQuery("#email").removeClass("error");
                    }
                    var subject = jQuery("#subject").val();
                    if (jQuery("#subject").val() === "") {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_subject'); ?>");
                        jQuery('#subject').focus();
                        jQuery('#subject').addClass("error");
                        return false;
                    }
                    if (jQuery("#subject").val() !== "") {
                        jQuery('#subject').removeClass("error");
                    }
                    var msg = jQuery('#msg').val();
                    if (jQuery("#msg").val() === " ") {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_msg'); ?>");
                        jQuery('#msg').focus();
                        jQuery('#msg').addClass("error");
                        return false;
                    }
                    if (jQuery("msg").val() !== " ") {
                        jQuery('#msg').removeClass("error");
                    }
                    if (jQuery("#captcha").val() === "") {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_empty_captcha'); ?>");
                        jQuery('#captcha').focus();
                        jQuery('#captcha').addClass("error");
                        return false;
                    }
                    if (jQuery("#captcha").val() !== "11") {
                        jQuery("#contact-form #message").text("<?php echo get_option('error_captcha'); ?>");
                        jQuery('#captcha').focus();
                        jQuery('#captcha').addClass("error");
                        return false;
                    }

                    else {
                        jQuery('#captcha').removeClass("error");
                        var url = jQuery('#url').val();
                        var data = ({
                            action: 'add_contactform',
                            fname: fname, email: email, msg: msg, url: url, subject: subject

                        });

                        jQuery("#submit").hide();
                        jQuery(".ajaxsave").show();
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                                function(response) {
                                    jQuery(".ajaxsave").hide();
                                    jQuery("#submit").show();
                                    jQuery("#contact-form #message").html(response);
                                });
                        return false;
                    }
                });
            });
        </script>

        <?php
        if (get_option('label_comment') == '1') {
            add_filter('comments_template', 'no_comments_on_page');

            function no_comments_on_page($file) {
                if (is_page()) {
                    $file = dirname(__FILE__) . '/empty-file.php';
                }
                return $file;
            }

        }
    }

}

function contact_form_management() {
    global $wpdb;

    $table = new ContactPress();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d'), count($_REQUEST['id'])) . '</p></div>';
    }
    if ('send_all' === $table->current_action()) {

        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Message sent Successfully: %d'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>ContactPress User Management</h2>
        <?php echo $message; ?>

        <form id="persons-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $table->display() ?>
        </form>

    </div>
    <?php
}

function contact_form_managements() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contactpress'; // do not forget about tables prefix

    $message = '';
    $notice = '';

// this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => '',
        'email' => '',
        'url' => null,
        'subject' => '',
        'message' => '',
    );

// here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        $item_valid = contact_form_validation($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved');
                } else {
                    $notice = __('There was an error while saving item');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated');
                } else {
                    $notice = __('There was an error while updating item');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    } else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found');
            }
        }
    }

// here we adding our custom meta box
    add_meta_box('persons_form_meta_box', 'Contact Person Data', 'contact_form_meta_box', 'contact', 'normal', 'default');
    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>ContactPress User Management<a class="add-new-h2"
                                           href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contact_form_a_plus'); ?>"><?php _e('back to list') ?></a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('contact', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('Save') ?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function view_contact_form() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contactpress'; // do not forget about tables prefix

    $message = '';
    $notice = '';

// this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => '',
        'email' => '',
        'url' => null,
        'subject' => '',
        'message' => '',
    );

// here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        $item_valid = contact_form_validation($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved');
                } else {
                    $notice = __('There was an error while saving item');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated');
                } else {
                    $notice = __('There was an error while updating item');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    } else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found');
            }
        }
    }

// here we adding our custom meta box
    add_meta_box('view_form_meta_box', 'View Person Data', 'viewer_form_meta_box', 'contacting', 'normal', 'default');
    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>User Detail<a class="add-new-h2"
                          href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contact_form_a_plus'); ?>"><?php _e('back to list') ?></a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('contacting', 'normal', $item); ?>
                    <!-- <input type="submit" value="<?php _e('Save') ?>" id="submit" class="button-primary" name="submit"> -->
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function viewer_form_meta_box($item) {
    ?>

    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="from"><?php _e('From') ?></label>
                </th>
                <td>
                    <?php echo $item['name'] ?>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('E-Mail') ?></label>
                </th>
                <td>
                    <?php echo $item['email'] ?>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="url"><?php _e('URL') ?></label>
                </th>
                <td>
                    <?php echo $item['url'] ?>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="subject"><?php _e('Subject') ?></label>
                </th>
                <td>
                    <?php echo $item['subject'] ?>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="message"><?php _e('Message') ?></label>
                </th>
                <td>
                    <?php echo $item['message'] ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

function contact_form_meta_box($item) {
    ?>

    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php _e('Name') ?></label>
                </th>
                <td>
                    <input id="name" name="name" type="text" style="width: 95%" value="<?php echo $item['name'] ?>" size="50" class="code" placeholder="<?php _e('Your name') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('E-Mail') ?></label>
                </th>
                <td>
                    <input id="email" name="email" type="email" style="width: 95%" value="<?php echo $item['email'] ?>" size="50" class="code" placeholder="<?php _e('Your E-Mail') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="url"><?php _e('URL') ?></label>
                </th>
                <td>
                    <input id="url" name="url" type="text" style="width: 95%" value="<?php echo $item['url'] ?>"
                           size="50" class="code" placeholder="<?php _e('') ?>">
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="subject"><?php _e('Subject') ?></label>
                </th>
                <td>
                    <input id="subject" name="subject" type="text" style="width: 95%" value="<?php echo $item['subject'] ?>"
                           size="50" class="code" placeholder="<?php _e('') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="message"><?php _e('Message') ?></label>
                </th>
                <td>
                    <textarea id="message" name="message"  type="textarea" style="width: 95%" class="code" placeholder="<?php _e('') ?>" required><?php echo $item['message'] ?>
                    </textarea>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

function contact_form_validation($item) {
    $messages = array();

    if (empty($messages))
        return true;
    return implode('<br />', $messages);
}

if (isset($_POST["reset"])) {
    add_action('admin_init', array('ContactPress', 'reset_contact_admin'));
}
if (isset($_POST["reset_error"])) {
    add_action('admin_init', array('ContactPress', 'reset_contact_error_setting'));
}
if (isset($_POST['reset_bulk'])) {
    add_action('admin_init', array('ContactPress', 'reset_contact_bulk_setting'));
}
if (isset($_POST['reset_thanks'])) {
    add_action('admin_init', array('ContactPress', 'reset_contact_thankyou_setting'));
}
if (isset($_POST['reset_credit'])) {
    add_action('admin_init', array('ContactPress', 'credit_link_activation'));
}

function contact_form_compose() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contactpress'; // do not forget about tables prefix

    $message = '';
    $notice = '';

// this is default $item which will be used for new records
    $default = array(
        'email' => '',
        'subject' => '',
        'message' => '',
    );

// here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        if (isset($_POST['submit1'])) {
            $item = shortcode_atts($default, $_REQUEST);
            $admin_email1 = get_option('admin_email');
            $to_mail = $_POST['email'];
            $subject = $_POST['subject1'];
            $message1 = $_POST['message1'];
            $blogname = get_option('blogname');
            $header = 'MIME-Version: 1.0' . "\r\n";
            $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $header .= "From:" . $blogname . " <$admin_email1>";
            $send = mail($to_mail, $subject, $message1, $header);
            if ($send) {
                $message = __('Mail Send Successfully');
            } else {
                $notice = __('There was an Error While Sending a Mail');
            }
        }
    } else {

        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found');
            }
        }
    }

// here we adding our custom meta box
    add_meta_box('compose_form_meta_box', 'Compose Mail', 'compose_form_meta_box', 'contact', 'normal', 'default');
    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>ContactPress Compose Message<a class="add-new-h2"
                                           href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contact_form_a_plus'); ?>"><?php _e('back to Edit') ?></a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('contact', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('Send') ?>" id="submit" class="button-primary" name="submit1">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function contact_form_reply() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contactpress'; // do not forget about tables prefix

    $message = '';
    $notice = '';

// this is default $item which will be used for new records
    $default = array(
        'email' => '',
        'subject' => '',
        'message' => '',
    );

// here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        if (isset($_POST['submit2'])) {
            $item = shortcode_atts($default, $_REQUEST);
            $admin_email1 = get_option('admin_email');
            $to_mail = $_POST['email'];
            $subject = $_POST['subject1'];

            $message1 = "\r\n" . $_POST['message2'];
            $blogname = get_option('blogname');
            $header = 'MIME-Version: 1.0' . "\r\n";
            $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $header .= "From:" . $blogname . "<$admin_email1>";
            $send = mail($to_mail, $subject, $message1, $header);
            if ($send) {
                $message = __('Mail Send Successfully');
            } else {
                $notice = __('There was an Error While Sending a Mail');
            }
        }
    } else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found');
            }
        }
    }

// here we adding our custom meta box
    add_meta_box('reply_form_meta_box', 'Reply Mail', 'reply_form_meta_box', 'reply', 'normal', 'default');
    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>ContactPress Compose Message<a class="add-new-h2"
                                           href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contact_form_a_plus'); ?>"><?php _e('back to Edit') ?></a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('reply', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('Send') ?>" id="submit" class="button-primary" name="submit2">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function compose_form_meta_box($item) {
    ?>

    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('From') ?></label>
                </th>
                <td>
                    <input id="email1" name="email1" type="email1" style="width: 95%" value="<?php echo get_option('admin_email'); ?>" size="50" class="code" />
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('To E-Mail') ?></label>
                </th>
                <td>
                    <input id="email" name="email" type="email" style="width: 95%" value="<?php echo $item['email'] ?>" size="50" class="code" />
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="subject"><?php _e('Subject') ?></label>
                </th>
                <td>
                    <input id="subject1" name="subject1" type="text" style="width: 95%" value=""
                           size="50" class="code" placeholder="<?php _e('') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="message1"><?php _e('Message') ?></label>
                </th>
                <td>

                </td>
            </tr>
        </tbody>
    </table>
    <?php
    $settings = array(
        'textarea_name' => 'message1',
        'media_buttons' => true,
        'tinymce' => array(
            'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
            'bullist,blockquote,|,justifyleft,justifycenter' .
            ',justifyright,justifyfull,|,link,unlink,|' .
            ',spellchecker,wp_fullscreen,wp_adv'
        )
    );
    wp_editor('', 'content', $settings);
    ?>
    <?php
}

function reply_form_meta_box($item) {
    ?>

    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('From') ?></label>
                </th>
                <td>
                    <input id="email2" name="email2" type="email1" style="width: 95%" value="<?php echo get_option('admin_email'); ?>" size="50" class="code" />
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('To E-Mail') ?></label>
                </th>
                <td>
                    <input id="email" name="email" type="email" style="width: 95%" value="<?php echo $item['email'] ?>" size="50" class="code" />
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="subject1"><?php _e('Subject') ?></label>
                </th>
                <td>
                    <input id="subject1" name="subject1" type="text" style="width: 95%" value="Re:<?php echo $item['subject'] ?>"
                           size="50" class="code" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="message2"><?php _e('Message') ?></label>
                </th>
                <td>

                </td>
            </tr>
        </tbody>
    </table>
    <?php
    $From = get_option('admin_email');
    $To = $item['name'] . $item['email'];
    $Subject = $item['subject'];
    $Date = $item['time'];

    $settings = array(
        'textarea_name' => 'message2',
        'media_buttons' => true,
        'tinymce' => array(
            'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
            'bullist,blockquote,|,justifyleft,justifycenter' .
            ',justifyright,justifyfull,|,link,unlink,|' .
            ',spellchecker,wp_fullscreen,wp_adv'
        )
    );
    wp_editor("\r\n\r\n\r\n\r\n<hr>\n<br/>\n" . "From : $From \r\n<br/>" . "To : $To \r\n<br/>" . "Subject : $Subject \r\n<br/>" . "Date : $Date \r\n\n<br/><br/>" . $item[message], 'content', $settings);
    ?>
    <?php
}

function credit_link() {
    if (get_option('credit_link_contactpress') != 2) {
        ?>
        <center><small> <small align="center"> <?php echo get_option('credits_text'); ?> <a href="<?php echo get_option('credits_defaults'); ?>" rel="<?php echo get_option('credits_nofollow'); ?>" > <?php echo get_option('credits_name'); ?></a> </small></small></center>
        <?php
    }
}

register_activation_hook(__FILE__, array('ContactPress', 'db_install'));
register_activation_hook(__FILE__, array('ContactPress', 'credit_link_activation'));
register_activation_hook(__FILE__, array('ContactPress', 'reset_contact_admin'));
register_activation_hook(__FILE__, array('ContactPress', 'reset_contact_bulk_setting'));
register_activation_hook(__FILE__, array('ContactPress', 'reset_contact_thankyou_setting'));
register_activation_hook(__FILE__, array('ContactPress', 'reset_contact_error_setting'));
add_action('wp_ajax_add_contactform', array('ContactPress', 'db_install_data'));
add_action('wp_ajax_nopriv_add_contactform', array('ContactPress', 'db_install_data'));
add_action('admin_menu', array('ContactPress', 'contact_form_admin_menu'));
add_shortcode('contactpress', array('ContactPress', 'contactformui'));
add_action('admin_init', array('ContactPress', 'update_contact_admin'));
add_filter('widget_text', 'do_shortcode');
add_action('wp_footer', 'credit_link');
?>