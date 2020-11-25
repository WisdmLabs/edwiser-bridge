<?php

/**
 * Handles frontend form submissions.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class Eb_Theme_Compatibility
{
	
	public function __construct()
	{

	}


	public function eb_content_start_theme_compatibility($wrapper_args)
	{

		$template = get_option('template');

		switch ($template) {

		        //Divi
		    case 'Divi':
		        echo '<div id="content-area" class="clearfix"> <div id="left-area">';
		        break;

		    case 'flatsome':
		        echo '<div class="large-9 col">';
		        break;

		    default:
		        echo '<div>';
		        break;
		}


	}




	public function eb_content_end_theme_compatibility($wrapper_args)
	{

		$template = get_option('template');

		switch ($template) {
		        //Divi
		    case 'Divi':
		        echo '</div>';
		        break;
		    default:
		        // Divi container
		        echo '</div>';
		        break;
		}


	}




	public function eb_sidebar_start_theme_compatibility($wrapper_args)
	{

		$template = get_option('template');

		switch ($template) {

		        //Divi
		    /*case 'Divi':
		        echo '<div class="large-3 col">';
		        break;*/

		    case 'flatsome':
		        echo '<div class="large-3 col">';
		        break;

		    default:
		        echo '<div>';
		        break;
		}


	}





	public function eb_sidebar_end_theme_compatibility($wrapper_args)
	{

		$template = get_option('template');

		switch ($template) {

		        //Divi
		    case 'Divi':
		        echo '</div>';
		        break;

		    case 'flatsome':
		        echo '</div>';
		        break;

		    default:
		        echo '</div>';
		        break;
		}


	}






}
