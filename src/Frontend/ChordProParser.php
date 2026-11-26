<?php
/**
 * ChordPro parser.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Parses ChordPro text into directives and sections.
 */
final class ChordProParser {

	/**
	 * Metadata directives.
	 *
	 * @var array<int, string>
	 */
	private const META_DIRECTIVES = array( 'album', 'artist', 'capo', 'composer', 'key', 'subtitle', 'tempo', 'time', 'title', 'year' );

	/**
	 * Parse ChordPro content.
	 *
	 * @param string $content ChordPro text.
	 * @return array{
	 *     directives: array<string, string>,
	 *     sections: array<int, array{type:string,label:string,lines:array<int, array<string, mixed>>}>
	 * }
	 */
	public function parse( string $content ): array {
		$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
		$lines   = explode( "\n", $content );

		$directives = array_fill_keys( self::META_DIRECTIVES, '' );
		$sections   = array();
		$type       = 'plain';
		$label      = '';
		$section    = array();
		$monospace  = false;

		foreach ( $lines as $line ) {
			$line           = self::clean_line_wrappers( (string) $line );
			$trimmed        = trim( $line );
			$directive_line = self::strip_pre_wrappers( $trimmed );
			$directive      = self::parse_directive( $directive_line );

			if ( $monospace ) {
				if ( null !== $directive && 'end_of_monospace' === $directive['name'] ) {
					self::close_section( $sections, $type, $label, $section );
					$monospace = false;
					$type      = 'plain';
					continue;
				}

				$section[] = array(
					'type' => 'monospace',
					'text' => $line,
				);
				continue;
			}

			if ( null !== $directive ) {
				$name  = $directive['name'];
				$value = $directive['value'];

				if ( 'meta' === $name ) {
					$meta = self::split_meta( $value );

					if ( null !== $meta ) {
						$directives[ $meta['name'] ] = $meta['value'];
					}

					continue;
				}

				if ( in_array( $name, self::META_DIRECTIVES, true ) ) {
					$directives[ $name ] = $value;
					continue;
				}

				if ( self::is_comment_directive( $name ) ) {
					$section[] = array(
						'type' => 'comment',
						'text' => $value,
					);
					continue;
				}

				if ( 'start_of_monospace' === $name ) {
					self::close_section( $sections, $type, $label, $section );
					$type      = 'monospace';
					$label     = $value;
					$monospace = true;
					continue;
				}

				if ( 'start_of_verse' === $name || 'start_of_chorus' === $name ) {
					self::close_section( $sections, $type, $label, $section );
					$type  = 'start_of_verse' === $name ? 'verse' : 'chorus';
					$label = $value;
					continue;
				}

				if ( 'end_of_verse' === $name || 'end_of_chorus' === $name ) {
					$expected = 'end_of_verse' === $name ? 'verse' : 'chorus';

					if ( $expected === $type ) {
						self::close_section( $sections, $type, $label, $section );
						$type = 'plain';
					}

					continue;
				}

				continue;
			}

			if ( '' === $trimmed ) {
				$section[] = array( 'type' => 'blank' );
				continue;
			}

			if ( str_starts_with( $trimmed, '#' ) || in_array( strtolower( $trimmed ), array( '<br>', '<br/>', '<br />' ), true ) ) {
				continue;
			}

			$open  = substr_count( $line, '[' );
			$close = substr_count( $line, ']' );

			if ( $open !== $close ) {
				$section[] = array(
					'type' => 'error',
					'raw'  => $line,
				);
				continue;
			}

			if ( 0 === $open ) {
				$section[] = array(
					'type'     => 'lyric',
					'segments' => array(
						array(
							'chord'  => '',
							'lyrics' => $line,
						),
					),
				);
				continue;
			}

			$segments  = self::parse_segments( $line );
			$section[] = array(
				'type'     => 'lyric',
				'segments' => $segments,
			);
		}

		self::close_section( $sections, $type, $label, $section );

		return array(
			'directives' => $directives,
			'sections'   => $sections,
		);
	}

	/**
	 * Parse a line containing chords into segments.
	 *
	 * @param string $line Line.
	 * @return array<int, array{chord:string,lyrics:string}>
	 */
	private static function parse_segments( string $line ): array {
		$parts    = explode( '[', $line );
		$segments = array();
		$intro    = array_shift( $parts );

		if ( '' !== $intro ) {
			$segments[] = array(
				'chord'  => '',
				'lyrics' => $intro,
			);
		}

		foreach ( $parts as $part ) {
			$split  = explode( ']', $part, 2 );
			$chord  = trim( $split[0] );
			$lyrics = $split[1] ?? '';

			if ( '' === $chord && '' === $lyrics ) {
				continue;
			}

			$segments[] = array(
				'chord'  => $chord,
				'lyrics' => $lyrics,
			);
		}

		return $segments;
	}

