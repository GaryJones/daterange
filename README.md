# Gamajo Date Range

[![Latest Stable Version](https://img.shields.io/packagist/v/gamajo/daterange.svg)](https://packagist.org/packages/gamajo/daterange)
[![Total Downloads](https://img.shields.io/packagist/dt/gamajo/daterange.svg)](https://packagist.org/packages/gamajo/daterange)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/gamajo/daterange.svg)](https://packagist.org/packages/gamajo/daterange)
[![License](https://img.shields.io/packagist/l/gamajo/daterange.svg)](https://packagist.org/packages/gamajo/daterange)

Display a range of dates, with consolidated time parts.

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Advanced Usage](#advanced-usage)
* [Contributing](#contributing)
* [License](#license)

## Installation

The best way to use this package is through Composer:

```BASH
composer require gamajo/daterange
```

## Basic Usage

Create an instance of the `DateRange` class, with `DateTimeImmutable` or `DateTime` start and end date-time objects as arguments. Then choose the format to use as the end date output. The start date will only display the time parts that are not duplicated.

```php
$dateRange = new DateRange(
    new DateTimeImmutable('23rd June 18 14:00'),
    new DateTimeImmutable('2018-06-23T15:00')
);
echo $dateRange->format('H:i d M Y'); // 14:00 – 15:00 23 Jun 2018
```

If the formatted date would be the same start and end date, only a single date is displayed:

```php
$dateRange = new DateRange(
    new DateTimeImmutable('23rd June 18 14:00'),
    new DateTimeImmutable('2018-06-23T15:00')
);
echo $dateRange->format('jS M Y'); // 23rd Jun 2018
```

## Advanced Usage

### Change Separator

The default separator between the start and end date, is a space, en-dash, space: `' – '`

This can be changed via the `changeSeparator()` method:

```php
$dateRange = new DateRange(
    new DateTimeImmutable('23rd June 18 14:00'),
    new DateTimeImmutable('2018-06-23T15:00')
);
$dateRange->changeSeparator(' to ');
echo 'From ', $dateRange->format('H:i d M Y'); // From 14:00 to 15:00 23 Jun 2018
```

### Change Removable Delimiters

The consolidation and removal of some time parts may leave delimiters from the format:

```php
$dateRange = new DateRange(
    new DateTimeImmutable('23rd June 18'),
    new DateTimeImmutable('2018-06-24')
);
echo $dateRange->format('d·M·Y'); //  23·· – 24·Jun·2018
```

Be default, `/`, `-` and `.` are trimmed from the start date, but this can be amended with the `changeRemovableDelimiters()` method:

```php
$dateRange = new DateRange(
    new DateTimeImmutable('23rd June 18'),
    new DateTimeImmutable('2018-06-24')
);
$dateRange->changeRemovableDelimiters('·');
echo $dateRange->format('d·M·Y'); //  23 – 24·Jun·2018
```

## Known Issues

These are known issues which need addressing before this package can be considered stable:

- [Escaped characters are not handled correctly](https://github.com/GaryJones/daterange/issues/2)
- [Duplicate time parts are not handled correctly](https://github.com/GaryJones/daterange/issues/3)
- [Hours, minutes and seconds are not handled correctly](https://github.com/GaryJones/daterange/issues/4)


## Contributing

All feedback, bug reports and pull requests are welcome.

## License

Copyright (c) 2018 Gary Jones, Gamajo

This code is licensed under the [MIT License](LICENSE).
