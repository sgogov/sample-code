<?php
/**
 * Plugin Name: Pageant Classification Tagger Plugin
 * Plugin URI: TBD
 * Description: This plugin is allowing tagging classifications to Post
 * Version: 1.0.1
 * Author: Pageant
 * Author URI: TBD
 * License: TBD
 */
if (!defined('ABSPATH')) {
    exit;
}

define('PCT_PLUGIN_VERSION', '1.0.1');
define('PCT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PCT_PLUGIN_DB_TABLE_ASSETS', 'pageant_classification_assets');
define('PCT_PLUGIN_DB_TABLE_STRATEGIES', 'pageant_classification_strategies');
define('PCT_PLUGIN_DB_TABLE_REGIONS', 'pageant_classification_regions');
define('PCT_PLUGIN_DB_TABLE_COUNTRIES', 'pageant_classification_countries');

// include the Composer autoload file
require __DIR__ . '/vendor/autoload.php';

use Src\Controller\ClassificationSettings;
use Src\Controller\ClassificationSettingsProduct;
use Src\Controller\ClassificationTagger;
use Src\Controller\ClassificationTaggerElasticpressSupport;
use Src\Controller\ClassificationTaggerManagePostColumn;

class PageantClassificationTagger
{
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'pluginActivate']);

        // Add Javascript and CSS for admin screens
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdmin']);

        // Register Settings page and options
        new ClassificationSettings();
        
        // Register Sub Settings page form Mulity Product Management
        new ClassificationSettingsProduct();
        
        // Register meda boxes
        new ClassificationTagger();

        // Register new column on admin post listing page
        new ClassificationTaggerManagePostColumn();
        
        // Check if the Elasticpress plugin is enabled
        if (defined('EP_VERSION')) {
            // Expose data on elastic press
            new ClassificationTaggerElasticpressSupport();
        }
        
    }

    /**
     * @return void
     */
    public function pluginActivate(): void
    {
         global $wpdb;
         $charset_collate = $wpdb->get_charset_collate();
         
         // Prepager Create Table Query
         $createTableAssets = $this->queryCreateClassificationTable(PCT_PLUGIN_DB_TABLE_ASSETS, $charset_collate);
         $createTableStrategies = $this->queryCreateClassificationTable(PCT_PLUGIN_DB_TABLE_STRATEGIES, $charset_collate);
         $createTableRegions = $this->queryCreateClassificationTable(PCT_PLUGIN_DB_TABLE_REGIONS, $charset_collate);
         $createTableCountries = $this->queryCreateClassificationTable(PCT_PLUGIN_DB_TABLE_COUNTRIES, $charset_collate);
         
         // Execute the Query
         require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
         dbDelta($createTableAssets);
         dbDelta($createTableStrategies);
         dbDelta($createTableRegions);
         dbDelta($createTableCountries);
    }
    
    /**
     * @param string $tableName
     * @param string $charset_collate
     * 
     * @return string
     */
    private function queryCreateClassificationTable(string $tableName = '', string $charset_collate = ''): string
    {
        if (empty($tableName)) {
            return '';
        }
        
        return "CREATE TABLE IF NOT EXISTS " . $tableName . " (
             id int(9) NOT NULL AUTO_INCREMENT,
             post_id int(9) NOT NULL,
             classification_id int(9) NOT NULL,
             PRIMARY KEY (id)
         ) $charset_collate;";
        
    }

    /**
     * Enqueuing a JavaScript files and a CSS files for use on the editor
     * 
     * @return void
     */
    public function enqueueAdmin(): void
    {
        if (!is_admin()) {
            return;
        }

        $currentAdminScreen = get_current_screen();
        if ($currentAdminScreen->base == 'post' || $currentAdminScreen->base == 'classification-tagger_page_classification-multiproduct-support') {
            // CSS
            wp_enqueue_style(
                    'main-style-pageant-classification-tagger',
                    plugins_url('assets/css/main-classification-tagger.css', __FILE__),
                    null,
                    PCT_PLUGIN_VERSION
            );
            // JS
            wp_enqueue_script(
                    'main-js-pageant-classification-tagger',
                    plugins_url('assets/js/main-classification-tagger.js', __FILE__),
                    ['jquery-ui-core'],
                    PCT_PLUGIN_VERSION
            );
        }
    }

}

new PageantClassificationTagger;
