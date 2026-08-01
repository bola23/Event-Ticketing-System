<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case TicketIssued = 'ticket_issued';
    case CheckedIn = 'checked_in';
    case Cancelled = 'cancelled';
}
