<?php namespace translinkrtti;

/**
 * Translink Open API Client
 * 
 * Provides a wrapper to the Vancouver BC Translink Open API and requires an API key to use.
 * For details on how to get started, or apply for an API key, please see developer.translink.ca.
 * 
 * @author Martyr2
 * @copyright 2021 Martyr2
 * @link https://www.coderslexicon.com
 * 
 */

use translinkrtti\lib\CurlRequests;
use translinkrtti\lib\TranslinkException;

class TranslinkRTTI 
{
    const TRANSLINK_DOMAIN = 'https://api.translink.ca/rttiapi/v1';
    const DEFAULT_BUS_COUNT = 6;
    const DEFAULT_TIMEFRAME = 120;
    const DEFAULT_STOP_MAX_RADIUS = 2000;

    private string $apiKey;

    public function __construct(string $apiKey) 
    {
        if (trim($apiKey) === '') {
            throw new TranslinkException('Please specify a Translink RTTI API key.');
        }

        $this->apiKey = $apiKey;
    }


    /**
     * Get the stop information for a given stop number, lat/long location, radius or route number.
     *
     * @param integer $stopNo - 5 Digit Stop number (default is zero for no stop number)
     * @param array $filters - Array of filter information including 'lat', 'long', 'radius' and 'routeNo'
     * @return stdClass
     * @throws TranslinkException if invalid stop number of filters
     */
    public function getStops(int $stopNo = 0, array $filters = []) 
    {
        if (($stopNo !== 0) && !$this->validStopNo($stopNo)) {
            throw new TranslinkException('Invalid stop number. It must be five digits with no leading zeros.');
        }
        
        $url = self::TRANSLINK_DOMAIN . '/stops' . (($stopNo !== 0) ? "/{$stopNo}" : '');
        $url = $this->appendAPIKey($url);

        $lat = $this->getFilter($filters, 'lat');
        $long = $this->getFilter($filters, 'long');
        $radius = $this->getFilter($filters, 'radius');
        $routeNo = $this->getFilter($filters, 'routeNo');

        $validLatAndLong = $this->isValidLatAndLong($lat, $long);

        if ($validLatAndLong) {
            $url .= "&lat={$lat}&long={$long}";
        }

        if (!empty($radius)) {
            if ($validLatAndLong) {
                if ($this->validRadius($radius)) {
                    $url .= "&radius={$radius}";
                } else {
                    throw new TranslinkException("You must specify a radius between 1 and " . self::DEFAULT_STOP_MAX_RADIUS . " meters.");
                }
            } else {
                throw new TranslinkException('You must specify a latitude and longitude if you specify a radius.');
            }
        } 

        if (!empty($routeNo)) {
            if ($validLatAndLong) {
                $url .= "&routeNo={$routeNo}";
            } else {
                throw new TranslinkException('You must specify a latitude and longitude if you specify a routeNo.');
            }
        }

        return $this->getResponse($url);
    }


