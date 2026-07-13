<?php

namespace App\Enums;

enum TicketCategory: string
{
    case SettlementObjection      = 'settlement_objection';
    case AdvanceRequest           = 'advance_request';
    case FuelRequest              = 'fuel_request';
    case TrafficViolationRequest  = 'traffic_violation_request';
    case PenaltyRequest           = 'penalty_request';
    case OtherFinancialRequest    = 'other_financial_request';
    case TechnicalSupport         = 'technical_support';
    case PayrollInquiry           = 'payroll_inquiry';
    case GeneralInquiry           = 'general_inquiry';

    /** Financial categories require a FinancialRequest row on ticket creation. */
    public function isFinancial(): bool
    {
        return in_array($this, [
            self::AdvanceRequest,
            self::FuelRequest,
            self::TrafficViolationRequest,
            self::PenaltyRequest,
            self::OtherFinancialRequest,
        ]);
    }

    public function label(): string
    {
        return match($this) {
            self::SettlementObjection     => 'اعتراض على التسوية',
            self::AdvanceRequest          => 'طلب سلفة',
            self::FuelRequest             => 'طلب وقود',
            self::TrafficViolationRequest => 'مخالفة مرورية',
            self::PenaltyRequest          => 'غرامة داخلية',
            self::OtherFinancialRequest   => 'طلب مالي آخر',
            self::TechnicalSupport        => 'مشكلة تقنية',
            self::PayrollInquiry          => 'استفسار عن الراتب',
            self::GeneralInquiry          => 'استفسار عام',
        };
    }

    /**
     * Canonical mapping from TicketCategory → settlement deduction_type.
     * This is the single source of truth used by the approval form, import, and labels.
     * Returns null for categories that have no fixed type (admin must choose).
     */
    public function defaultDeductionType(): ?string
    {
        return match($this) {
            self::FuelRequest             => 'fuel',
            self::AdvanceRequest          => 'advance',
            self::TrafficViolationRequest => 'traffic_violation',
            self::PenaltyRequest          => 'company_penalty',
            default                       => null,  // OtherFinancialRequest: admin chooses
        };
    }

    /**
     * Categories that delegates are allowed to submit via the portal.
     * PenaltyRequest is intentionally excluded (admin-internal only).
     */
    public function isPortalSubmittable(): bool
    {
        return in_array($this, [
            self::SettlementObjection,
            self::AdvanceRequest,
            self::FuelRequest,
            self::TrafficViolationRequest,
            self::OtherFinancialRequest,
            self::TechnicalSupport,
            self::PayrollInquiry,
            self::GeneralInquiry,
        ]);
    }

    /** Bootstrap badge class for the admin queue UI. */
    public function badgeClass(): string
    {
        return match($this) {
            self::SettlementObjection,
            self::AdvanceRequest,
            self::FuelRequest,
            self::TrafficViolationRequest,
            self::PenaltyRequest,
            self::OtherFinancialRequest => 'badge-warning',
            self::TechnicalSupport      => 'badge-secondary',
            self::PayrollInquiry,
            self::GeneralInquiry        => 'badge-info',
        };
    }
}
