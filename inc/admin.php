<?php

function contactform_aplus_admin_pages() {
    ?>
    <style type="text/css">
        #gif,#gif1,#gif2,#gif3,#gif4
        {
            width:100%;
            height:100%;
            display:none;
            margin-left: 393px;
        }
        #result,#result1,#result2,#result3,#result4
        {
            display:none;
            border-color:#e8426d;
            background-color:#FFFFF;
            color:#e8426d;
            border: solid;
            width:160px;
            text-align: center;
            margin-left: 389px;
        }


    </style>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            jQuery('#submit_general').click(function($)
            {
                jQuery('#gif').css("display", "block");


            });
        });
        function submitgeneral() {
            jQuery.ajax({type: 'POST', url: 'options.php', data: jQuery('#form_general').serialize(), success: function(response) {


                    jQuery('#gif').css("display", "none");
                    jQuery('#result').css("display", "block");
                    jQuery('#result').html("Settings Saved");
                    jQuery('#result').fadeOut(2500, "linear");
                }});

            return false;
        }
        jQuery(document).ready(function($) {
            jQuery('#submit_thanks').click(function($)
            {
                jQuery('#gif1').css("display", "block");


            });
        });
        function submitthanks() {
            jQuery.ajax({type: 'POST', url: 'options.php', data: jQuery('#form_thanks').serialize(), success: function(response) {


                    jQuery('#gif1').css("display", "none");
                    jQuery('#result1').css("display", "block");
                    jQuery('#result1').html("Settings Saved");
                    jQuery('#result1').fadeOut(2500, "linear");
                }});

            return false;
        }

        jQuery(document).ready(function($) {
            jQuery('#submit_bulk').click(function($)
            {
                jQuery('#gif2').css("display", "block");


            });
        });
        function submitbulk() {
            jQuery.ajax({type: 'POST', url: 'options.php', data: jQuery('#form_bulk').serialize(), success: function(response) {


                    jQuery('#gif2').css("display", "none");
                    jQuery('#result2').css("display", "block");
                    jQuery('#result2').html("Settings Saved");
                    jQuery('#result2').fadeOut(2500, "linear");
                }});

            return false;
        }
        jQuery(document).ready(function($) {
            jQuery('#submit_error').click(function($)
            {
                jQuery('#gif3').css("display", "block");


            });
        });
        function submiterror() {
            jQuery.ajax({type: 'POST', url: 'options.php', data: jQuery('#form_error').serialize(), success: function(response) {


                    jQuery('#gif3').css("display", "none");
                    jQuery('#result3').css("display", "block");
                    jQuery('#result3').html("Settings Saved");
                    jQuery('#result3').fadeOut(2500, "linear");
                }});

            return false;
        }
        function submitcreditlink() {
            jQuery.ajax({type: 'POST', url: 'options.php', data: jQuery('#form_credit_link').serialize(), success: function(response) {


                    jQuery('#gif4').css("display", "none");
                    jQuery('#result4').css("display", "block");
                    jQuery('#result4').html("Settings Saved");
                    jQuery('#result4').fadeOut(2500, "linear");
                }});

            return false;
        }
        jQuery(document).ready(function($) {
            jQuery('#submit_credit_link').click(function($)
            {
                jQuery('#gif4').css("display", "block");


            });
        });
    </script>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
    <link href ="<?php echo WP_CONTENT_URL; ?>/plugins/contactpress/css/adminstyle.css" type="text/css" rel="stylesheet"/>
    <div class="wrap">
        <?php
        $bpageheader = true;
        if ($bpageheader == true) {
            ?>
            <div class="ic"></div>
            <a href="http://fantasticplugins.com" target="_blank"><h2><img src="<?php echo WP_CONTENT_URL; ?>/plugins/contactpress/assets/favicon.png"/></h2></a>
        <?php } ?>
        <div class="left">
            <div class="metabox-holder4">
                <div class="postbox4">
                    <h3>General Settings</h3>
                    <div class="inside4">
                        <form id="form_general" onsubmit="return submitgeneral();">
                            <?php settings_fields('contact_form_a_plus'); ?>
                            <?php
                            $cmt = get_option('label_comment');
                            $enable_responsiveness = get_option('enable_responsiveness');
                            ?>

                            <ul>
                                <li>
                                    <label>Name: Initial Text</label>
                                    <input type="text" class="text_general" style="margin-left:250px" name="label_name" value="<?php echo get_option('label_name'); ?>"/>
                                </li>
                                <li>
                                    <label>Email: Initial Text</label>
                                    <input type="text" class="text_general" style="margin-left:250px" name="label_email" value="<?php echo get_option('label_email'); ?>"/>
                                </li>
                                <li>
                                    <label>Subject: Initial Text</label>
                                    <input type="text" class="text_general1" style="margin-left:239px" name="label_subject" value="<?php echo get_option('label_subject'); ?>"/>
                                </li>
                                <li>
                                    <label>URL: Initial Text</label>
                                    <input type="text" class="text_general2" style="margin-left:260px" name="label_url" value="<?php echo get_option('label_url'); ?>"/>
                                </li>
                                <li>
                                    <label>Remove Comment Box</label>
                                    <input type="checkbox" class="checkbox_general" name="label_comment" value="1"<?php checked('1', $cmt); ?>/>
                                </li>
                                <li>
                                    <label>Enable Responsiveness</label>
                                    <input type="checkbox" class="checkbox_general" style="margin-left:221px;" name="enable_responsiveness" value="1"<?php checked('1', $enable_responsiveness); ?>/>
                                </li>
                            </ul>
                            <div id="gif"><img src="<?php echo WP_PLUGIN_URL; ?>/contactpress/assets/bar.gif"/></div>
                            <div id="result"></div>
                            <p class="submit">
                                <input type="submit" value="submit" name="submit" id="submit_general" class="button-primary"/>
                            </p>

                        </form>
                        <form action="" method="post" class="form-1">
                            <input type="submit" value="Reset" name="reset" class="button-secondary"/>
                            <input name="action" type="hidden" value="reset" />

                        </form><br/>
                        <br/>

                    </div>
                </div>
            </div>
            <div class="metabox-holder4">
                <div class="postbox4">
                    <h3>Thank You Settings</h3>
                    <div class="inside4">
                        <form id="form_thanks" onsubmit="return submitthanks();">
                            <?php settings_fields('contact_form_a_plus_thanks'); ?>
                            <ul>
                                <li>
                                    <label>Thank You Message</label>
                                    <textarea type="textarea" name="thanks_contact" rows="5" cols="35" class="thanks_contact"><?php echo get_option('thanks_contact'); ?></textarea>
                                </li>
                            </ul>
                            <div id="gif1"><img src="<?php echo WP_PLUGIN_URL; ?>/contactpress/assets/bar.gif"/></div>
                            <div id="result1"></div>
                            <p class="submit">
                                <input type="submit" value="submit" id="submit_thanks" name="submit" class="button-primary"/>
                            </p>

                        </form>
                        <form action="" method="post" class="form-1">
                            <input type="submit" value="Reset" name="reset_thanks" class="button-secondary"/>
                            <input name="action" type="hidden" value="reset" />

                        </form><br/>
                        <br/>
                    </div>
                </div>
            </div>
            <div class="metabox-holder4">
                <div class="postbox4">
                    <h3>Bulk Mail Settings</h3>
                    <div class="inside4">
                        <form id="form_bulk" onsubmit="return submitbulk();">
                            <?php settings_fields('contact_form_a_plus_bulk'); ?>
                            <ul>
                                <li>
                                    <label>Bulk Mail Subject</label>
                                    <input type="text" name="bulk_subject" class="text_general" value="<?php echo get_option('bulk_subject'); ?>"/>
                                </li>
                                <li>
                                    <label>Bulk Mail Messages</label>
                                    <textarea type="textarea" name="bulk_messages" rows="5" cols="50" style="margin-left:216px;" class="bulk_messages"><?php echo get_option('bulk_messages'); ?></textarea>
                                </li>
                            </ul>
                            <div id="gif2"><img src="<?php echo WP_PLUGIN_URL; ?>/contactpress/assets/bar.gif"/></div>
                            <div id="result2"></div>
                            <p class="submit">
                                <input type="submit" id="submit_bulk" value="submit" name="submit" class="button-primary"/>
                            </p>

                        </form>
                        <form action="" method="post" class="form-1">
                            <input type="submit" value="Reset" name="reset_bulk" class="button-secondary"/>
                            <input name="action" type="hidden" value="reset" />

                        </form><br/>
                        <br/>
                    </div>
                </div>
            </div>

            <div class="metabox-holder4">
                <div class="postbox4">
                    <h3>Error Settings</h3>
                    <div class="inside4">
                        <form  id="form_error" onsubmit="return submiterror();">
                            <?php settings_fields('contact_form_a_plus_error'); ?>
                            <ul>
                                <li>
                                    <label>Name Error Message:</label>
                                    <textarea type="textarea" name="error_name" rows="5" cols="50" style="margin-left:216px;" class="error_messages"><?php echo get_option('error_name'); ?></textarea>
                                </li>
                                <li>
                                    <label>Email Field Error Message:(If Empty)</label>
                                    <textarea type="textarea" name="error_empty_email" rows="5" cols="50" style="margin-left:103px;" class="error_messages"><?php echo get_option('error_empty_email'); ?></textarea>
                                </li>
                                <li>
                                    <label>Email Field Error Message:(If it is not Mail)</label>
                                    <textarea type="textarea" name="error_email" rows="5" cols="50" style="margin-left:61px;" class="error_messages"><?php echo get_option('error_email'); ?></textarea>
                                </li>
                                <li>
                                    <label>Subject Error Message:</label>
                                    <textarea type="textarea" name="error_subject" rows="5" cols="50" style="margin-left:209px;" class="error_messages"><?php echo get_option('error_subject'); ?></textarea>
                                </li>
                                <li>
                                    <label>Message Field Error Message:</label>
                                    <textarea type="textarea" name="error_msg" rows="5" cols="50" style="margin-left:161px;" class="error_messages"><?php echo get_option('error_msg'); ?></textarea>
                                </li>
                                <li>
                                    <label>Captcha Field Error Message:(If Empty)</label>
                                    <textarea type="textarea" name="error_empty_captcha" rows="5" cols="50" style="margin-left:91px;" class="error_empty_captcha"><?php echo get_option('error_empty_captcha'); ?></textarea>
                                </li>
                                <li>
                                    <label>Captcha Field Error Message:(If it is not true)</label>
                                    <textarea type="textarea" name="error_captcha" rows="5" cols="50" style="margin-left:53px;" class="error_captcha"><?php echo get_option('error_captcha'); ?></textarea>
                                </li>
                            </ul>
                            <div id="gif3"><img src="<?php echo WP_PLUGIN_URL; ?>/contactpress/assets/bar.gif"/></div>
                            <div id="result3"></div>
                            <p class="submit">
                                <input type="submit" id="submit_error" value="submit" name="submit" class="button-primary"/>
                            </p>

                        </form>
                        <form action="" method="post" class="form-1">
                            <input type="submit" value="Reset" name="reset_error" class="button-secondary"/>
                            <input name="action" type="hidden" value="reset" />

                        </form><br/>
                        <br/>
                    </div>
                </div>
            </div>
            <div class="metabox-holder4">
                <div class="postbox4">
                    <h3>Credit Link</h3>
                    <div class="inside4">
                        <form id="form_credit_link" onsubmit="return submitcreditlink();">
                            <?php settings_fields('contactpress_credit_link'); ?>
                            <?php $credit_links = get_option('credit_link_contactpress'); ?>
                            <ul>
                                <li>
                                    <label>Credit Link </label>
                                    <input type="radio" class="radiobox_general" name="credit_link_contactpress" style="margin-left:309px;" value="1"<?php checked('1', $credit_links); ?>/><label>&nbsp;ON</label><br/>
                                    <input type="radio" class="radiobox_general" name="credit_link_contactpress" style="margin-left:393px;" value="2"<?php checked('2', $credit_links); ?>/><label>&nbsp;OFF</label><br/><br/>
                                </li>
                            </ul>
                            <div id="gif4"><img src="<?php echo WP_PLUGIN_URL; ?>/contactpress/assets/bar.gif"/></div>
                            <div id="result4"></div>
                            <p class="submit">
                                <input type="submit" id="submit_credit_link" value="submit" name="submit" class="button-primary"/>
                            </p>

                        </form>
                        <form action="" method="post" class="form-1">
                            <input type="submit" value="Reset" name="reset_credit" class="button-secondary"/>
                            <input name="action" type="hidden" value="reset" />

                        </form><br/>
                    </div>
                </div>
            </div>
            <div class="metabox-holder4">
                <div class="postbox4">
                    <h3>Usage</h3>
                    <div class="inside4">
                        <pre>Using Shortcode [contactpress] or &lt;?php echo do_shortcode('[contactpress]'); ?&gt;</pre>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php } ?>