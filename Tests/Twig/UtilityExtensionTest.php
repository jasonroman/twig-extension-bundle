<?php

namespace JasonRoman\Bundle\TwigExtensionBundle\Tests\Twig;

use JasonRoman\Bundle\TwigExtensionBundle\Twig\UtilityExtension;

/**
 * Utility Bundle Twig Extension unit tests
 * 
 * @author Jason Roman <j@jayroman.com>
 */
class UtilityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JasonRoman\Bundle\TwigExtensionBundle\Twig\UtilityExtension
     */
    private static $class;


    /**
     * Runs once per entire suite of tests
     */
    public static function setUpBeforeClass()
    {
        self::$class = new UtilityExtension();
    }

    /**
     * Tests that all of the twig filters are initiated and of the proper type
     */
    public function testGetFilters()
    {
        $validFilters = array('phone', 'price', 'boolean', 'md5', 'timeAgo');

        $filters = self::$class->getFilters();

        foreach ($filters as $filter)
        {
            $this->assertContains($filter->getName(), $validFilters);
            $this->assertInstanceOf('Twig_SimpleFilter', $filter);
        }
    }

    /**
     * Test the name of the twig utility extension class
     */
    public function testGetName()
    {
        $this->assertEquals('utility_extension', self::$class->getName());
    }
    
    /**
     * Tests that the default phone filter returns phone numbers in the default format
     */
    public function testPhoneFilterDefault()
    {
        $default_filter = '(123) 456-7890';

        // these all contain 10 or 11 digits and should return the same string
        $this->assertEquals($default_filter, self::$class->phoneFilter('1234567890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('123.456.7890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('11234567890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('(123)4567890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('(123) 456-7890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('(123)456-7890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('1-123-456-7890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('1 (123)-456-7890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('+1234567890'));
        $this->assertEquals($default_filter, self::$class->phoneFilter('(123)  456 ,.7890'));
    }

    /**
     * Tests that a custom phone filter returns phone numbers in that custom format
     */
    public function testPhoneFilterCustom()
    {
        // testing the 10-digit cases
        $this->assertEquals('(123) 456-7890', self::$class->phoneFilter('1234567890', '($2) $3-$4'));
        $this->assertEquals('123-456-7890', self::$class->phoneFilter('1234567890', '$2-$3-$4'));
        $this->assertEquals('123.456.7890', self::$class->phoneFilter('1234567890', '$2.$3.$4'));
        $this->assertEquals('456::7890:..:123', self::$class->phoneFilter('1234567890', '$3::$4:..:$2'));
        $this->assertEquals('-(123) 456-7890', self::$class->phoneFilter('1234567890', '$1-($2) $3-$4'));

        // testing the 11-digit cases
        $this->assertEquals('1 (123) 456-7890', self::$class->phoneFilter('11234567890', '$1 ($2) $3-$4'));
        $this->assertEquals('1-123-456-7890', self::$class->phoneFilter('11234567890', '$1-$2-$3-$4'));
        $this->assertEquals('1.123.456.7890', self::$class->phoneFilter('11234567890', '$1.$2.$3.$4'));
        $this->assertEquals('456::7890:..:123???1', self::$class->phoneFilter('11234567890', '$3::$4:..:$2???$1'));
        $this->assertEquals('2-(123) 456-7890', self::$class->phoneFilter('21234567890', '$1-($2) $3-$4'));
    }

    /**
     * Test that invalid phone numbers will return the original string
     * 
     * @dataProvider phoneInvalidProvider
     */
    public function testPhoneFilterInvalid($phone)
    {
        $this->assertEquals($phone, self::$class->phoneFilter($phone));
    }

    /**
     * Test the price filter returning in the proper format
     */
    public function testPriceFilter()
    {
        $this->assertEquals('$20.35', self::$class->priceFilter(20.35));
        $this->assertEquals('$20.35', self::$class->priceFilter('20.35'));
        $this->assertEquals('$123,456.79', self::$class->priceFilter(123456.7890));
        $this->assertEquals('$123,456.79', self::$class->priceFilter(123456.7890, 2));
        $this->assertEquals('$123,456.79', self::$class->priceFilter(123456.7890, 2, '.'));
        $this->assertEquals('$123,456.79', self::$class->priceFilter(123456.7890, 2, '.', ','));
        $this->assertEquals('$123,456.79', self::$class->priceFilter(123456.7890, 2, '.', ','));
        $this->assertEquals('$123,456.789', self::$class->priceFilter(123456.7890, 3, '.', ','));
        $this->assertEquals('$123*456_7890', self::$class->priceFilter(123456.7890, 4, '_', '*'));
        $this->assertEquals('$123$456$789!789', self::$class->priceFilter(123456789.7890, 3, '!', '$'));
    }

    /**
     * Tests that all of the passed-in values will return true (default 'Yes' for the twig extension)
     * 
     * @dataProvider booleanTrueProvider
     */
    public function testBooleanFilterTrue($value)
    {
        $this->assertEquals('Yes', self::$class->booleanFilter($value));
    }

    /**
     * Tests that all of the passed-in values will return false (default 'No' for the twig extension)
     * 
     * @dataProvider booleanFalseProvider
     */
    public function testBooleanFilterFalse($value)
    {
        $this->assertEquals('No', self::$class->booleanFilter($value));
    }

    /**
     * Tests that custom values will be returned instead of Yes/No
     */
    public function testBooleanFilterCustom()
    {
        $this->assertEquals('Hey', self::$class->booleanFilter(true, 'Hey', 'YeahNo'));
        $this->assertEquals('Hey', self::$class->booleanFilter('true', 'Hey', 'YeahNo'));
        $this->assertEquals('YeahNo', self::$class->booleanFilter(false, 'Hey', 'YeahNo'));
        $this->assertEquals('YeahNo', self::$class->booleanFilter(0, 'Hey', 'YeahNo'));
    }

    /**
     * Tests that the Twig filter returns the md5 hash of the given string
     * 
     * @dataProvider md5Provider
     */
    public function testMd5Filter($string)
    {
        $this->assertEquals(md5($string), self::$class->md5Filter($string));   
    }

    /**
     * Tests that the time ago filter returns time in the appropriate format
     */
    public function testTimeAgoFilterValid()
    {
        // testing 1 parameter passed
        $this->assertEquals('1 day ago', self::$class->timeAgoFilter(new \DateTime('-1 day')));
        $this->assertEquals('2 days ago', self::$class->timeAgoFilter(new \DateTime('-2 days')));
        $this->assertEquals('14 days ago', self::$class->timeAgoFilter(new \DateTime('-2 weeks')));
        $this->assertEquals('14 days ago', self::$class->timeAgoFilter('-2 weeks'));

        // testing 2 parameters passed
        $this->assertEquals('18 hours ago', self::$class->timeAgoFilter(new \DateTime('-1 day 6 hours'), 2));
        $this->assertEquals('1 day 6 hours ago', self::$class->timeAgoFilter(new \DateTime('-30 hours'), 2));
        $this->assertEquals('2 days 6 hours ago', self::$class->timeAgoFilter(new \DateTime('-54 hours'), 2));
        $this->assertEquals('2 days 6 hours ago', self::$class->timeAgoFilter('-54 hours', 2));

        // testing 3 parameters passed
        $this->assertEquals('1 day ago, yar!', self::$class->timeAgoFilter(new \DateTime('-1 day'), 1, 'ago, yar!'));
        $this->assertEquals('1 day ago, yar!', self::$class->timeAgoFilter('-1 day', 1, 'ago, yar!'));

        // testing 4 parameters passed
        $this->assertEquals('2 days ago', self::$class->timeAgoFilter(new \DateTime(), 1, 'ago', new \DateTime('+2 days')));
        $this->assertEquals('2 days mkay', self::$class->timeAgoFilter(new \DateTime('-4 days'), 1, 'mkay', new \DateTime('-2 days')));
        $this->assertEquals('2 days mkay', self::$class->timeAgoFilter(new \DateTime('+7 days'), 1, 'mkay', new \DateTime('+9 days')))
        $this->assertEquals('2 days mkay', self::$class->timeAgoFilter('+7 days', 1, 'mkay', new \DateTime('+9 days')));
    }

    /**
     * Tests that the time ago filter with varying levels of granularity
     */
    public function testTimeAgoFilterValidGranularity()
    {
        $this->assertEquals('1 day ago', self::$class->timeAgoFilter(new \DateTime('-1 day'), 1));
        $this->assertEquals('1 day ago', self::$class->timeAgoFilter(new \DateTime('-30 hours'), 1));
        $this->assertEquals('1 day 6 hours ago', self::$class->timeAgoFilter(new \DateTime('-30 hours'), 2));
        $this->assertEquals('2 days 6 hours 10 minutes ago', self::$class->timeAgoFilter(new \DateTime('-54 hours -10 minutes'), 3));

        $this->assertEquals('2 days 6 hours 10 minutes 27 seconds ago',
            self::$class->timeAgoFilter(new \DateTime('-54 hours -10 minutes -27 seconds'), 4));

        $this->assertEquals('3 months 2 days 6 hours 10 minutes 27 seconds ago',
            self::$class->timeAgoFilter(new \DateTime('-3 months -2 days -6 hours -10 minutes -27 seconds'), 5));

        $this->assertEquals('8 years 1 month 2 days 1 hour 10 minutes 27 seconds ago',
            self::$class->timeAgoFilter(new \DateTime('-8 years -1 months -2 days -1 hour -10 minutes -27 seconds'), 6));

        // test passing a value less than 1 or greater than 6, which should set the granularity to 1
        $this->assertEquals('1 day ago', self::$class->timeAgoFilter(new \DateTime('-1 day'), -5));
        $this->assertEquals('1 day ago', self::$class->timeAgoFilter(new \DateTime('-30 hours 10 seconds'), 0));
        $this->assertEquals('2 days ago, yar!', self::$class->timeAgoFilter(new \DateTime('-54 hours -10 minutes'), 7, 'ago, yar!'));
        $this->assertEquals('1 year ago', self::$class->timeAgoFilter(new \DateTime('-1 year -2 hours'), 'a', 'ago', new \DateTime('+2 days')));
    }

    /**
     * Tests that the time ago filter properly returns when the times are the same
     */
    public function testTimeAgoFilterSame()
    {
        $dateTime = new \DateTime();

        $this->assertEquals('0 seconds ago', self::$class->timeAgoFilter($dateTime, 1, 'ago', $dateTime)));
        $this->assertEquals('0 seconds weeee', self::$class->timeAgoFilter($dateTime, 1, 'weeee', $dateTime)));
    }

    /**
     * Tests that the time ago filter does not return time in the appropriate format
     */
    public function testTimeAgoFilterInvalid()
    {
        $this->assertNull(self::$class->timeAgoFilter(new \DateTime('+30 seconds')));
        $this->assertNull(self::$class->timeAgoFilter(new \DateTime('+2 days')));
        $this->assertNull(self::$class->timeAgoFilter(new \DateTime(), 1, 'ago', new \DateTime('-1 day')));
        $this->assertNull(self::$class->timeAgoFilter(new \DateTime('-2 days'), 1, 'ago', new \DateTime('-7 days')));
        $this->assertNull(self::$class->timeAgoFilter(new \DateTime('+5 days'), 1, 'ago', new \DateTime('+3 days')));
    }

    /**
     * Returns invalid phone numbers that will not be converted to the specified format
     * 
     * @return array
     */
    public function phoneInvalidProvider()
    {
        return array(
            array('xxx'),
            array('123-456-789'),
            array('456-7890'),
            array('1 (123) 456-78901'),
        );
    }

    /**
     * Returns values that should return 'Yes' (true) for the boolean filter
     */
    public function booleanTrueProvider()
    {
        return array(
            array(true),
            array('true'),
            array('t'),
            array('yes'),
            array('y'),
            array(-1),
            array(1),
            array('YeS'),
            array('TRUE'),
            array('Y')
        );
    }

    /**
     * Returns values that should return 'No' (false) for the boolean filter
     */
    public function booleanFalseProvider()
    {
        return array(
            array(false),
            array('trueee'),
            array('fa1lse'),
            array('adslfkjadslkfjadsf'),
            array(0),
            array(5),
        );
    }

    /**
     * Returns strings to be passed to the md5 filter
     */
    public function md5Provider()
    {
        return array(
            array('abcdefg'),
            array('@)LK#_(VI)U*&SD(*V&S)D(F*_SDFJQ#*JWEF'),
            array('lk30v9m3219k-vc3ok'),
            array('kkkXXXX000__-1-1pdkfMMMD::BV'),
        );
    }
}
