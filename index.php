<?php
/*
Plugin Name:  Cloudflare CAPTURE for Advance Forms
Description:  Adds Cloudflare Turnstile CAPTURE to Advanced Forms
Version:      1.0.0
Author:       Matt Davies
*/


namespace mrldavies\CCAF;

require_once(__DIR__ . '/src/Capture.php');

$plugin = new Capture(CAPTURE_SITE_KEY, CAPTURE_SECRET_KEY);
$plugin->init();
