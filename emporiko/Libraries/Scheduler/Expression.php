<?php

namespace EMPORIKO\Libraries\Scheduler;

use \DateTime;
use \DateTimeInterface;
use \DateTimeZone;
use \Exception;

/**
 * Cron expression parser and validator
 *
 * @author René Pollesch
 */
class Expression
{
    /**
     * Weekday name look-up table
     */
    private const WEEKDAY_NAMES = [
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6
    ];

    /**
     * Month name look-up table
     */
    private const MONTH_NAMES = [
        'jan' => 1,
        'feb' => 2,
        'mar' => 3,
        'apr' => 4,
        'may' => 5,
        'jun' => 6,
        'jul' => 7,
        'aug' => 8,
        'sep' => 9,
        'oct' => 10,
        'nov' => 11,
        'dec' => 12
    ];

    /**
     * Value boundaries
     */
    private const VALUE_BOUNDARIES = [
        0 => [
            'min' => 0,
            'max' => 59,
            'mod' => 1
        ],
        1 => [
            'min' => 0,
            'max' => 23,
            'mod' => 1
        ],
        2 => [
            'min' => 1,
            'max' => 31,
            'mod' => 1
        ],
        3 => [
            'min' => 1,
            'max' => 12,
            'mod' => 1
        ],
        4 => [
            'min' => 0,
            'max' => 7,
            'mod' => 0
        ]
    ];

    /**
     * Time zone
     *
     * @var DateTimeZone|null
     */
    protected $timeZone = null;

    /**
     * Matching registers
     *
     * @var array|null
     */
    public $registers = null;
    
    public static function init(string $expression, DateTimeZone $timeZone = null)
    {
        return new Expression($expression,$timeZone);
    }
    
    /**
     * @param string $expression a cron expression, e.g. "* * * * *"
     * @param DateTimeZone|null $timeZone time zone object
     */
    public function __construct(string $expression, DateTimeZone $timeZone = null)
    {
        $this->timeZone = $timeZone;

        try {
            $this->registers = $this->parse($expression);
        } catch (Exception $e) {
            $this->registers = null;
        }
    }

    /**
     * Whether current cron expression has been parsed successfully
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return null !== $this->registers;
    }

    /**
     * Match either "now", a given date/time object or a timestamp against current cron expression
     *
     * @param mixed $when a DateTime object, a timestamp (int), or "now" if not set
     * @return bool
     * @throws Exception
     */
    public function isMatching($when = null): bool
    {
        if (false === ($when instanceof DateTimeInterface)) {
            $when = (new DateTime())->setTimestamp($when === null ? time() : $when);
        }

        if ($this->timeZone !== null) {
            $when->setTimezone($this->timeZone);
        }

        return $this->isValid() && $this->match(sscanf($when->format('i G j n w'), '%d %d %d %d %d'));
    }

    /**
     * Calculate next matching timestamp
     *
     * @param mixed $start a DateTime object, a timestamp (int) or "now" if not set
     * @return int|bool next matching timestamp, or false on error
     * @throws Exception
     */
    public function getNext($start = null)
    {
        if ($this->isValid()) {
            $now = $this->toDateTime($start);
            $pointer = sscanf($now->format('i G j n Y'), '%d %d %d %d %d');

            do {
                $current = $this->adjust($now, $pointer);
            } while ($this->forward($now, $current));

            return $now->getTimestamp();
        }

        return false;
    }

    /**
     * @param mixed $start a DateTime object, a timestamp (int) or "now" if not set
     * @return DateTime
     */
    private function toDateTime($start): DateTime
    {
        if ($start instanceof DateTimeInterface) {
            $now = $start;
        } elseif ((int)$start > 0) {
            $now = new DateTime('@' . $start);
        } else {
            $now = new DateTime('@' . time());
        }

        $now->setTimestamp($now->getTimeStamp() - $now->getTimeStamp() % 60);
        $now->setTimezone($this->timeZone ?: new DateTimeZone(date_default_timezone_get()));

        if ($this->isMatching($now)) {
            $now->modify('+1 minute');
        }

        return $now;
    }

    /**
     * @param DateTimeInterface $now
     * @param array $pointer
     * @return array
     */
    private function adjust(DateTimeInterface $now, array &$pointer): array
    {
        $current = sscanf($now->format('i G j n Y w'), '%d %d %d %d %d %d');

        if ($pointer[0] !== $current[0] || $pointer[1] !== $current[1]) {
            $pointer[0] = $current[0];
            $pointer[1] = $current[1];
            $now->setTime($current[1], $current[0]);
        } elseif ($pointer[4] !== $current[4]) {
            $pointer[4] = $current[4];
            $now->setDate($current[4], 1, 1);
            $now->setTime(0, 0);
        } elseif ($pointer[3] !== $current[3]) {
            $pointer[3] = $current[3];
            $now->setDate($current[4], $current[3], 1);
            $now->setTime(0, 0);
        } elseif ($pointer[2] !== $current[2]) {
            $pointer[2] = $current[2];
            $now->setTime(0, 0);
        }

        return $current;
    }

