<?php
/**
 * Safe HTML handling for ChordPro content.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Applies the inline-lyrics HTML policy.
 */
final class HtmlSanitizer {

	/**
	 * Sanitize inline lyric HTML.
	 *
	 * @param string $html Raw inline HTML.
	 * @return string
	 */
	public function inline( string $html ): string {
		if ( function_exists( 'wp_kses_post' ) && function_exists( 'wp_kses' ) ) {
			if ( function_exists( 'current_user_can' ) && current_user_can( 'unfiltered_html' ) ) {
				return wp_kses( $html, self::allowed_html() );
			}

			return wp_kses_post( $html );
		}

		return self::fallback( $html );
	}

	/**
	 * Return the allowed HTML map for trusted editors.
	 *
	 * Scripts, iframes, objects, embeds, forms and event handlers remain excluded.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private static function allowed_html(): array {
		$allowed = function_exists( 'wp_kses_allowed_html' ) ? wp_kses_allowed_html( 'post' ) : array();

		foreach ( array( 'span', 'div', 'p', 'b', 'strong', 'i', 'em', 'u', 'small', 'code', 'pre' ) as $tag ) {
			if ( isset( $allowed[ $tag ] ) && is_array( $allowed[ $tag ] ) ) {
				$allowed[ $tag ]['style'] = true;
			}
		}

		return $allowed;
	}

	/**
	 * Test fallback used when WordPress functions are unavailable.
	 *
	 * @param string $html Raw HTML.
	 * @return string
	 */
	public static function fallback( string $html ): string {
		$html = preg_replace( '#<(script|iframe|object|embed|form|style|link|meta)\b[^>]*>.*?</\1>#is', '', $html );
		$html = is_string( $html ) ? $html : '';
		$html = preg_replace( '#<(script|iframe|object|embed|form|style|link|meta)\b[^>]*/?>#is', '', $html );
		$html = is_string( $html ) ? $html : '';
		$html = preg_replace( '/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html );
		$html = is_string( $html ) ? $html : '';
		$html = preg_replace( '/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1="#"', $html );

		return is_string( $html ) ? $html : '';
	}
}
