<?php

namespace App\Exception;

/** A user tried to report their own content. Maps to 403. */
class SelfReportException extends ReportException
{
}