    /**
     * Get top estimates for a given stop.
     *
     * @param integer $stopNo - 5 Digit Stop Number (required)
     * @param array $filters - Array of filter information including 'count' (bus count), 'time_frame_min' (time frame in minutes) and 'routeNo'
     * @return stdClass
     * @throws TranslinkException - Invalid stop number or filters specified
     */
    public function getStopEstimates(int $stopNo, array $filters = []) 
    {
        if (!$this->validStopNo($stopNo)) {
            throw new TranslinkException('Invalid stop number. It must be five digits with no leading zeros.');
        }

        $url = self::TRANSLINK_DOMAIN . "/stops/{$stopNo}/estimates";
        $url = $this->appendAPIKey($url);

        $busCount = $this->getFilter($filters, 'count');
        $busCountFiltered = filter_var($busCount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);

        if ((trim($busCount) !== '') && !$busCountFiltered) {
            throw new TranslinkException('Invalid bus count specified. Please try an integer between 1 and 10.');
        }

        $timeFrame = $this->getFilter($filters, 'time_frame_min');
        $timeFrameFiltered = filter_var($timeFrame, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]]);

        if ((trim($timeFrame) !== '') && !$timeFrameFiltered) {
            throw new TranslinkException('Invalid time frame specified. Please try an integer between 1 and 120.');
        }

        $routeNo = $this->getFilter($filters, 'routeNo');

        if ($busCountFiltered !== false) {
            $url .= "&count={$busCountFiltered}";
        }

        if ($timeFrameFiltered !== false) {
            $url .= "&timeframe={$timeFrameFiltered}";
        }

        if ($routeNo !== '') {
            $url .= "&routeNo={$routeNo}";
        }

        return $this->getResponse($url);
    }


    /**
     * Get bus information
     *
     * @param integer $busNo - 4 Digit Bus Number (Optional)
     * @param array $filters - Array of filter information including 'stopNo' (five digit bus stop number) and 'routeNo'
     * @return stdClass
     * @throws TranslinkException for invalid stop number
     */
    public function getBuses(int $busNo = 0, array $filters = []) 
    {
        $url = self::TRANSLINK_DOMAIN . '/buses' . ($busNo > 0 ? "/{$busNo}" : '');
        $url = $this->appendAPIKey($url);

        $stopNo = $this->getFilter($filters, 'stopNo');
        $routeNo = $this->getFilter($filters, 'routeNo');

        if (!empty($stopNo)) {
            if (!$this->validStopNo($stopNo)) {
                throw new TranslinkException('Invalid stop number. It must be five digits.');
            }

            $url .= "&stopNo={$stopNo}";
        }

        if (!empty($routeNo)) {
            $url .= "&routeNo={$routeNo}";
        }

        return $this->getResponse($url);
    }


    /**
     * Get route information
     *
     * @param string $routeNo - Three digit route number (Optional)
     * @param array $filters - Array of filter information including 'stopNo' (five digit stop number)
     * @return stdClass
     * @throws TranslinkException if stop number is invalid
     */
    public function getRoutes(string $routeNo = '', array $filters = []) 
    {
        $url = self::TRANSLINK_DOMAIN . '/routes' . ((trim($routeNo) !== '') ? '/' . trim($routeNo) : '');
        $url = $this->appendAPIKey($url);

        $stopNo = $this->getFilter($filters, 'stopNo');

        if (!empty($stopNo)) {
            if (!$this->validStopNo($stopNo)) {
                throw new TranslinkException('Invalid stop number. It must be five digits.');
            }

            $url .= "&stopNo={$stopNo}";
        }

        return $this->getResponse($url);
    }


    /**
     * Get status of various services
     *
     * @param string $serviceName - Must be one of 'location', 'schedule' or 'all'
     * @return stdClass
     * @throws TranslinkException if service name is not recoginized.
     */
    public function getStatus(string $serviceName) 
    {
        $lowerServiceName = strtolower($serviceName);
        
        if (!$this->validServiceName($lowerServiceName)) {
            throw new TranslinkException('Invalid service name. Must be "location", "schedule" or "all".');
        }

        $url = $this->appendAPIKey(self::TRANSLINK_DOMAIN . "/status/{$lowerServiceName}");

        return $this->getResponse($url);
    }


    /**
     * Appends API key onto a given URL
     *
     * @param string $url - URL to append key parameter to.
     * @return string 
     */
    private function appendAPIKey(string $url) 
    {
        return $url . "?apikey={$this->apiKey}";
    }


    /**
     * Validates that a stop number is five digits long with no leading zeros.
     *
     * @param integer $stopNo - Five Digit Stop Number
     * @return bool
     */
    private function validStopNo(int $stopNo) 
    {
        return preg_match('/^\d{5}$/', $stopNo) === 1;
    }


    /**
     * Validates that a radius is in range of 1 to DEFAULT_STOP_MAX_RADIUS
     *
     * @param mixed $radius
     * @return int|false Integer if the radius is valid, false otherwise
     */
    private function validRadius($radius) 
    {
        return filter_var($radius, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => self::DEFAULT_STOP_MAX_RADIUS]]);
    }


    /**
     * Validates that service name is 'location', 'schedule' or 'all
     *
     * @param string $serviceName
     * @return boolean
     */
    private function validServiceName(string $serviceName) 
    {
        $serviceNameLower = strtolower($serviceName);

        return in_array($serviceNameLower, ['location', 'schedule', 'all']);
    }


    /**
     * Validates if latitude and longitude are valid
     *
     * @param mixed $lat - Latitude to test (between -90.0 and 90.0)
     * @param mixed $long - Longitude to test (between -180.0 and 180.0)
     * @return boolean
     * @throws TranslinkException If lat or long are not in proper float ranges
     */
    private function isValidLatAndLong($lat, $long) 
    {
        if (empty($lat) && empty($long)) {
            return false;
        }

        $filteredLat = filter_var($lat, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => -90.0, 'max_range' => 90.0]]);
        $filteredLong = filter_var($long, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => -180.0, 'max_range' => 180.0]]);
    
        if ($filteredLat === false) {
            throw new TranslinkException('Invalid latitude provided. Make sure it is between -90.0 and 90.0 and try again.');
        }

        if ($filteredLong === false) {
            throw new TranslinkException('Invalid longitude provided. Make sure it is between -180.0 and 180.0 and try again.');
        }

        return true;
    }


    /**
     * Calls Translink API and builds a stdClass object response
     *
     * @param string $url - API URL to call
     * @return stdClass|null
     * @throws TranslinkException if response contains an error message from the API
     */
    private function getResponse(string $url) 
    {
        $response = CurlRequests::get($url, ['Accept' => 'application/json']);

        if ($response !== false) {
            $content = json_decode($response->content);

            if (is_object($content) && property_exists($content, 'Code')) {
                throw new TranslinkException($content->Message, $content->Code);
            }

            return $response;
        }

        return null;
    }


    /**
     * Get a specified filter from the filter array
     *
     * @param array $filters - List of filters specified
     * @param string $filter_key - Key of a filter in the array
     * @return mixed Empty string if key doesn't exist or is empty otherwise value of filter
     */
    private function getFilter(array $filters, string $filter_key) 
    {
        if (!array_key_exists($filter_key, $filters) || trim($filters[$filter_key]) === '') {
            return '';
        } 

        return $filters[$filter_key];
    }
}
