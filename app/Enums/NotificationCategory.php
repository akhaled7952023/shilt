<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case SettlementPublished         = 'settlement_published';
    case SettlementViewed            = 'settlement_viewed';
    case TicketNew                   = 'ticket_new';
    case TicketReply                 = 'ticket_reply';
    case TicketClosed                = 'ticket_closed';
    case TicketReopened              = 'ticket_reopened';
    case FinancialRequestApproved    = 'financial_request_approved';
    case FinancialRequestRejected    = 'financial_request_rejected';
    case IqamaExpiring               = 'iqama_expiring';
    case PassportExpiring            = 'passport_expiring';
    case DrivingLicenseExpiring      = 'driving_license_expiring';
    case VehicleRegistrationExpiring = 'vehicle_registration_expiring';
    case VehicleInsuranceExpiring    = 'vehicle_insurance_expiring';

    /** Categories that are sent to delegates (portal recipient). */
    public function isForDelegate(): bool
    {
        return in_array($this, [
            self::SettlementPublished,
            self::TicketReply,
            self::TicketClosed,
            self::FinancialRequestApproved,
            self::FinancialRequestRejected,
            self::IqamaExpiring,
            self::PassportExpiring,
            self::DrivingLicenseExpiring,
            self::VehicleRegistrationExpiring,
            self::VehicleInsuranceExpiring,
        ]);
    }

    /** Categories that are sent to admins (admin recipient). */
    public function isForAdmin(): bool
    {
        return in_array($this, [
            self::TicketNew,
            self::TicketReopened,
            self::SettlementViewed,
        ]);
    }
}
