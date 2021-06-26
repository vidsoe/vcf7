<?php
/*
Author: Vidsoe
Author URI: https://vidsoe.com
Description: Vidsoe - Contact Form 7
Domain Path:
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network: true
Plugin Name: VCF7
Plugin URI: https://github.com/vidsoe/vcf7
Requires at least: 5.7
Requires PHP: 5.6
Text Domain: vcf7
Version: 1.6.26
*/

if(defined('ABSPATH')){
    require_once(plugin_dir_path(__FILE__) . 'classes/class-vcf7.php');
    vcf7::instance(__FILE__);
}
