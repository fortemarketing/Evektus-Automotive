<?php

/**
 * Upload rules.
 *
 * @package Evektus
 */

defined('ABSPATH') || exit;

/**
 * The smallest image the library will accept.
 *
 * 450 x 350 is the 'card' image size registered in functions.php, which is a
 * hard crop: an image smaller than this in either direction cannot fill a card
 * without being scaled up, so it arrives soft. Rejecting it at the door is the
 * only point at which that is cheap to fix - once it is in the library and on
 * a page, the person who notices is a visitor.
 *
 * Change the numbers through the filter rather than here, so a plugin or a
 * child theme can relax them without touching this file:
 *
 *   add_filter('evek_min_upload_dimensions', function (array $min): array {
 *       return array('width' => 800, 'height' => 600);
 *   });
 *
 * @return array{width: int, height: int}
 */
function evek_min_upload_dimensions(): array
{
	$min = apply_filters(
		'evek_min_upload_dimensions',
		array(
			'width'  => 450,
			'height' => 350,
		)
	);

	return array(
		'width'  => isset($min['width']) ? (int) $min['width'] : 0,
		'height' => isset($min['height']) ? (int) $min['height'] : 0,
	);
}

/**
 * Turn away images that are too small to use, before they reach the library.
 *
 * wp_handle_upload_prefilter is the hook that fires for a person choosing a
 * file, and not for wp_handle_sideload(), so an importer or a plugin pulling
 * images in programmatically is not caught by this.
 *
 * @param array<string, mixed> $file The $_FILES entry being uploaded.
 * @return array<string, mixed>
 */
function evek_reject_undersized_images(array $file): array
{
	// Something upstream already rejected it; its message is the useful one.
	if (!empty($file['error'])) {
		return $file;
	}

	if (strpos((string) ($file['type'] ?? ''), 'image/') !== 0) {
		return $file;
	}

	$path = (string) ($file['tmp_name'] ?? '');

	if ($path === '' || !is_readable($path)) {
		return $file;
	}

	// Anything without pixel dimensions to read - an SVG, or a format this
	// PHP build does not know - passes through rather than being blocked on
	// the strength of a measurement we could not take.
	$size = function_exists('wp_getimagesize') ? wp_getimagesize($path) : @getimagesize($path);

	if (!is_array($size) || empty($size[0]) || empty($size[1])) {
		return $file;
	}

	$width  = (int) $size[0];
	$height = (int) $size[1];
	$min    = evek_min_upload_dimensions();

	if ($width >= $min['width'] && $height >= $min['height']) {
		return $file;
	}

	$file['error'] = sprintf(
		/* translators: 1: file name, 2: uploaded width, 3: uploaded height, 4: minimum width, 5: minimum height */
		__(
			'%1$s is %2$d × %3$d pixels, which is too small. Images must be at least %4$d × %5$d pixels. Please upload a larger version.',
			'evek'
		),
		$file['name'] ?? __('This image', 'evek'),
		$width,
		$height,
		$min['width'],
		$min['height']
	);

	return $file;
}

// Disabled for now so small images like logos can be uploaded. Uncomment to
// turn the minimum size check back on.
// add_filter('wp_handle_upload_prefilter', 'evek_reject_undersized_images');
