<?php

namespace Tests\AppBundle\Util;

use AppBundle\Util\Stats;

class StatsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $vals
     * @param $mean
     *
     * @dataProvider getMeanData
     */
    public function testMean($vals, $mean)
    {
        $this->assertSame($mean, round(Stats::mean($vals), 2));
    }

    public function testMeanOfEmptyArrayIsNull()
    {
        $this->assertNull(Stats::mean(array()));
    }

    public function getMeanData()
    {
        // Mean values should be rounded to a precision of 2 for these tests.
        return array(
            array(array(1), 1.0),
            array(array(1,2,3), 2.0),
            array(array(1,1000), 500.5),
            array(array(1,2,3,4,5,1000), 169.17),
        );
    }

    /**
     * @param $vals
     * @param $median
     *
     * @dataProvider getMedianData
     */
    public function testMedian($vals, $median)
    {
        $this->assertSame($median, Stats::median($vals));
    }

    public function getMedianData()
    {
        return array(
            array(array(), null), // Empty array returns null
            array(array(1), 1),
            array(array(1,2,3), 2),
            array(array(2,4,6), 4),
            array(array(1,1000), 500.5),
            array(array(1,2,1000), 2),
            array(array(1,2,3,4,5,1000), 3.5),
        );
    }

    /**
     * @param $vals
     * @param $range
     *
     * @dataProvider getRangeData
     */
    public function testRange($vals, $range)
    {
        $this->assertSame($range, Stats::range($vals));
    }

    public function getRangeData()
    {
        return array(
            array(array(), null),
            array(array(1), 0),
            array(array(1, 10), 9),
            array(array(9, 3, 23.5, 17), 20.5),
        );
    }

    /**
     * @param $vals
     * @param $variance
     *
     * @dataProvider getPopulationVarianceData
     */
    public function testPopulationVariance($vals, $variance)
    {
        $this->assertSame($variance, round(Stats::variance($vals), 4));
    }

    public function testPopulationVarianceOfEmptyArrayIsNull()
    {
        $this->assertNull(Stats::variance(array()));
    }

    public function getPopulationVarianceData()
    {
        // Result should be a float rounded to a precision of 4 for these tests
        return array(
            array(array(10, 17, 32.654, 9, 3.14), 103.0535),
            array(array(1), 0.0),
            array(array(5, 5), 0.0),
            array(array(1, 5, 999), 220450.6667),
            array(array(1, 999), 249001.0),
            array(array(10, 20, 30, 40), 125.0),
        );
    }

    /**
     * @param $vals
     * @param $variance
     *
     * @dataProvider getSampleVarianceData
     */
    public function testSampleVariance($vals, $variance)
    {
        $this->assertSame($variance, round(Stats::variance($vals, true), 4));
    }

    public function testSampleVarianceWithLessThanTwoElementsIsNull()
    {
        $this->assertNull(Stats::variance(array(), true));
        $this->assertNull(Stats::variance(array(1), true));
    }

    public function getSampleVarianceData()
    {
        // Result should be a float rounded to a precision of 4 for these tests
        return array(
            array(array(10, 17, 32.654, 9, 3.14), 128.8169),
            array(array(5, 5), 0.0),
            array(array(1, 5, 999), 330676.0),
            array(array(1, 999), 498002.0),
            array(array(10, 20, 30, 40), 166.6667),
        );
    }

    /**
     * @param $vals
     * @param $stdev
     *
     * @dataProvider getPopulationStandardDeviationData
     */
    public function testPopulationStandardDeviation($vals, $stdev)
    {
        $this->assertSame($stdev, round(Stats::standardDeviation($vals), 4));
    }

    public function testPopulationStandardDeviationOfEmptyArrayIsNull()
    {
        $this->assertNull(Stats::standardDeviation(array()));
    }

    public function getPopulationStandardDeviationData()
    {
        // Result should be a float rounded to a precision of 4 for these tests
        return array(
            array(array(10, 17, 32.654, 9, 3.14), 10.1515),
            array(array(1), 0.0),
            array(array(5, 5), 0.0),
            array(array(1, 5, 999), 469.5217),
            array(array(1, 999), 499.0),
            array(array(10, 20, 30, 40), 11.1803),
        );
    }

    /**
     * @param $vals
     * @param $stdev
     *
     * @dataProvider getSampleStandardDeviationData
     */
    public function testSampleStandardDeviation($vals, $stdev)
    {
        $this->assertSame($stdev, round(Stats::standardDeviation($vals, 1), 4));
    }

    public function testSampleStandardDeviationWithLessThanTwoElementsIsNull()
    {
        $this->assertNull(Stats::standardDeviation(array(), true));
        $this->assertNull(Stats::standardDeviation(array(1), true));
    }

    public function getSampleStandardDeviationData()
    {
        // Result should be a float rounded to a precision of 4 for these tests
        return array(
            array(array(10, 17, 32.654, 9, 3.14), 11.3498),
            array(array(5, 5), 0.0),
            array(array(1, 5, 999), 575.0443),
            array(array(1, 999), 705.6926),
            array(array(10, 20, 30, 40), 12.9099),
        );
    }
}
