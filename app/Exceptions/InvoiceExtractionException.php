<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Extraction failure whose message is safe to show to the user (Greek, no
 * provider/internal details, no invoice content). Any other exception type is
 * replaced by a generic message before it reaches the UI or API.
 */
class InvoiceExtractionException extends RuntimeException
{
}