    /**
     * @param DateTimeInterface $now
     * @param array $current
     * @return bool
     */
    private function forward(DateTimeInterface $now, array $current): bool
    {
        if (isset($this->registers[3][$current[3]]) === false) {
            $now->modify('+1 month');
            return true;
        } elseif (false === (isset($this->registers[2][$current[2]]) && isset($this->registers[4][$current[5]]))) {
            $now->modify('+1 day');
            return true;
        } elseif (isset($this->registers[0][$current[0]]) === false) {
            $now->modify('+1 minute');
            return true;
        } elseif (isset($this->registers[1][$current[1]]) === false) {
            $now->modify('+1 hour');
            return true;
        }

        return false;
    }

    /**
     * @param array $segments
     * @return bool
     */
    public function match(array $segments): bool
    {
        $result = true;

        foreach (is_array($this->registers) ? $this->registers : [] as $i => $item) 
        {
            if (isset($item[(int)$segments[$i]]) === false) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Parse whole cron expression
     *
     * @param string $expression
     * @return array
     * @throws Exception
     */
    private function parse(string $expression): array
    {
        $segments = preg_split('/\s+/', trim($expression));

        if (is_array($segments) && sizeof($segments) === 5) {
            $registers = array_fill(0, 5, []);

            foreach ($segments as $index => $segment) {
                $this->parseSegment($registers[$index], $index, $segment);
            }

            if (isset($registers[4][7])) {
                $registers[4][0] = true;
            }

            return $registers;
        }

        throw new Exception('invalid number of segments');
    }

    /**
     * Parse one segment of a cron expression
     *
     * @param array $register
     * @param int $index
     * @param string $segment
     * @throws Exception
     */
    private function parseSegment(array &$register, $index, $segment): void
    {
        $allowed = [false, false, false, self::MONTH_NAMES, self::WEEKDAY_NAMES];

        // month names, weekdays
        if ($allowed[$index] !== false && isset($allowed[$index][strtolower($segment)])) {
            // cannot be used together with lists or ranges
            $register[$allowed[$index][strtolower($segment)]] = true;
        } else {
            // split up current segment into single elements, e.g. "1,5-7,*/2" => [ "1", "5-7", "*/2" ]
            foreach (explode(',', $segment) as $element) {
                $this->parseElement($register, $index, $element);
            }
        }
    }

    /**
     * @param array $register
     * @param int $index
     * @param string $element
     * @throws Exception
     */
    private function parseElement(array &$register, int $index, string $element): void
    {
        $step = 1;
        $segments = explode('/', $element);

        if (sizeof($segments) > 1) {
            $this->validateStepping($segments, $index);

            $element = (string)$segments[0];
            $step = (int)$segments[1];
        }

        if (is_numeric($element)) {
            $this->validateValue($element, $index, $step);
            $register[intval($element)] = true;
        } else {
            $this->parseRange($register, $index, $element, $step);
        }
    }

    /**
     * Parse range of values, e.g. "5-10"
     *
     * @param array $register
     * @param int $index
     * @param string $range
     * @param int $stepping
     * @throws Exception
     */
    private function parseRange(array &$register, int $index, string $range, int $stepping): void
    {
        if ($range === '*') {
            $range = [self::VALUE_BOUNDARIES[$index]['min'], self::VALUE_BOUNDARIES[$index]['max']];
        } else {
            $range = explode('-', $range);
        }

        $this->validateRange($range, $index);
        $this->fillRange($register, $index, $range, $stepping);
    }

    /**
     * @param array $register
     * @param int $index
     * @param array $range
     * @param int $stepping
     */
    private function fillRange(array &$register, int $index, array $range, int $stepping): void
    {
        $boundary = self::VALUE_BOUNDARIES[$index]['max'] + self::VALUE_BOUNDARIES[$index]['mod'];
        $length = $range[1] - $range[0];

        if ($range[0] > $range[1]) {
            $length += $boundary;
        }

        for ($i = 0; $i <= $length; $i += $stepping) {
            $register[($range[0] + $i) % $boundary] = true;
        }
    }

    /**
     * Validate whether a given range of values exceeds allowed value boundaries
     *
     * @param array $range
     * @param int $index
     * @throws Exception
     */
    private function validateRange(array $range, int $index): void
    {
        if (sizeof($range) !== 2) {
            throw new Exception('invalid range notation');
        }

        foreach ($range as $value) {
            $this->validateValue($value, $index);
        }
    }

    /**
     * @param string $value
     * @param int $index
     * @param int $step
     * @throws Exception
     */
    private function validateValue(string $value, int $index, int $step = 1): void
    {
        if ((string)$value !== (string)(int)$value) {
            throw new Exception('non-integer value');
        }

        if (intval($value) < self::VALUE_BOUNDARIES[$index]['min'] ||
            intval($value) > self::VALUE_BOUNDARIES[$index]['max']
        ) {
            throw new Exception('value out of boundary');
        }

        if ($step !== 1) {
            throw new Exception('invalid combination of value and stepping notation');
        }
    }

    /**
     * @param array $segments
     * @param int $index
     * @throws Exception
     */
    private function validateStepping(array $segments, int $index): void
    {
        if (sizeof($segments) !== 2) {
            throw new Exception('invalid stepping notation');
        }

        if ((int)$segments[1] < 1 || (int)$segments[1] > self::VALUE_BOUNDARIES[$index]['max']) {
            throw new Exception('stepping out of allowed range');
        }
    }
}