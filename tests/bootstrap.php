<?php
/**
 * bootstrap.php
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2026, Ashley Gibson
 * @license   MIT
 */

const ABSPATH = 'foo/bar';

require_once dirname(__DIR__).'/vendor/autoload.php';

WP_Mock::setUsePatchwork( true);
WP_Mock::bootstrap();
