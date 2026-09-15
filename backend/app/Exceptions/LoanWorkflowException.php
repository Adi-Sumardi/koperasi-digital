<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan alur bisnis pinjaman (mis. jenjang persetujuan, status tidak valid).
 */
class LoanWorkflowException extends RuntimeException {}
