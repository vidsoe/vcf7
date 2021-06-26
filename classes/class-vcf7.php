<?php

if(!class_exists('vcf7')){
    final class vcf7 {

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    	//
    	// private static
    	//
    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        private static $instance = null;

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    	//
    	// public static
    	//
    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public static function instance($file = ''){
            if(null === self::$instance){
                if(@is_file($file)){
                    self::$instance = new self($file);
                } else {
                    wp_die(sprintf(__('File &#8220;%s&#8221; doesn&#8217;t exist?'), $file));
                }
            }
            return self::$instance;
    	}

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    	//
    	// private
    	//
    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        private $active_tab = 0, $file = '';

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	private function __clone(){}

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	private function __construct($file = ''){
            $this->file = $file;
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
            add_filter('redirect_post_location', [$this, 'redirect_post_location'], 10, 2);
            add_filter('register_post_type_args', [$this, 'register_post_type_args'], 10, 2);
            add_filter('rwmb_meta_boxes', [$this, 'rwmb_meta_boxes']);
            add_filter('wpcf7_editor_panels', [$this, 'wpcf7_editor_panels']);
        }

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        private function is_editing(){
            global $pagenow;
            if(!is_admin()){
                return false;
            }
            if('post.php' !== $pagenow){
                return false;
            }
            if(!isset($_GET['action'])){
                return false;
            }
            if('edit' !== $_GET['action']){
                return false;
            }
            if(!isset($_GET['active-tab'])){
                return false;
            }
            if(!isset($_GET['post'])){
                return false;
            }
            if('wpcf7_contact_form' !== get_post_type($_GET['post'])){
                return false;
            }
            if(!isset($_GET['_wpnonce'])){
                return false;
            }
            return wp_verify_nonce($_GET['_wpnonce'], 'vidsoe-edit-' . $_GET['post']);
        }

    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    	//
    	// public
    	//
    	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function admin_enqueue_scripts(){
            if($this->is_editing()){
                $src = plugin_dir_url($this->file) . 'assets/vcf7.css';
                $ver = filemtime(plugin_dir_path($this->file) . 'assets/vcf7.css');
                wp_enqueue_style('vcf7', $src, [], $ver);
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function redirect_post_location($location, $post_id){
            if('wpcf7_contact_form' !== get_post_type($post_id)){
                return $location;
            }
            $referer = wp_get_referer();
            if(false === $referer){
                return $location;
            }
            return add_query_arg('message', 1, $referer);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function register_post_type_args($args, $post_type){
            if('wpcf7_contact_form' !== $post_type){
                return $args;
            }
            if(!$this->is_editing()){
                return $args;
            }
            $args['show_in_menu'] = false;
            $args['show_ui'] = true;
            $args['supports'] = ['title'];
            return $args;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function rwmb_meta_boxes($meta_boxes){
            if($this->is_editing()){
                $url = admin_url('admin.php?action=edit&active-tab=' . $_GET['active-tab'] . '&page=wpcf7&post=' . $_GET['post']);
                $meta_boxes[] = [
        			'context' => 'side',
        			'fields' => [
        				[
        					'std' => '<a href="' . $url . '">' . __('Go back') . '</a>',
        					'type' => 'custom_html',
        				],
        			],
        			'id' => 'vcf7_go_back',
        			'post_types' => 'wpcf7_contact_form',
        			'priority' => 'low',
        			'title' => __('Additional Settings', 'contact-form-7'),
        		];
            }
            return $meta_boxes;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function wpcf7_editor_panel($contact_form){
            $html = '<h2>' . __('Additional Settings', 'contact-form-7') . '</h2>';
            $html .= '<fieldset>';
            $html .= '<legend>';
            if($contact_form->id()){
                $url = admin_url('post.php?action=edit&active-tab=' . $this->active_tab . '&post=' . $contact_form->id());
                $nonce_url = wp_nonce_url($url, 'vcf7-edit-' . $contact_form->id());
                $html .= '<a href="' . $nonce_url . '">' . __('Edit This') . '</a>';
            } else {
                $html .= __('Save Changes');
            }
            $html .= '</legend>';
            $html .= '</fieldset>';
            echo $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function wpcf7_editor_panels($panels){
            if(isset($panels['vcf7'])){
               return $panels;
            }
            $this->active_tab = count($panels);
            $panels['vcf7'] = [
               'callback' => [$this, 'wpcf7_editor_panel'],
               'title' => 'Vidsoe',
            ];
            return $panels;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
    if(!function_exists('vcf7')){
        function vcf7(){
            return vcf7::instance();
        }
    }
}
