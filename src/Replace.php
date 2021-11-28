<?php

namespace wppunk\PluginRename;

class Replace {

	private $dir;
	private $name;
	private $slug;
	private $plugin_url;
	private $version;
	private $author;
	private $author_url;
	private $plugin_slug = [];
	private $replacements = [];

	public function __construct( $dir, $plugin_name, $plugin_slug, $version, $plugin_url, $author, $author_url ) {
		$this->dir        = $dir;
		$this->name       = $plugin_name;
		$this->slug       = $plugin_slug;
		$this->version    = $version;
		$this->plugin_url = $plugin_url;
		$this->author     = $author;
		$this->author_url = $author_url;
	}

	public function run() {
		$this->create_variables();
		$this->replace();
	}

	private function replace() {
		$files = $this->get_files();
		foreach ( $files as $relative_path => $full_path ) {
			$content = $this->replace_content( file_get_contents( $full_path ) );
			file_put_contents( $full_path, $content );

			$this->replace_file_name( $relative_path, $full_path );
			echo '.';
		}
		$count = count( $files );
		echo sprintf( ' %s/%s', $count, $count ) . PHP_EOL;
	}

	private function remove_empty_lines( $content ) {
		return preg_replace( '/ (Author(| URI):|@link|@author)\s+\*/is', '', $content );
	}

	private function create_variables() {
		$this->get_plugin_slug( $this->slug );
		$this->replacements = [
			'{PLUGIN_NAME}' => $this->name,
			'{VERSION}'     => $this->version,
			'{URL}'         => $this->plugin_url,
			'{AUTHOR}'      => $this->author,
			'{AUTHOR_URL}'  => $this->author_url,
		];
	}

	private function get_files() {
		$files  = [];
		$finder = new \Symfony\Component\Finder\Finder();
		$finder
			->files()
			->ignoreDotFiles( false )
			->in( $this->dir )
			->notPath( [ '.phpcs.cache', '.idea', 'node_modules', 'vendor', 'vendor-bin' ] )
			->contains( '/{URL}|{PLUGIN_NAME}|{VERSION}|{AUTHOR}|{AUTHOR_URI}|[pP](lugin|LUGIN)[-_ ]{0,1}[sS](lug|LUG)/is' );

		foreach ( $finder as $file ) {
			$files[ $file->getRelativePathname() ] = $file->getRealPath();
		}

		return $files;
	}

	private function get_plugin_slug( $plugin_slug ) {
		$default = ucwords(
			preg_replace(
				'/[-_]/',
				' ',
				preg_replace(
					'!\s+!',
					' ',
					preg_replace(
						'/(?<!^)[A-Z]/',
						' $0',
						strtoupper( $plugin_slug ) === $plugin_slug
							? strtolower( $plugin_slug )
							: $plugin_slug
					)
				)
			)
		);

		$this->plugin_slug = [
			'plugin_slug' => preg_replace( '/[ _-]/', '_', strtolower( $default ) ),
			'plugin-slug' => preg_replace( '/[ _]/', '-', strtolower( $default ) ),
			'PluginSlug'  => preg_replace( '/[-_ ]/', '', $default ),
			'PLUGIN_SLUG' => preg_replace( '/[- ]/', '_', strtoupper( $default ) ),
		];
	}

	private function replace_plugin_name( $file ) {
		foreach ( $this->plugin_slug as $search => $replacement ) {
			$file = preg_replace(
				sprintf( '/%s/', $search ),
				$replacement,
				$file
			);
		}

		return $file;
	}

	private function replace_file_name( $relative_path, $full_path ) {
		$path = $this->replace_plugin_name( $relative_path );
		if ( $path === $relative_path ) {
			return;
		}
		$dir = $this->dir . dirname( $path );
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755, true );
		}
		rename( $full_path, $this->dir . $path );

		$previous_dir = dirname( $full_path );
		if ( ! ( new \FilesystemIterator( $previous_dir ) )->valid() ) {
			rmdir( $previous_dir );
		}
	}

	private function replace_content( $content ) {
		foreach ( $this->replacements as $search => $replacement ) {
			$content = preg_replace(
				sprintf( '/%s/', $search ),
				$replacement,
				$content
			);
		}
		$content = $this->replace_plugin_name( $content );

		return $this->remove_empty_lines( $content );
	}

}
