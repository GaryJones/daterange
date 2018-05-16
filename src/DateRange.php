<?php
/**
 * Gamajo Date Range.
 *
 * Display a range of dates, with consolidated time parts.
 *
 * @package   Gamajo\DateRange
 * @author    Gary Jones
 * @license   MIT
 * @link      https://gamajo.com
 * @copyright 2018 Gary Jones, Gamajo
 */

declare(strict_types=1);

namespace Gamajo\DateRange;

use DateTimeInterface;

// @codingStandardsChangeSetting ObjectCalisthenics.Metrics.MethodPerClassLimit maxCount 11
// @codingStandardsChangeSetting ObjectCalisthenics.Files.ClassTraitAndInterfaceLength maxLength 250

/**
 * Class DateRange.
 *
 * @package Gamajo\DateRange
 */
class DateRange
{
    /**
     * Start date.
     *
     * @var DateTimeInterface
     */
    protected $startDate;

    /**
     * End Date
     *
     * @var DateTimeInterface
     */
    protected $endDate;

    /**
     * Separator between the consolidated start and end dates.
     *
     * Can be changed with setSeparator() method.
     *
     * @var string
     */
    protected $separator = ' – ';

    /**
     * Removable delimiters once time parts have been consolidated.
     *
     * Can be changed with setRemovableDelimiters() method.
     *
     * @var string
     */
    protected $removableDelimiters = '/-.';

    /**
     * Time part character sets.
     *
     * Treated like aliases i.e. a month part might be required as March, Mar, 3 or 03,
     * but they are all treated as a month.
     *
     * As per https://secure.php.net/manual/en/function.date.php.
     */
    protected const CHAR_SETS = [
        ['o', 'Y', 'y'], // Year
        ['F', 'm', 'M', 'n'], // Month
        ['d', 'j'], // Date
        ['g', 'G', 'h', 'H'], // Hour
        ['i'], // Minute
        ['s'], // Second
    ];

    /**
     * DateRange constructor.
     *
     * @since 1.0.0
     *
     * @param DateTimeInterface $startDate Start date.
     * @param DateTimeInterface $endDate   End date.
     */
    public function __construct(DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Change the separator between the start and end date.
     *
     * @since 1.0.0
     *
     * @param string $separator Separator.
     */
    public function changeSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    /**
     * Change the delimiters that should be trimmed from the start date format ends.
     *
     * Avoids a format of 'd/M/Y' having a starting format of `d//` when month and year are consolidated.
     *
     * @since 1.0.0
     *
     * @param string $removableDelimiters [description]
     */
    public function changeRemovableDelimiters(string $removableDelimiters): void
    {
        $this->removableDelimiters = $removableDelimiters;
    }

    /**
     * Format the date range.
     *
     * Time parts are consolidated, starting with the largest time part.
     * i.e. start and end dates with the same year would not show the year for the start date:
     *
     * 14th May - 5th June 2018
     *
     * If the year and the month are the same, then neither yar or month are shown for the start date:
     *
     * 14th - 15th May 2018.
     *
     * This continues for date (day of the month), hours, minutes and seconds.
     *
     * The output looks best when the format has time parts in increasing order of size.
     * It will technically work in other orders though:
     *
     * 14:00  23rd – 2018 14:00 Jun 24th 2018
     *
     * @since 1.0.0
     *
     * @param string $endDateFormat Date format as per https://secure.php.net/manual/en/function.date.php
     *
     * @return string Date range output.
     */
    public function format(string $endDateFormat): string
    {
        // Formatted dates are the same, so return single date.
        if ($this->formattedDatesMatch($endDateFormat)) {
            return $this->endDate->format($endDateFormat);
        }

        $startDateFormat = $this->getStartDateFormat($endDateFormat);

        return $this->startDate->format($startDateFormat) .
               $this->separator .
               $this->endDate->format($endDateFormat);
    }

    /**
     * Get the consolidated start date format.
     *
     * This is based on removing duplicated time parts between the start and end dates.
     *
     * @since 1.0.0
     *
     * @param string $endDateFormat End date format.
     *
     * @return string Start date format.
     */
    protected function getStartDateFormat(string $endDateFormat): string
    {
        $startDateFormat    = trim($endDateFormat);
        $timePartCharacters = str_split($startDateFormat);

        $sortedTimePartCharacters = $this->sortTimePartCharacters($timePartCharacters);

        foreach ($sortedTimePartCharacters as $timePartCharacter) {
            if ($this->timePartValueInDatesIsInconsistent($timePartCharacter)) {
                break;
            }

            $startDateFormat =  $this->removeTimePartCharacterFromFormat($timePartCharacter, $startDateFormat);
        }

        return trim(trim($startDateFormat, $this->removableDelimiters));
    }

    // @codingStandardsChangeSetting ObjectCalisthenics.Metrics.MaxNestingLevel maxNestingLevel 3
    /**
     * Sort timePartCharacters by the size of the time part.
     *
     * i.e. all the year characters first, then month characters etc.
     *
     * @param array $timePartCharacters
     *
     * @return array
     */
    protected function sortTimePartCharacters(array $timePartCharacters)
    {
        $sorted = [];

        foreach (self::CHAR_SETS as $charset) {
            foreach ($timePartCharacters as $timePartCharacter) {
                if (in_array($timePartCharacter, $charset)) {
                    $sorted[] = $timePartCharacter;
                }
            }
        }

        return array_unique($sorted);
    }
    // @codingStandardsChangeSetting ObjectCalisthenics.Metrics.MaxNestingLevel maxNestingLevel 2

    /**
     * Remove a time part character and its aliases from a format.
     *
     * @param string $timePartCharacter Time part character.
     * @param string $format            Date format.
     *
     * @return string
     */
    protected function removeTimePartCharacterFromFormat(string $timePartCharacter, string $format): string
    {
        $timePartAliases = $this->getTimePartAliases($timePartCharacter);

        return $this->removeTimePartAliasesFromFormat($timePartAliases, $format);
    }

    /**
     * @param array  $timePartAliases Characters that match the time part.
     * @param string $format          Date format.
     *
     * @return string Updated date format.
     */
    protected function removeTimePartAliasesFromFormat(array $timePartAliases, string $format): string
    {
        return str_replace($timePartAliases, '', $format);
    }

    /**
     * @param string $timePartCharacter Time part character from format.
     *
     * @return array
     */
    protected function getTimePartAliases(string $timePartCharacter): array
    {
        foreach (self::CHAR_SETS as $timePartAliases) {
            if (in_array($timePartCharacter, $timePartAliases)) {
                $return = $timePartAliases;
            }
        }

        return $return;
    }

    /**
     * Check to see if dates match for the desired format.
     *
     * @param string $format
     *
     * @return bool
     */
    protected function formattedDatesMatch(string $format): bool
    {
        return $this->startDate->format($format) === $this->endDate->format($format);
    }

    /**
     * Check if time part value in dates is inconsistent (i.e. Feb and Mar)
     *
     * @param string $timePartCharacter Time part character to check.
     *
     * @return bool
     */
    protected function timePartValueInDatesIsInconsistent(string $timePartCharacter): bool
    {
        return ! $this->formattedDatesMatch($timePartCharacter);
    }
}

// @codingStandardsChangeSetting ObjectCalisthenics.Metrics.MethodPerClassLimit maxCount 10
// @codingStandardsChangeSetting ObjectCalisthenics.Files.ClassTraitAndInterfaceLength maxLength 200
