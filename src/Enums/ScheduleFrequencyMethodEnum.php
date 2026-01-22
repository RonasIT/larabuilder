<?php

namespace RonasIT\Larabuilder\Enums;

enum ScheduleFrequencyMethodEnum: string
{
    case Cron = 'cron';

    case EverySecond = 'everySecond';
    case EveryTwoSeconds = 'everyTwoSeconds';
    case EveryFiveSeconds = 'everyFiveSeconds';
    case EveryTenSeconds = 'everyTenSeconds';
    case EveryFifteenSeconds = 'everyFifteenSeconds';
    case EveryTwentySeconds = 'everyTwentySeconds';
    case EveryThirtySeconds = 'everyThirtySeconds';

    case EveryMinute = 'everyMinute';
    case EveryTwoMinutes = 'everyTwoMinutes';
    case EveryThreeMinutes = 'everyThreeMinutes';
    case EveryFourMinutes = 'everyFourMinutes';
    case EveryFiveMinutes = 'everyFiveMinutes';
    case EveryTenMinutes = 'everyTenMinutes';
    case EveryFifteenMinutes = 'everyFifteenMinutes';
    case EveryThirtyMinutes = 'everyThirtyMinutes';

    case Hourly = 'hourly';
    case HourlyAt = 'hourlyAt';
    case EveryOddHour = 'everyOddHour';
    case EveryTwoHours = 'everyTwoHours';
    case EveryThreeHours = 'everyThreeHours';
    case EveryFourHours = 'everyFourHours';
    case EverySixHours = 'everySixHours';

    case Daily = 'daily';
    case DailyAt = 'dailyAt';
    case TwiceDaily = 'twiceDaily';
    case TwiceDailyAt = 'twiceDailyAt';
    case DaysOfMonth = 'daysOfMonth';

    case Weekly = 'weekly';
    case WeeklyOn = 'weeklyOn';

    case Monthly = 'monthly';
    case MonthlyOn = 'monthlyOn';
    case TwiceMonthly = 'twiceMonthly';
    case LastDayOfMonth = 'lastDayOfMonth';

    case Quarterly = 'quarterly';
    case QuarterlyOn = 'quarterlyOn';

    case Yearly = 'yearly';
    case YearlyOn = 'yearlyOn';

    case Timezone = 'timezone';
}
