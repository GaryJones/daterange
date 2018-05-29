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

/**
 * Class DateRange.
 *
 * @package Gamajo\DateRange
 */
class DateFormat
{
    /**
     * Time part character sets.
     *
     * Treated like aliases i.e. a month part might be required as March, Mar, 3 or 03,
     * but they are all treated as a month.
     *
     * As per https://secure.php.net/manual/en/function.date.php.
     */
    public const CHAR_SETS = [
        ['o', 'Y', 'y'], // Year
        ['F', 'm', 'M', 'n'], // Month
        ['d', 'j'], // Date
        'time' => [
            ['g', 'G', 'h', 'H'], // Hour
            ['i'], // Minute
            ['s'], // Second
        ],
    ];

    // @codingStandardsChangeSetting ObjectCalisthenics.Metrics.MaxNestingLevel maxNestingLevel 3
    /**
     * Sanitize timePartCharacters to remove escaped characters and only contain known characters to replace.
     *
     * @param string $format
     *
     * @return array
     */
    public static function getTimePartCharactersAsArray(string $format)
    {
        $sanitized = [];

        $characters = \str_split($format);

        foreach ($characters as $index => $character) {
            if (self::isKnownTimePartCharacter($character)) {
                // Skip over escaped characters.
                if (! self::isEscapedCharacter($characters, $index)) {
                    $sanitized[] = $character;
                }
            }
        }

        return \array_unique($sanitized);
    }

    /**
     * Determine if the next character represents a smaller time part.
     *
     * @param array $timePartCharacters
     * @param int   $currentCharacterIndex
     *
     * @return bool True if next character represents a smaller time part.
     */
    public static function nextCharacterIsSmallerTimePart(array $timePartCharacters, int $currentCharacterIndex): bool
    {
        if (\count($timePartCharacters) === $currentCharacterIndex + 1) {
            return false;
        }

        $currentCharsetIndex = 0;
        $nextCharacterCharsetIndex = 0;

        foreach (self::CHAR_SETS as $charsetIndex => $charset) {
            if (\in_array($timePartCharacters[$currentCharacterIndex], self::flattenArray($charset), true)) {
                $currentCharsetIndex = $charsetIndex;
            }
        }

        foreach (self::CHAR_SETS as $charsetIndex => $charset) {
            if (\array_key_exists($currentCharacterIndex + 1, $timePartCharacters) &&
                in_array($timePartCharacters[ $currentCharacterIndex + 1], self::flattenArray($charset), true)
            ) {
                $nextCharacterCharsetIndex = $charsetIndex;
            }
        }

        return $currentCharsetIndex <= $nextCharacterCharsetIndex;
    }

    /**
     * Given a time part character, remove other time part characters that represent smaller time parts.
     *
     * e.g. if M (month) is given, remove characters representing day, but not month or year.
     *
     * @param array  $timePartCharacters Time part characters.
     * @param string $timePartCharacter  Time part character.
     *
     * @return array Time part characters.
     */
    public static function removeSmallerTimePartCharacters(array $timePartCharacters, string $timePartCharacter): array
    {
        foreach (self::CHAR_SETS as $charsetIndex => $charset) {
            if (\in_array($timePartCharacter, self::flattenArray($charset), true)) {
                $currentCharsetIndex = $charsetIndex;
                break;
            }
        }

        $timePartCharactersString = \implode($timePartCharacters);

        foreach (self::CHAR_SETS as $charsetIndex => $charset) {
            if ($charsetIndex > $currentCharsetIndex) {
                $timePartCharactersString = self::removeTimePartAliasesFromFormat(
                    self::flattenArray($charset),
                    $timePartCharactersString
                );
            }
        }

        return \str_split($timePartCharactersString);
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
    public static function removeTimePartCharacterFromFormat(string $timePartCharacter, string $format): string
    {
        $timePartAliases = self::getTimePartAliases($timePartCharacter);

        return self::removeTimePartAliasesFromFormat($timePartAliases, $format);
    }

    /**
     * @param array  $timePartAliases Characters that match the time part.
     * @param string $format          Date format.
     *
     * @return string Updated date format.
     */
    public static function removeTimePartAliasesFromFormat(array $timePartAliases, string $format): string
    {
        return \str_replace($timePartAliases, '', $format);
    }

    /**
     * @param string $timePartCharacter Time part character from format.
     *
     * @return array
     */
    public static function getTimePartAliases(string $timePartCharacter): array
    {
        foreach (self::CHAR_SETS as $timePartAliases) {
            if (\in_array($timePartCharacter, $timePartAliases, true)) {
                $return = $timePartAliases;
            }
        }

        return $return;
    }

    /**
     * Determine if a character is an hour, minute or second time part character.
     *
     * @param string $character Time part character to test.
     *
     * @return bool True if it is an hour, minute or second time part character.
     */
    public static function isKnownTimePartCharacter(string $character): bool
    {
        return \in_array($character, self::flattenArray(self::CHAR_SETS), true);
    }

    /**
     * Determine if a character is an hour, minute or second time part character.
     *
     * @param string $character Time part character to test.
     *
     * @return bool True if it is an hour, minute or second time part character.
     */
    public static function isHourMinuteSecond(string $character): bool
    {
        return \in_array($character, self::flattenArray(self::CHAR_SETS['time']), true);
    }

	/**
	 * Check if the character is escaped.
	 *
	 * @param array $characters Array of characters.
	 * @param int   $index      Index of the array for the character to be checked.
	 *
	 * @return bool True if the character at the given index was escaped.
	 */
    public static function isEscapedCharacter(array $characters, int $index): bool
    {
        return $index >= 1 && $characters[$index-1] === '\\';
    }

    /**
     * Flatten a multidimensional index array.
     *
     * @param array $array Multidimensional array to flatten.
     *
     * @return array Flattened array.
     */
    public static function flattenArray(array $array)
    {
        $return = [];
        \array_walk_recursive($array, function ($arr) use (&$return) {
            $return[] = $arr;
        });
        return $return;
    }
}
