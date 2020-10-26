<?php

namespace wppunk\PluginRename;

class Wizard {

	private $response = [];

	private $name_question        = 'Plugin name (Example: Plugin Name)';
	private $version_question     = 'Version (Default: 1.0.0)';
	private $additional_questions = [
		'Plugin url (Example: https://gitub.com/wppunk/WPPlugin. Default: \'\')',
		'Author (Example: wppunk. Default: \'\')',
		'Author url (Example: https://gitub.com/wppunk/. Default: \'\')',
	];

	public function run() {
		$name = '';
		while ( empty( $name ) ) {
			$name = $this->ask( $this->name_question );
		}
		$verison              = $this->ask( $this->version_question );
		$verison              = ! empty( $verison ) ? $verison : '1.0.0';
		$additional_responses = [];
		foreach ( $this->additional_questions as $question ) {
			$additional_responses[] = $this->ask( $question );
		}

		return array_merge(
			[
				$name,
				$verison,
			],
			$additional_responses
		);
	}

	private function ask( $value ) {
		echo "\033[0;32m$value:\033[0m ";

		return $this->sanitize( fgets( STDIN ) );
	}

	private function sanitize( $string ) {
		return trim(
			preg_replace(
				'/\s\s+/',
				' ',
				filter_var( $string, FILTER_SANITIZE_STRING )
			)
		);
	}

}
