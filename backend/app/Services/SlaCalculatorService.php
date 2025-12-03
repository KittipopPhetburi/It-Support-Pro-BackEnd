<?php

namespace App\Services;

use App\Models\BusinessHour;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlaCalculatorService
{
    protected Collection $businessHours;
    protected Collection $holidays;

    public function __construct()
    {
        $this->loadBusinessHours();
        $this->loadHolidays();
    }

    /**
     * Load business hours from database
     */
    protected function loadBusinessHours(): void
    {
        $this->businessHours = BusinessHour::where('is_working_day', true)->get();
    }

    /**
     * Load holidays from database
     */
    protected function loadHolidays(): void
    {
        $this->holidays = Holiday::all();
    }

    /**
     * Check if a specific date is a holiday
     */
    public function isHoliday(Carbon $date): bool
    {
        foreach ($this->holidays as $holiday) {
            $holidayDate = Carbon::parse($holiday->date);
            
            if ($holiday->recurring) {
                // Check month and day only for recurring holidays
                if ($date->month === $holidayDate->month && $date->day === $holidayDate->day) {
                    return true;
                }
            } else {
                // Check exact date for non-recurring holidays
                if ($date->isSameDay($holidayDate)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get business hours for a specific day of week
     * @param int $dayOfWeek 0 = Sunday, 1 = Monday, etc.
     */
    public function getBusinessHoursForDay(int $dayOfWeek): ?BusinessHour
    {
        return $this->businessHours->firstWhere('day_of_week', $dayOfWeek);
    }

    /**
     * Check if a specific datetime is within business hours
     */
    public function isWithinBusinessHours(Carbon $dateTime): bool
    {
        // Check if it's a holiday
        if ($this->isHoliday($dateTime)) {
            return false;
        }

        // Get business hours for this day
        $businessHour = $this->getBusinessHoursForDay($dateTime->dayOfWeek);
        
        if (!$businessHour || !$businessHour->is_working_day) {
            return false;
        }

        $startTime = Carbon::parse($businessHour->start_time);
        $endTime = Carbon::parse($businessHour->end_time);

        $currentTime = $dateTime->copy();
        $dayStart = $currentTime->copy()->setTimeFrom($startTime);
        $dayEnd = $currentTime->copy()->setTimeFrom($endTime);

        return $currentTime->between($dayStart, $dayEnd);
    }

    /**
     * Calculate business minutes between two datetimes
     * Only counts time during business hours
     */
    public function calculateBusinessMinutes(Carbon $start, Carbon $end): int
    {
        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $totalMinutes = 0;
        $current = $start->copy();

        while ($current->lessThan($end)) {
            // Check if it's a holiday
            if ($this->isHoliday($current)) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Get business hours for this day
            $businessHour = $this->getBusinessHoursForDay($current->dayOfWeek);

            if (!$businessHour || !$businessHour->is_working_day) {
                // Not a working day, skip to next day
                $current->addDay()->startOfDay();
                continue;
            }

            // Parse business hours for this day
            $dayStart = $current->copy()->setTimeFromTimeString($businessHour->start_time);
            $dayEnd = $current->copy()->setTimeFromTimeString($businessHour->end_time);

            // If current time is before business hours start
            if ($current->lessThan($dayStart)) {
                $current = $dayStart->copy();
            }

            // If current time is after business hours end
            if ($current->greaterThanOrEqualTo($dayEnd)) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Calculate the effective end for this day
            $effectiveEnd = $end->lessThan($dayEnd) ? $end->copy() : $dayEnd->copy();

            // If effective end is before current (could happen if end is before business hours start)
            if ($effectiveEnd->lessThanOrEqualTo($current)) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Add the minutes for this working period
            $minutesInPeriod = $current->diffInMinutes($effectiveEnd);
            $totalMinutes += $minutesInPeriod;

            // Move to next day
            $current = $dayEnd->copy()->addDay()->startOfDay();
        }

        return $totalMinutes;
    }

    /**
     * Calculate SLA deadline based on business hours
     * @param Carbon $startTime The start time
     * @param int $slaMinutes The SLA time in minutes
     * @return Carbon The deadline
     */
    public function calculateSlaDeadline(Carbon $startTime, int $slaMinutes): Carbon
    {
        $remainingMinutes = $slaMinutes;
        $current = $startTime->copy();
        $maxIterations = 365; // Safety limit
        $iterations = 0;

        while ($remainingMinutes > 0 && $iterations < $maxIterations) {
            $iterations++;

            // Check if it's a holiday
            if ($this->isHoliday($current)) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Get business hours for this day
            $businessHour = $this->getBusinessHoursForDay($current->dayOfWeek);

            if (!$businessHour || !$businessHour->is_working_day) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Parse business hours
            $dayStart = $current->copy()->setTimeFromTimeString($businessHour->start_time);
            $dayEnd = $current->copy()->setTimeFromTimeString($businessHour->end_time);

            // If current time is before business hours start
            if ($current->lessThan($dayStart)) {
                $current = $dayStart->copy();
            }

            // If current time is after business hours end
            if ($current->greaterThanOrEqualTo($dayEnd)) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Calculate available minutes in this working period
            $availableMinutes = $current->diffInMinutes($dayEnd);

            if ($availableMinutes >= $remainingMinutes) {
                // Deadline is within this working period
                return $current->addMinutes($remainingMinutes);
            } else {
                // Use all available minutes and continue to next day
                $remainingMinutes -= $availableMinutes;
                $current = $dayEnd->copy()->addDay()->startOfDay();
            }
        }

        // Fallback: return start time plus SLA minutes if something goes wrong
        return $startTime->copy()->addMinutes($slaMinutes);
    }

    /**
     * Calculate remaining SLA time in minutes (business hours only)
     * @param Carbon $startTime When the SLA started
     * @param int $slaMinutes Total SLA time in minutes
     * @param Carbon|null $currentTime Current time (defaults to now)
     * @return int Remaining minutes (negative if exceeded)
     */
    public function calculateRemainingSlaMinutes(Carbon $startTime, int $slaMinutes, ?Carbon $currentTime = null): int
    {
        $currentTime = $currentTime ?? Carbon::now();
        $elapsedBusinessMinutes = $this->calculateBusinessMinutes($startTime, $currentTime);
        return $slaMinutes - $elapsedBusinessMinutes;
    }

    /**
     * Get SLA status with details
     */
    public function getSlaStatus(Carbon $startTime, int $slaMinutes, ?Carbon $currentTime = null): array
    {
        $currentTime = $currentTime ?? Carbon::now();
        $elapsedBusinessMinutes = $this->calculateBusinessMinutes($startTime, $currentTime);
        $remainingMinutes = $slaMinutes - $elapsedBusinessMinutes;
        $deadline = $this->calculateSlaDeadline($startTime, $slaMinutes);
        
        $percentageUsed = $slaMinutes > 0 ? round(($elapsedBusinessMinutes / $slaMinutes) * 100, 2) : 0;

        $status = 'on_track';
        if ($remainingMinutes <= 0) {
            $status = 'breached';
        } elseif ($percentageUsed >= 75) {
            $status = 'at_risk';
        } elseif ($percentageUsed >= 50) {
            $status = 'warning';
        }

        return [
            'elapsed_business_minutes' => $elapsedBusinessMinutes,
            'remaining_minutes' => $remainingMinutes,
            'total_sla_minutes' => $slaMinutes,
            'percentage_used' => $percentageUsed,
            'deadline' => $deadline->toIso8601String(),
            'status' => $status,
            'is_breached' => $remainingMinutes <= 0,
            'is_within_business_hours' => $this->isWithinBusinessHours($currentTime),
        ];
    }

    /**
     * Format minutes to human readable string
     */
    public function formatMinutes(int $minutes): string
    {
        $absMinutes = abs($minutes);
        $sign = $minutes < 0 ? '-' : '';
        
        if ($absMinutes < 60) {
            return $sign . $absMinutes . ' นาที';
        }
        
        $hours = floor($absMinutes / 60);
        $mins = $absMinutes % 60;
        
        if ($hours < 24) {
            return $sign . $hours . ' ชั่วโมง ' . ($mins > 0 ? $mins . ' นาที' : '');
        }
        
        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        
        $result = $sign . $days . ' วัน';
        if ($remainingHours > 0) {
            $result .= ' ' . $remainingHours . ' ชั่วโมง';
        }
        if ($mins > 0) {
            $result .= ' ' . $mins . ' นาที';
        }
        
        return trim($result);
    }
}
