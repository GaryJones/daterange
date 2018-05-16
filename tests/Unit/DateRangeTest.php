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

namespace Gamajo\DateRange\Tests\Unit;

use DateTimeImmutable;
use Exception;
use Gamajo\DateRange\DateRange;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Class BoilerplateClassTest.
 *
 * @since  0.1.0
 *
 * @author Gary Jones
 */
class DateRangeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test whether the class can be instantiated.
     *
     * DateTimeInterface can't be mocked
     *
     * @since 0.1.0
     * @throws Exception
     */
    public function testClassInstantiationWithDateTimeImmutable()
    {
        $dateTime = m::mock(DateTimeImmutable::class);
        $object   = new DateRange($dateTime, $dateTime);
        self::assertInstanceOf(
            'Gamajo\DateRange\DateRange',
            $object
        );
    }

    /**
     * Test date range output with no modifications.
     *
     * @param string $startDate Start Date.
     * @param string $endDate   End Date.
     * @param string $format    Format.
     * @param string $expected  Expected.
     *
     * @dataProvider dataStartEndDates
     * @throws Exception
     */
    public function testDateRangeNoModifications(string $startDate, string $endDate, string $format, string $expected)
    {
        $dateRange = new DateRange(new DateTimeImmutable($startDate), new DateTimeImmutable($endDate));
        self::assertEquals($expected, $dateRange->format($format));
    }

    /**
     * @return array
     */
    public function dataStartEndDates()
    {
        return [
            'Single date' => [
                '1980-03-14',
                '1980-Mar-14',
                'D jS F Y',
                'Fri 14th March 1980',
            ],
            'Common month and year' => [
                '2018-06-18',
                '23rd June 2018',
                'jS F Y',
                '18th – 23rd June 2018',
            ],
            'Same date of the month, but different month' => [
                '2018-06-23',
                '23 July 2018',
                'd M Y',
                '23 Jun – 23 Jul 2018',
            ],
            'Same date of the month, different month, date of the month ignored' => [
                '2017-06-23',
                '2018-06-23',
                'M Y',
                'Jun 2017 – Jun 2018',
            ],
            'Single date, date of the month different but ignored' => [
                '2017-04-06',
                '2017-04-05',
                'M Y',
                'Apr 2017',
            ],
            'Different hour, but hour ignored' => [
                '23 Jun 18 14:00',
                '2018-06-23T15:00',
                'd M Y',
                '23 Jun 2018',
            ],
            'Same date, different hour' => [
                '23rd June 18 14:00',
                '2018-06-23T15:00',
                'H:i d M Y',
                '14:00 – 15:00 23 Jun 2018',
            ],
            'Different date and hour' => [
                '2018-06-23T14:00',
                'June 24 18 15:00',
                'H:i dS M Y',
                '14:00 23rd – 15:00 24th Jun 2018',
            ],
        ];
    }

    /**
     * Test date range output with modified separator.
     *
     * @throws Exception
     */
    public function testDateRangeModifiedSeparator()
    {
        $dateRange = new DateRange(new DateTimeImmutable('6th Feb 18'), new DateTimeImmutable('2018-02-07'));
        $dateRange->changeSeparator(' to ');
        self::assertEquals('06 to 07/2/18', $dateRange->format('d/n/y'));
    }

    /**
     * Test date range output with modified removable delimiters.
     *
     * @throws Exception
     */
    public function testDateRangeModifiedRemovableDelimiters()
    {
        $dateRange = new DateRange(new DateTimeImmutable('6th Feb 18'), new DateTimeImmutable('2018-02-07'));
        $dateRange->changeRemovableDelimiters('~\\');
        self::assertEquals('06 – 07~Feb\18', $dateRange->format('d~M\\\y'));
    }

    /**
     * Test date range output with duplicated formatting characters.
     *
     * @group duplicate
     * @throws Exception
     */
    public function testDateRangeDuplicatedFormattingCharacters()
    {
        $dateRange = new DateRange(new DateTimeImmutable('6th Feb 18'), new DateTimeImmutable('2018-02-07'));
        $dateRange->changeRemovableDelimiters('~\\');
        self::assertEquals('0606Tue – 201807Feb180207Feb2018Wed02', $dateRange->format('YdMymdMYDm'));
    }
}
