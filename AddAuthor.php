<?php
/*
Plugin Name: Author Name in RSS Feed
Plugin URI: http://roesapps.com/tag/author-in-rss/
Description: Adds the Author Name to the Title Line of each post in the RSS feed
Author: Courtney Roes
Version: 0.1
Author URI: http://www.RoesApps.com
*/   
   
/*  Copyright 2010  

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Based on Wordpress Plugin Template found at http://pressography.com/  */

/**
* Guess the wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


if (!class_exists('HCRAddAuthor')) {
    class HCRAddAuthor {
        //This is where the class variables go, don't forget to use @var to tell what they're for
        /**
        * @var string The options string name for this plugin
        */
        var $optionsName = 'HCRAddAuthor_options';
        
        /**
        * @var string $localizationDomain Domain used for localization
        */
        var $localizationDomain = "HCRAddAuthor";
        
        /**
        * @var string $pluginurl The path to this plugin
        */ 
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';
            
        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();
        
        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function HCRAddAuthor(){$this->__construct();}
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //Language Setup
            $locale = get_locale();
            $mo = dirname(__FILE__) . "/languages/" . $this->localizationDomain . "-".$locale.".mo";
            load_textdomain($this->localizationDomain, $mo);

            //"Constants" setup
            $this->thispluginurl = PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = PLUGIN_PATH . '/' . dirname(plugin_basename(__FILE__)).'/';
            
            //Initialize the options
            //This is REQUIRED to initialize the options when the plugin is loaded!
            $this->getOptions();
            
            //Actions        
            add_action("admin_menu", array(&$this,"admin_menu_link"));

            
            //Widget Registration Actions
            add_action('plugins_loaded', array(&$this,'register_widgets'));
            
            /*
            add_action("wp_head", array(&$this,"add_css"));
            add_action('wp_print_scripts', array(&$this, 'add_js'));
            */
            
            //Filters
            /*
            add_filter('the_content', array(&$this, 'filter_content'), 0);
            */
			add_filter('the_title_rss', array(&$this, 'HCRAddAuthorCode'), 10, 0);
        }
        
        /**
		* Adds the Author to the Title Line on the RSS feeds
		*/
		function HCRAddAuthorCode() {
			$HCRSeparator = stripslashes($this->options['HCRAddAuthor_separator']);
			$HCRSpacing = '';
			if ($this->options['HCRAddAuthor_enabled'] == 'double') {
				$HCRSpacing = '&nbsp;&nbsp;';
				} elseif ( $this->options['HCRAddAuthor_enabled'] == 'single') {
				$HCRSpacing = '&nbsp;';
				};
			$newtitle = the_title('','', true) . $HCRSpacing . $HCRSeparator . $HCRSpacing . get_the_author();
			//$newtitle = ' - ';
			//$newtitle = the_author();
			
			return $newtitle;
			
		}
        
        
        /**
        * Retrieves the plugin options from the database.
        * @return array
        */
        function getOptions() {
            //Don't forget to set up the default options
            if (!$theOptions = get_option($this->optionsName)) {
                $theOptions = array('default'=>'options');
                update_option($this->optionsName, $theOptions);
            }
            $this->options = $theOptions;
            
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //There is no return here, because you should use the $this->options variable!!!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }
        /**
        * Saves the admin options to the database.
        */
        function saveAdminOptions(){
            return update_option($this->optionsName, $this->options);
        }
        
        /**
        * @desc Adds the options subpanel
        */
        function admin_menu_link() {
            //If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
            //reflect the page filename (ie - options-general.php) of the page your plugin is under!
            add_options_page('Author Name in RSS Feed', 'Author Name in RSS Feed', 10, basename(__FILE__), array(&$this,'admin_options_page'));
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
        }
        
        /**
        * @desc Adds the Settings link to the plugin activate/deactivate page
        */
        function filter_plugin_actions($links, $file) {
           //If your plugin is under a different top-level menu than Settiongs (IE - you changed the function above to something other than add_options_page)
           //Then you're going to want to change options-general.php below to the name of your top-level page
           $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
           array_unshift( $links, $settings_link ); // before other links

           return $links;
        }
        
        /**
        * Adds settings/options page
        */
        function admin_options_page() { 
            if($_POST['HCRAddAuthor_save']){
                if (! wp_verify_nonce($_POST['_wpnonce'], 'HCRAddAuthor-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.'); 
                $this->options['HCRAddAuthor_separator'] = esc_attr(trim($_POST['HCRAddAuthor_separator']));                   
                $this->options['HCRAddAuthor_enabled'] = $_POST['HCRAddAuthor_enabled'];
                                        
                $this->saveAdminOptions();
                
                echo '<div class="updated"><p>Success! Your changes were sucessfully saved!</p></div>';
            }
?>                                   
                <div class="wrap">
                <h2>Author Name in RSS Feed</h2>
                <form method="post" id="HCRAddAuthor_options">
                <?php wp_nonce_field('HCRAddAuthor-update-options'); ?>
                    <table width="100%" cellspacing="2" cellpadding="5" class="form-table"> 
                        <tr valign="top"> 
                            <th width="33%" scope="row"><?php _e('Separator (ex. >> , by , von ):', $this->localizationDomain); ?></th> 
                            <td><input name="HCRAddAuthor_separator" type="text" id="HCRAddAuthor_separator" size="45" value="<?php echo $this->options['HCRAddAuthor_separator'] ;?>"/>
                        </td> 
                        </tr>
                        <tr valign="top"> 
                            <th><label for="HCRAddAuthor_enabled"><?php _e('Spacing around separator:', $this->localizationDomain); ?></label></th><td><input name="HCRAddAuthor_enabled" type="radio" id="HCRAddAuthor_enabled" value="double" <?php if ($this->options['HCRAddAuthor_enabled']=="double") echo "checked=\"checked\""; ?> /> 
                            <?php _e('Double Spacing', $this->localizationDomain); ?> <input name="HCRAddAuthor_enabled" type="radio" id="HCRAddAuthor_enabled" value="single" <?php if ($this->options['HCRAddAuthor_enabled']=="single") echo "checked=\"checked\""; ?> />
                            <?php _e('Single Spacing', $this->localizationDomain); ?> <input name="HCRAddAuthor_enabled" type="radio" id="HCRAddAuthor_enabled" value="nospacing"<?php if ($this->options['HCRAddAuthor_enabled']=="nospacing") echo "checked=\"checked\""; ?> />
                            <?php _e('No Spacing', $this->localizationDomain); ?></td>
                        </tr>
                        <tr>
                            <th colspan=2><input type="submit" name="HCRAddAuthor_save" value="Save" /></th>
                        </tr>
                    </table>
                </form>
                <?php
        }
        
        /*
        * ============================
        * Plugin Widgets
        * ============================
        */                        
        function register_widgets() {
            //Make sure the widget functions exist
            if ( function_exists('wp_register_sidebar_widget') ) {
                //============================
                //Example Widget 1
                //============================
                function display_HCRAddAuthorWidget($args) {                    
                    extract($args);
                    echo $before_widget . $before_title . $this->options['title'] . $after_title;
                    echo '<ul>';
                    //!!! Widget 1 Display Code Goes Here!
                    echo '</ul>';
                    echo $after_widget;
                }                                                                             
                function HCRAddAuthorWidget_control() {            
                    if ( $_POST["HCRAddAuthor_HCRAddAuthorWidget_submit"] ) {
                        $this->options['HCRAddAuthor-comments-title'] = stripslashes($_POST["HCRAddAuthor-comments-title"]);        
                        $this->options['HCRAddAuthor-comments-template'] = stripslashes($_POST["HCRAddAuthor-comments-template"]);
                        $this->options['HCRAddAuthor-hide-admin-comments'] = ($_POST["HCRAddAuthor-hide-admin-comments"]=='on'?'':'1');
                        $this->saveAdminOptions();
                    }                                                                  
                    $title = htmlspecialchars($options['HCRAddAuthor-comments-title'], ENT_QUOTES);
                    $template = htmlspecialchars($options['HCRAddAuthor-comments-template'], ENT_QUOTES);
                    $hide_admin_comments = $options['HCRAddAuthor-hide-admin-comments'];      
                ?>
                    <p><label for="HCRAddAuthor-comments-title"><?php _e('Title:', $this->localizationDomain); ?> <input style="width: 250px;" id="HCRAddAuthor-comments-title" name="HCRAddAuthor-comments-title" type="text" value="<?= $title; ?>" /></label></p>               
                    <p><label for="HCRAddAuthor-comments-template"><?php _e('Template:', $this->localizationDomain); ?> <input style="width: 250px;" id="HCRAddAuthor-comments-template" name="HCRAddAuthor-comments-template" type="text" value="<?= $template; ?>" /></label></p>
                    <p><?php _e('The template is made up of HTML and tokens. You can get a list of available tokens at the', $this->localizationDomain); ?> <a href='http://pressography.com/plugins/wp-HCRAddAuthor/#tokens-recent' target='_blank'><?php _e('plugin page', $this->localizationDomain); ?></a></p>
                    <p><input id="HCRAddAuthor-hide-admin-comments" name="HCRAddAuthor-hide-admin-comments" type="checkbox" <?= ($hide_admin_comments=='1')?'':'checked="CHECKED"'; ?> /> <label for="HCRAddAuthor-hide-admin-comments"><?php _e('Show Admin Comments', $this->localizationDomain); ?></label></p>
                    <input type="hidden" id="HCRAddAuthor_HCRAddAuthorWidget_submit" name="HCRAddAuthor_HCRAddAuthorWidget_submit" value="1" />
                <?php
                }
                $widget_ops = array('classname' => 'HCRAddAuthorWidget', 'description' => __( 'Widget Description', $this->localizationDomain ) );
                wp_register_sidebar_widget('HCRAddAuthor-HCRAddAuthorWidget', __('Widget Title', $this->localizationDomain), array($this, 'display_HCRAddAuthorWidget'), $widget_ops);
                wp_register_widget_control('HCRAddAuthor-HCRAddAuthorWidget', __('Widget Title', $this->localizationDomain), array($this, 'HCRAddAuthorWidget_control'));
                
            }  
        }       
        
  } //End Class
} //End if class exists statement

//instantiate the class
if (class_exists('HCRAddAuthor')) {
    $HCRAddAuthor_var = new HCRAddAuthor();
}
?>