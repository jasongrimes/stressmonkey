<?php

namespace AppBundle\Util;

class Stats
{
    /**
     * An alias for mean().
     *
     * @param array $vals
     * @return float|int
     */
    public static function average(array $vals)
    {
        return self::mean($vals);
    }

    /**
     * Calculate the mean of an array of values.
     *
     * @param array $vals
     * @return float|int
     */
    public static function mean(array $vals)
    {
        if (empty($vals)) {
            return null;
        }

        return array_sum($vals) / count($vals);
    }

    /**
     * Calculate the median of an array of values.
     *
     * @param array $vals
     * @return float|int
     */
    public static function median(array $vals)
    {
        $numVals = count($vals);

        if ($numVals == 0) {
            return null;
        }

        sort($vals, SORT_NUMERIC);
        $middleIndex = (int) floor($numVals/2);

        if ($numVals % 2) {
            // Odd number of values. Return the middle one.
            return $vals[$middleIndex];

        } else {
            // Even number of values.
            // Return the average of the two middle values.
            return ($vals[$middleIndex] + $vals[$middleIndex - 1]) / 2;
        }
    }

    /**
     * Calculate the range of an array of values.
     *
     * @param array $vals
     * @return int|mixed
     */
    public static function range(array $vals)
    {
        if (empty($vals)) {
            return null;
        }

        sort($vals, SORT_NUMERIC);

        return end($vals) - $vals[0];
    }

    /**
     * Calculate the variance (the average of squared differences from the mean) of an array of values.
     *
     * Note that this algorithm may suffer from "numerical instability" for very large amounts.
     * See https://en.wikipedia.org/wiki/Algorithms_for_calculating_variance#Online_algorithm
     * for discussion and a possible alternate approach.
     *
     * @param array $vals
     * @param bool $sample If true, compute the (unbiased) sample variance. Otherwise, compute the population variance.
     * @return float|null
     */
    public static function variance(array $vals, $sample = false)
    {
        $n = count($vals);

        if ($n === 0) {
            return null;
        }

        if ($sample && $n === 1) {
            return null;
        }

        $carry = 0.0;
        foreach ($vals as $val) {
            $d = ((double) $val) - self::mean($vals);
            $carry += $d * $d;
        };

        if ($sample) {
            $n--;
        }

        return $carry / $n;
    }

    /**
     * Calculate the standard deviation of an array of values.
     *
     * @param array $vals
     * @param bool $sample If true, compute the unbiased sample standard deviation. Otherwise, compute the population standard deviation.
     * @return float|null
     */
    public static function standardDeviation(array $vals, $sample = false)
    {
        $variance = self::variance($vals, $sample);

        if ($variance === null) {
            return null;
        }

        return sqrt($variance);
    }
}