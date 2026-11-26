<?php
/**
 * Migration exception.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Raised when a migration cannot be completed safely.
 */
final class MigrationException extends RuntimeException {}
