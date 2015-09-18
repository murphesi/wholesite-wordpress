<?php

namespace WholeSite\EmailTemplates;

/**
 * Settings for Email Templates
 * Define keys that will be used to map a confirmation key to an email confirmation template
 */

class Mapping {
	static $KEY_TO_TEMPLATE_MAP = array(
			'default' => 'Email Template 1 (Default)',
			'key2' => 'Email Template 2',
			'key3' => 'Email Template 3'
		);
}