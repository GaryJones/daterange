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
     * End date.
     *
     * @var DateTimeInterface
     */
    protected $endDate;

    /**
     * Start date format.
     *
     * @var string
     */
    protected $startDateFormat;

    /**
     * End Date format.
     *
     * @var string
     */
    protected $endDateFormat;

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
     * If the year and the month are the same, then neither year or month are shown for the start date:
     *
     * 14th - 15th May 2018.
     *
     * This continues for date (day of the month), hours, minutes and seconds.
     *
     * It even works when not in size order:
     *
     * Jun 23rd – 28th 2018
     *
     * Hours, minutes and second time parts NOT supported.
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

        $this->endDateFormat   = trim($endDateFormat);
        $this->startDateFormat = $this->endDateFormat;

        $this->consolidateDateFormats();

        return $this->startDate->format($this->startDateFormat) .
               $this->separator .
               $this->endDate->format($this->endDateFormat);
    }

    /**
     * Consolidated the start and end date formats.
     *
     * This is based on removing duplicated time parts between the start and end dates, depending on the order of
     * different sized time parts.
     *
     * @since 1.0.0
     */
    protected function consolidateDateFormats()
    {
        $timePartCharacters = DateFormat::getTimePartCharactersAsArray($this->startDateFormat);

        foreach ($timePartCharacters as $index => $timePartCharacter) {
            if ($this->timePartValueInDatesIsConsistent($timePartCharacters, $timePartCharacter)) {
                if (DateFormat::nextCharacterIsSmallerTimePart($timePartCharacters, $index)) {
                    $this->endDateFormat = DateFormat::removeTimePartCharacterFromFormat(
                        $timePartCharacter,
                        $this->endDateFormat
                    );
                } else {
                    $this->startDateFormat = DateFormat::removeTimePartCharacterFromFormat(
                        $timePartCharacter,
                        $this->startDateFormat
                    );
                }
            }
        }

        $this->startDateFormat = trim(trim($this->startDateFormat, $this->removableDelimiters));
        $this->endDateFormat   = trim(trim($this->endDateFormat, $this->removableDelimiters));
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
     * Check if time part value in dates is consistent (i.e. Feb and Feb)
     *
     * @param string $timePartCharacter Time part character to check.
     *
     * @return bool
     */
    protected function timePartValueInDatesIsConsistent(array $timePartCharacters, string $timePartCharacter): bool
    {
        $format = implode(DateFormat::removeSmallerTimePartCharacters($timePartCharacters, $timePartCharacter));

        return $this->formattedDatesMatch($format);
    }
}

// @codingStandardsChangeSetting ObjectCalisthenics.Files.ClassTraitAndInterfaceLength maxLength 200
