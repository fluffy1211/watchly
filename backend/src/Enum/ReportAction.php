<?php

namespace App\Enum;

/**
 * What an admin does with a Report.
 *
 * - Keep:   dismiss the report, leave the reported content in place.
 * - Delete: remove the reported content; the DB cascade clears its reports.
 */
enum ReportAction: string
{
    case Keep = 'keep';
    case Delete = 'delete';
}
