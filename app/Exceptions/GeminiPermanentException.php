<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kegagalan Gemini yang TIDAK akan sembuh dengan mengulang:
 * API key salah, API belum diaktifkan, project tanpa kuota, permintaan tak valid.
 * Job harus langsung menyerah supaya guru cepat dapat pesan errornya.
 */
class GeminiPermanentException extends RuntimeException {}
