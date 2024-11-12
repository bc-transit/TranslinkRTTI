# About TranslinkRTTI API Wrapper

This is a simple wrapper that wraps the Vancouver based [Translink RTTI API](https://developer.translink.ca/ServicesRtti/ApiReference) service. This wrapper can be used for creating basic Translink based apps and retrieving information from the Translink system. It requires that you apply for your own API Key to use. 

- [Register for API Key](https://developer.translink.ca/Account/Register).
- [RTTI Open API - Overview](https://developer.translink.ca/ServicesRtti).
- [RTTI Open API - API Reference](https://developer.translink.ca/ServicesRtti/ApiReference)

## Usage

First create an instance of the TranslinkRTTI class and then call one of the methods.

```php
$translinkRTTI = new TranslinkRTTI('API_KEY');

// Call information about stop 55612 which is the Surrey Central Station Bay 4
$surreyCentral = $translinkRTTI->getStops(55612);
var_dump($surreyCentral);
```

Output will be a standard class object with a status code and content member. The content will also be a JSON string.

```php
object(stdClass)#4 (2) {
  ["status_code"]=> int(200)
  ["content"]=> string(243) "{"StopNo":55612,"Name":"SURREY CENTRAL STN BAY 4       ","BayNo":"4  ","City":"SURREY","OnStreet":"SURREY CENTRAL STN","AtStreet":"BAY 4","Latitude":49.188850,"Longitude":-122.849361,"WheelchairAccess":1,"Distance":-1,"Routes":"501, 509, N19"}"
}
```

There are five methods supported:

- getStops(int $stopNo = 0, array $filters = []) 
    - Filters include 'lat', 'long', 'radius' and 'routeNo'
- getStopEstimates(int $stopNo, array $filters = [])
    - Filters include 'count', 'time_frame_min' and 'routeNo'
- getBuses(int $busNo = 0, array $filters = []) 
    - Filters include 'stopNo' and 'routeNo'
- getRoutes(string $routeNo = '', array $filters = []) 
    - Filters include 'stopNo'
- getStatus(string $serviceName)

For more information on usage, see the source code and the related doc strings.

## License

The TranslinkRTTI wrapper class is software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Questions or to Report An Issue

If you would like to report an issue with this code, please [email us](mailto:github@coderslexicon.com).


------

# Link to the rttiapi code

```
$ grep -r rttiapi *
TranslinkRTTI.php:    const TRANSLINK_DOMAIN = 'https://api.translink.ca/rttiapi/v1';

```


