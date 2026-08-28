<?php

namespace App\Exception;

/**
 * Base for every way ReportService::file() can refuse a report.
 * ReportController maps each subclass to an HTTP status.
 */
abstract class ReportException extends \RuntimeException
{
}
