<?php

namespace JasonRoman\Bundle\TwigExtensionBundle\Twig;

use Twig_Extension;
use Twig_Filter_Method;

/**
 * UtilityExtension
 * 
 * Custom Twig Filters
 *      ex: {{ somePhone|phone }}
 *          {{ someCurrency|price }}
 *          {{ someValue|boolean('Y', 'N') }}
 *          {{ someString|md5 }}
 *          {{ someDate|timeAgo }}
 * 
 * @author Jason Roman <j@jayroman.com>
 */
class UtilityExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            'phone'     => new Twig_Filter_Method($this, 'phoneFilter'),
            'price'     => new Twig_Filter_Method($this, 'priceFilter'),
            'boolean'   => new Twig_Filter_Method($this, 'booleanFilter'),
            'md5'       => new Twig_Filter_Method($this, 'md5Filter'),
            'timeAgo'   => new Twig_Filter_Method($this, 'timeAgoFilter'),
        );
    }

    public function getName()
    {
        return 'utility_extension';
    }

    /**
     * Returns a phone number in the specified format
     *   example for formatting '1-800-234-5678': $1 = 1, $2 = 800, $3 = 234, $4 = 5678
     * 
     * @param string $phone
     * @param string $format
     * @return string
     * 
     */
    public function phoneFilter($phone, $format = '($2) $3-$4')
    {
        $original = $phone;

        // strip any non-numeric values from the phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // if the phone number is not 10 or 11 digits, return the original value
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return $original;
        }

        // convert to the specified format and return the value
        $phone = preg_replace('/(\d{1})*(\d{3})(\d{3})(\d{4})$/', $format, $phone);
        
        return $phone;
    }

    /**
     * Returns a monetary price string in the specified format
     * 
     * @param string $number
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    public function priceFilter($number, $decimals = 2, $decPoint = '.', $thousandsSep = ',')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }

    /**
     * Return a string based on a boolean value, where the boolean may not actually
     * be of type boolean (database values of 'True', 'False', 'Yes', 'No', 1, 0, etc.)
     * 
     * @param string $value
     * @param string $true
     * @param string $false
     * @return string
     */
    public function booleanFilter($value, $true = 'Yes', $false = 'No')
    {
        // all of the possible values for true
        $true_values = array('true', 't', 'yes', 'y', -1, 1);

        // if the value is already a boolean, just do the boolean test and return the value
        if (is_bool($value)) {
            return ($value) ? $true : $false;
        }

        // otherwise return true if set to one of the acceptable true values
        if (in_array(strtolower($value), $true_values)) {
            return $true;
        }

        return $false;
    }

    /**
     * Return the md5 hash of a string
     * 
     * @param string $value
     * @return string 32 alphanumeric characters
     */
    public function md5Filter($value)
    {
        return md5($value);
    }

    /**
     * Convert a time to time 'ago', such as 5 days, 1 week, etc.
     * 
     * @param \DateTime $date
     * @param int $granularity level of granularity (how far to drill down in exact time ago)
     * @param string $postText text to display after the time ago
     * @param \DateTime $dateFrom if wanting time ago from a specific datetime rather than the current datetime
     * @return string|null null if the passed in date is earlier than the date to compare it to
     */
    public function timeAgoFilter(\DateTime $date, $granularity = 1, $postText = 'ago', \DateTime $dateFrom = null)
    {
        // interval array matching date format and corresponding type
        $intervals  = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        );

        $i      = 0;
        $result = '';

        // default to find time against the current datetime
        if (!$dateFrom) {
            $dateFrom = new \DateTime();
        }

        // make sure accuracy is between 1 and 6
        $granularity = (int) $granularity;
 
        if ($granularity < 1 || $granularity > 6) {
            $granularity = 1;
        }
 
        // if the datetime passed in is later than the datetime comparing, return null
        if ($date > $dateFrom) {
            return null;
        }
 
        // get the difference between the two dates
        $difference = $dateFrom->diff($date);
 
        // now check each interval to see if the date difference contains that interval
        foreach ($intervals as $interval => $name)
        {
            // only add to the time ago string if the interval is contained in the date difference
            if ($difference->$interval >= 1)
            {
                $result .= ' '.$difference->$interval.' '.$intervals[$interval];
                $i++;

                // if interval is not exactly 1, add an 's' to the type (1 day ago, 3 days ago)
                if ($difference->$interval != 1) {
                    $result .= 's';
                }
            }

            // if we have reached the maximum level of granularity, stop building the string
            if ($i == $granularity) {
                break;
            }
        }

        // now add any suffix to the result string (1 day <postText> => 1 day ago)
        if (strlen($postText)) {
            $result .= ' '.$postText;
        }
 
        // return the trimmed result (the result starts with whitespace)
        return trim($result);
    }
}