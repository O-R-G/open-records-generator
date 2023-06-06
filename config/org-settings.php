<?php
/* 
number of images
default wisiwyg editor mode
records order type
*/

$org_settings = array(
	'max_uploads' => array(
		'input_type' => 'select',
		'display' => 'maximum # of uploads',
		'name' => 'max_uploads',
		'default' => 5,
		'options' => array()
	),
	'default_editor_mode' => array(
		'input_type' => 'select',
		'display' => 'default editor mode',
		'name' => 'default_editor_mode',
		'default' => 'rich_text',
		'options' => array(
			array(
				'display' => 'Rich text editor',
				'value'   => 'rich_text'
			),
			array(
				'display' => 'HTML editor',
				'value'   => 'html'
			)
		)
	),
	'order_type' => array(
		'input_type' => 'select',
		'display' => 'order type',
		'name' => 'order_type',
		'default' => 'default',
		'options' => array(
			array(
				'display' => 'Default',
				'value'   => 'default'
			),
			array(
				'display' => 'Chronological',
				'value'   => 'chronological'
			),
			array(
				'display' => 'Alphabetical',
				'value'   => 'alphabetical'
			)
		)
	)
);
for($i = 5; $i <= 50; $i+= 5)
{
	$org_settings['max_uploads']['options'][] = array(
		'display' => $i,
		'value'   => $i
	);
}