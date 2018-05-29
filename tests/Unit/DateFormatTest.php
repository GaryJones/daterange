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

use Gamajo\DateRange\DateFormat;
use PHPUnit\Framework\TestCase;

/**
 * Class BoilerplateClassTest.
 *
 * @since  0.1.0
 *
 * @author Gary Jones
 * @group dateformat
 */
class DateFormatTest extends TestCase
{
    /**
     * Test date range output with no modifications.
     *
     * @param string $startDate Start Date.
     * @param string $endDate   End Date.
     * @param string $format    Format.
     * @param string $expected  Expected.
     *
     * @dataProvider dataGetTimePartCharactersAsArray
     */
    public function testGetTimePartCharactersAsArray(string $dateString, array $expected)
    {
        self::assertEquals($expected, DateFormat::getTimePartCharactersAsArray($dateString));
    }

    /**
     * @return array
     */
    public function dataGetTimePartCharactersAsArray()
    {
        return [
            [
                'd-M-Y',
                ['d', 'M', 'Y'],
            ],
            [
                'd-M-Yabc',
                ['d', 'M', 'Y'],
            ],
            [
                'M j<\s\up>S</\s\up> Y',
                ['M', 'j', 'Y'],
            ],
            // 'a' => [
            //     'd~M\Y', // Y is escaped - should show literal Y.
            //     ['d', 'M'],
            // ],
            // 'b' => [
            //     'd~M\\Y', // Y is escaped - should show literal Y.
            //     ['d', 'M'],
            // ],
            // 'c' => [
            //     'd~M\\\Y', // Y is NOT escaped - should show \2018.
            //     ['d', 'M', 'Y'],
            // ],
        ];
    }

    /**
     * @param array $characters
     * @param int   $index
     * @param bool  $expected
     * @dataProvider dataIsEscapedCharacter
     */
    public function testIsNotEscapedCharacter(array $characters, int $index, bool $expected)
    {
        self::markTestIncomplete();
        self::assertEquals($expected, DateFormat::isEscapedCharacter($characters, $index));
    }

    /**
     * @return array
     */
    public function dataIsEscapedCharacter()
    {
        return [
            [
                ['d', '~', 'M', '\\', 'Y'],
                4,
                true
            ],
            [
                ['d', '~', 'M', '\\', '\\', 'Y'],
                4,
                true
            ],
        ];
    }

    /**
     * @param array $timePartCharacters
     * @param int   $currentCharacterIndex
     * @param bool  $expected
     * @dataProvider dataNextTimePartCharacterIsSmaller
     */
    public function testNextTimePartCharacterIsSmaller(
        array $timePartCharacters,
        int $currentCharacterIndex,
        bool $expected
    ) {
        self::assertEquals(
            $expected,
            DateFormat::nextCharacterIsSmallerTimePart($timePartCharacters, $currentCharacterIndex)
        );
    }

    /**
     * @return array
     */
    public function dataNextTimePartCharacterIsSmaller()
    {
        return [
            [
                ['d', 'M', 'Y'],
                1,
                false,
            ],
            [
                ['M', 'd', 'Y'],
                1,
                false,
            ],
            [
                ['M', 'd', 'Y'],
                0,
                true,
            ],
            [
                ['M', 'm', 'Y'],
                0,
                true,
            ],
            [
                ['M', 'm', 'Y'],
                2,
                false,
            ],
        ];
    }

    /**
     * @param array  $timePartCharacters
     * @param string $character
     * @param array  $expected
     * @dataProvider dataRemoveSmallerTimePartCharacters
     */
    public function testremoveSmallerTimePartCharacters(array $timePartCharacters, string $character, array $expected)
    {
        self::assertEquals($expected, DateFormat::removeSmallerTimePartCharacters($timePartCharacters, $character));
    }

    /**
     * @return array
     */
    public function dataRemoveSmallerTimePartCharacters()
    {
        return [
            [
                ['d', 'M', 'Y'],
                'M',
                ['M', 'Y'],
            ],
            [
                ['M', 'd', 'Y'],
                'd',
                ['M', 'd', 'Y'],
            ],
            [
                ['M', 'd', 'Y'],
                'j',
                ['M', 'd', 'Y'],
            ],
            [
                ['M', 'd', 'Y'],
                'Y',
                ['Y'],
            ],
        ];
    }

    /**
     * @param string $character
     * @param bool   $expected
     * @dataProvider dataIsHourMinuteSecond
     */
    public function testIsHourMinuteSecond(string $character, bool $expected)
    {
        self::assertEquals($expected, DateFormat::isHourMinuteSecond($character));
    }

    /**
     * @return array
     */
    public function dataIsHourMinuteSecond()
    {
        return [
            'Numeric representation of a month, with leading zeros' => [
                'm',
                false,
            ],
            '24-hour format of an hour without leading zeros' => [
                'G',
                true,
            ],
            '24-hour format of an hour with leading zeros' => [
                'H',
                true,
            ],
            'Minutes with leading zeros' => [
                'i',
                true,
            ],
            'Seconds, with leading zeros' => [
                's',
                true,
            ],
            'English ordinal suffix for the day of the month, 2 characters' => [
                'S',
                false,
            ],
        ];
    }
}