	/**
	 * Parse a directive line.
	 *
	 * @param string $line Trimmed line.
	 * @return array{name:string,value:string}|null
	 */
	private static function parse_directive( string $line ): ?array {
		if ( ! str_starts_with( $line, '{' ) || ! str_ends_with( $line, '}' ) ) {
			return null;
		}

		$inner = trim( substr( $line, 1, -1 ) );

		if ( '' === $inner ) {
			return null;
		}

		$parts = explode( ':', $inner, 2 );
		$name  = strtolower( str_replace( array( ' ', '-' ), '_', trim( $parts[0] ) ) );
		$value = isset( $parts[1] ) ? trim( $parts[1] ) : '';
		$name  = self::normalize_directive_name( $name );

		if ( '' === $name ) {
			return null;
		}

		return array(
			'name'  => $name,
			'value' => $value,
		);
	}

	/**
	 * Normalize directive aliases.
	 *
	 * @param string $name Directive name.
	 * @return string
	 */
	private static function normalize_directive_name( string $name ): string {
		$aliases = array(
			't'                => 'title',
			'st'               => 'subtitle',
			'c'                => 'comment',
			'soc'              => 'start_of_chorus',
			'eoc'              => 'end_of_chorus',
			'sov'              => 'start_of_verse',
			'eov'              => 'end_of_verse',
			'som'              => 'start_of_monospace',
			'eom'              => 'end_of_monospace',
			'start_of_tab'     => 'start_of_monospace',
			'end_of_tab'       => 'end_of_monospace',
			'start_of_tabular' => 'start_of_monospace',
			'end_of_tabular'   => 'end_of_monospace',
		);

		return $aliases[ $name ] ?? $name;
	}

	/**
	 * Whether a normalized directive name is a comment directive.
	 *
	 * ChordPress 4.x rendered every directive whose name contains "comment",
	 * so older songs may use names such as {comments: Chorus}. Those lines must
	 * still render as comments instead of being dropped as unknown directives.
	 *
	 * @param string $name Normalized directive name.
	 * @return bool
	 */
	private static function is_comment_directive( string $name ): bool {
		return str_contains( $name, 'comment' );
	}

	/**
	 * Split a {meta: name value} directive.
	 *
	 * @param string $value Directive value.
	 * @return array{name:string,value:string}|null
	 */
	private static function split_meta( string $value ): ?array {
		$parts = preg_split( '/\s+/', trim( $value ), 2 );

		if ( ! is_array( $parts ) || count( $parts ) < 2 ) {
			return null;
		}

		$name = strtolower( $parts[0] );

		if ( ! in_array( $name, self::META_DIRECTIVES, true ) ) {
			return null;
		}

		return array(
			'name'  => $name,
			'value' => $parts[1],
		);
	}

	/**
	 * Close the current section and append it.
	 *
	 * @param array<int, array{type:string,label:string,lines:array<int, array<string, mixed>>}> $sections Sections.
	 * @param string                                                                             $type     Current type.
	 * @param string                                                                             $label    Current label.
	 * @param array<int, array<string, mixed>>                                                   $lines    Current lines.
	 * @return void
	 */
	private static function close_section( array &$sections, string &$type, string &$label, array &$lines ): void {
		if ( array() === $lines && 'plain' === $type ) {
			$label = '';
			return;
		}

		$sections[] = array(
			'type'  => $type,
			'label' => $label,
			'lines' => $lines,
		);

		$lines = array();
		$type  = 'plain';
		$label = '';
	}

	/**
	 * Remove wrapping paragraph tags added by wpautop.
	 *
	 * @param string $line Line.
	 * @return string
	 */
	private static function clean_line_wrappers( string $line ): string {
		$line = preg_replace( '/^\s*<p\b[^>]*>/i', '', $line );
		$line = is_string( $line ) ? $line : '';
		$line = preg_replace( '/<\/p>\s*$/i', '', $line );
		$line = is_string( $line ) ? $line : '';
		$line = preg_replace( '/\s*<br\s*\/?>\s*$/i', '', $line );

		return is_string( $line ) ? $line : '';
	}

	/**
	 * Remove pre wrappers around a directive.
	 *
	 * @param string $line Trimmed line.
	 * @return string
	 */
	private static function strip_pre_wrappers( string $line ): string {
		$line = preg_replace( '/^<pre\b[^>]*>\s*/i', '', $line );
		$line = is_string( $line ) ? $line : '';
		$line = preg_replace( '/\s*<\/pre>\s*$/i', '', $line );

		return is_string( $line ) ? $line : '';
	}
}
