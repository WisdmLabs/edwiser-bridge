<?php
/**
 * Email Header.
 *
 * @package Edwiser bridge.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$header = $args['header']; // get email header.
$bg     = 'whitesmoke';

$wrapper            = '
    background-color:rgb(239, 239, 239);
    width:100%;
    -webkit-text-size-adjust:none !important;
    margin:0;
    padding: 70px 0 70px 0;
';
$template_container = '
    box-shadow:0 0 0 3px rgba(0,0,0,0.025) !important;
    border-radius:6px !important;
    background-color: rgb(223, 223, 223);
    border-radius:6px !important;
';
$template_header    = '
    background-color:rgb(70, 92, 148);
    border-top-left-radius:6px !important;
    border-top-right-radius:6px !important;
    border-bottom: 0;
    font-family:Arial;
    font-weight:bold;
    line-height:100%;
    vertical-align:middle;
';
$body_content       = '
    background-color: rgb(223, 223, 223);
    border-radius:6px !important;
';
$body_content_inner = '
    font-family:Arial;
    font-size:14px;
    line-height:150%;
    text-align:left;
';
$header_content_h1  = '
    color: white;
    margin:0;
    padding: 28px 24px;
    text-shadow: 0 1px 0 0;
    display:block;
    font-family:Arial;
    font-size:30px;
    font-weight:bold;
    text-align:left;
    line-height: 150%;
';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	</head>
	<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div style="<?php echo esc_html( $wrapper ); ?>">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr><td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container"
							style="<?php echo esc_html( $template_container ); ?>">
							<tr><td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header"
										style="<?php echo esc_html( $template_header ); ?>">
										<tr>
											<td>
												<h1 style="<?php echo esc_html( $header_content_h1 ); ?>">
													<?php echo esc_html( $header ); ?></h1>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td></tr>
							<tr>
								<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
										<tr>
											<td valign="top" style="<?php echo esc_html( $body_content ); ?>">
												<!-- Content -->
												<table border="0" cellpadding="20" cellspacing="0" width="100%">
													<tr>
														<td valign="top">
															<div style="<?php echo esc_html( $body_content_inner ); ?>">
