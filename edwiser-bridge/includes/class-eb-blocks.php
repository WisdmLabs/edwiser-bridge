<?php

class EdwiserBridge_Blocks
{
    public function __construct()
    {
        add_action('init', array($this, 'eb_register_blocks'));
        add_action('wp_enqueue_scripts', array($this, 'eb_woo_storeapi_nonce'));
        add_filter('block_categories_all', array($this, 'eb_register_edwiser_category'));
    }

    public static function eb_register_blocks()
    {
        register_block_type(__DIR__  . '/../blocks/build/courses');
        register_block_type(__DIR__  . '/../blocks/build/course-description');
    }

    public static function eb_woo_storeapi_nonce()
    {
        wp_register_script('eb_woo_storeapi_nonce', '', [], '', true);

        wp_enqueue_script('eb_woo_storeapi_nonce');

        $nonce = wp_create_nonce('wc_store_api');

        wp_localize_script(
            'eb_woo_storeapi_nonce',
            'ebStoreApiNonce',
            array(
                'nonce' => $nonce,
            )
        );
    }

    public static function eb_register_edwiser_category($categories)
    {

        $categories[] = array(
            'slug'  => 'edwiser',
            'title' => 'Edwiser'
        );

        return $categories;
    }
}

new EdwiserBridge_Blocks();
