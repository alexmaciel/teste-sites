<?php
/**
 * Google Class
 * This class enables you to use the Google Protocol
 *
 * @subpackage Libraries
 * @category   Analytics
 */

defined('BASEPATH') || exit('No direct script access allowed');

const REPORT_REQUESTS_LIMIT = 5;
const METRICS_LIMIT = 10;
const PAGESIZE_LIMIT = 100000;

// Load the Google API PHP Client Library.
include_once(APPPATH . 'vendor/autoload.php');

use Google\Analytics\Data\V1beta\RunReportResponse;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;

use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\ApiCore\ApiException;

class Api_Google
{

    /**
     * @var Google_Client
     */
    private $client;

    private $analytics;

    private $reportingDimension = null;

    private $reportingMetric = null;

    private $property_id;

    private $startDate;

    private $end_date;

    private $metrics;

    private $dimensions;

    private $returnedReports;

    public function __construct(String $property_id = null)
    {
        try {
            $this->property_id = $property_id;
            // Creates and returns the Analytics Reporting service object.
            $this->initializeAnalytics();
        } catch (ApiException $e) {
            // Handle API service exceptions.
            $response = 'Error:' . $e->getMessage();
        }

        return $this->property_id;
    }

    /**
     * @return Google_Service_AnalyticsReporting
     */
    public function initializeAnalytics() 
    {
        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_PATH = APPPATH . 'third_party/google/analytics-sites-kadabra-ea187c359ff6.json';

        if (!file_exists($KEY_FILE_PATH)) {
            throw new \Exception("Invalid Argument: credentials file not found ('$KEY_FILE_PATH' entered)");
        }

        return $this->analytics = new BetaAnalyticsDataClient([
            'credentials' => $KEY_FILE_PATH,
        ]);
    }     

    /**
     * @param $startDate
     * @param $end_date
     */
    public function createDateRange($startDate, $end_date)
    {
        if ($startDate == null) {
            // Use the day that Google Analytics was released (1 Jan 2005).
            $startDate = '30daysAgo';
        } elseif (is_int($startDate)) {
            // Perhaps we are receiving a Unix timestamp.
            $startDate = date('Y-m-d', $startDate);
        }   
        
        if ($end_date == null) {
            $end_date = 'today';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $end_date = $end_date;
        }  
   
        $result = array(
            'start_date' => $startDate,
            'end_date' => $end_date,
        );                   

        return $result;
    }

    /**
     * requestRealtimeReportData
     *
     * simple helper & wrapper of Google Api Client
     *
     * @param $viewId
     * @param $startDate
     * @param $end_date
     * @param array $metrics
     * @param array $dimensions
     * @param array $sorting ( = [ ['fields']=>['sessions','bounceRate',..] , 'order'=>'descending' ] )
     * @param array $filterMetric ( = [ ['metric_name']=>['sessions'] , 'operator'=>'LESS_THAN' , 'comparison_value'=>'100' ] )
     * @param array $filterDimension ( = [ ['dimension_name']=>['sourceMedium'] , 'operator'=>'EXACT' , 'expressions'=>['my_campaign'] ] )
     * @return mixed
     *
     *
     */
    public function requestRealtimeReportData($data) 
    {    
        $data_dimensions = $data['dimensions'];
        $data_metrics = $data['metrics'];       

        //Create the Dimensions object.
        if (!empty($data_dimensions)) {
            $reportingDimensions = [];
            foreach ($data_dimensions as $dimension_value) {
                $dimensionObj = new Dimension();
                $dimensionObj->setName($dimension_value);
                $reportingDimensions[] = $dimensionObj;
            }         
        } 

        // Create the Metrics object.
        if (!empty($data_metrics)) {
            $reportingMetrics = [];
            foreach ($data_metrics as $metric_value) {
                $metric_obj = new Metric();
                $metric_obj->setName($metric_value);
                $reportingMetrics[] = $metric_obj;
            }  
        } 

        if ($data['start_date'] == null) {
            // Use the day that Google Analytics was released (30 days ago).
            $start_date = '30daysAgo';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $start_date = date('Y-m-d', strtotime($data['start_date']));
        } 

        if ($data['end_date'] == null) {
            $end_date = 'today';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $end_date = date('Y-m-d', strtotime($data['end_date']));
        }          

                
        // Create and configure a new client object.
        $report = $this->analytics->runRealtimeReport([
            'property' => 'properties/' . $this->property_id,
            'dateRanges' => [
                new DateRange([
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ]),
            ],
            'dimensions' => $reportingDimensions,            
            'metrics' => $reportingMetrics,
            'orderBys' => [
                new OrderBy([
                    'desc' => $data_dimensions[0],
                    'dimension' => new DimensionOrderBy([
                        'dimension_name' => $data_dimensions[0],
                        'order_type' => Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy\OrderType::NUMERIC
                    ])                    
                ])
            ]           
        ]); 

        return self::_returnedReports($report);         
    }    

    /**
     * requestReportData
     *
     * simple helper & wrapper of Google Api Client
     *
     * @param $viewId
     * @param $startDate
     * @param $end_date
     * @param array $metrics
     * @param array $dimensions
     * @param array $sorting ( = [ ['fields']=>['sessions','bounceRate',..] , 'order'=>'descending' ] )
     * @param array $filterMetric ( = [ ['metric_name']=>['sessions'] , 'operator'=>'LESS_THAN' , 'comparison_value'=>'100' ] )
     * @param array $filterDimension ( = [ ['dimension_name']=>['sourceMedium'] , 'operator'=>'EXACT' , 'expressions'=>['my_campaign'] ] )
     * @return mixed
     *
     * @link https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     * @link https://ga-dev-tools.appspot.com/query-explorer/
     * @link https://developers.google.com/analytics/devguides/reporting/core/v4/quickstart/web-php
     * @link https://developers.google.com/analytics/devguides/reporting/core/v4/samples
     * @link https://github.com/google/google-api-php-client
     *
     */
    public function requestReportData($data) 
    {       

        $data_dimensions = $data['dimensions'];
        $data_metrics = $data['metrics'];       

        //Create the Dimensions object.
        if (!empty($data_dimensions)) {
            $reportingDimensions = [];
            foreach ($data_dimensions as $dimension_value) {
                $dimensionObj = new Dimension();
                $dimensionObj->setName($dimension_value);
                $reportingDimensions[] = $dimensionObj;
            }         
        } 

        // Create the Metrics object.
        if (!empty($data_metrics)) {
            $reportingMetrics = [];
            foreach ($data_metrics as $metric_value) {
                $metric_obj = new Metric();
                $metric_obj->setName($metric_value);
                $reportingMetrics[] = $metric_obj;
            }  
        } 

        if ($data['start_date'] == null) {
            // Use the day that Google Analytics was released (30 days ago).
            $start_date = '30daysAgo';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $start_date = date('Y-m-d', strtotime($data['start_date']));
        } 

        if ($data['end_date'] == null) {
            $end_date = 'today';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $end_date = date('Y-m-d', strtotime($data['end_date']));
        }          

                
        // Create and configure a new client object.
        $report = $this->analytics->runReport([
            'property' => 'properties/' . $this->property_id,
            'dateRanges' => [
                new DateRange([
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ]),
            ],
            'dimensions' => $reportingDimensions,            
            'metrics' => $reportingMetrics,
            'orderBys' => [
                new OrderBy([
                    'desc' => $data_dimensions[0],
                    'dimension' => new DimensionOrderBy([
                        'dimension_name' => $data_dimensions[0],
                        'order_type' => Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy\OrderType::NUMERIC
                    ])                    
                ])
            ]           
        ]); 

        return self::_returnedReports($report);
    }    

    /**
     * @param $property_id
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     *
     * https://ga-dev-tools.appspot.com/query-explorer/
     *
     */
    public function requestDataMetric($data) 
    {
        $data_metrics = $data['metrics'];       

        // Create the Metrics object.
        if (!empty($data_metrics)) {
            $reportingMetrics = [];
            foreach ($data_metrics as $metric_value) {
                $metric_obj = new Metric();
                $metric_obj->setName($metric_value);
                $reportingMetrics[] = $metric_obj;
            }  
        } 

        if ($data['start_date'] == null) {
            // Use the day that Google Analytics was released (30 days ago).
            $start_date = '30daysAgo';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $start_date = date('Y-m-d', strtotime($data['start_date']));
        } 

        if ($data['end_date'] == null) {
            $end_date = 'today';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $end_date = date('Y-m-d', strtotime($data['end_date']));
        }         

        $report = $this->analytics->runReport([
            'property' => 'properties/' . $this->property_id,
            'dateRanges' => [
                new DateRange([
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ]),
            ],           
            'metrics' => $reportingMetrics,
        ]); 

        return self::_returnedDataMetric($report);
    }    
    
    
    public function parseReturnedReports(
        string $startDate=null,
        string $end_date=null, 
        array $metrics, 
        array $dimensions=null,
        $sorting=null,
        $filterMetric=null,
        $filterDimension=null
    ) {       

        //Create the Dimensions object.
        if (!empty($dimensions)) {
            $reportingDimensions = [];
            foreach ($dimensions as $dimension_value) {
                $dimensionObj = new Dimension();
                $dimensionObj->setName($dimension_value);
                $reportingDimensions[] = $dimensionObj;
            }         
        }

        // Create the Metrics object.
        if (!empty($metrics)) {
            $reportingMetrics = [];
            foreach ($metrics as $metric_value) {
                $metric_obj = new Metric();
                $metric_obj->setName($metric_value);
                $reportingMetrics[] = $metric_obj;
            }  
        }   
        
        if ($startDate == null) {
            // Use the day that Google Analytics was released (30 days ago).
            $startDate = '30daysAgo';
        } elseif (is_int($startDate)) {
            // Perhaps we are receiving a Unix timestamp.
            $startDate = date('Y-m-d', $startDate);
        }   
        
        if ($end_date == null) {
            $end_date = 'today';
        } else {
            // Perhaps we are receiving a Unix timestamp.
            $end_date = $end_date;
        }          
                
        // Create and configure a new client object.
        $data = $this->analytics->runReport([
            'property' => 'properties/' . $this->property_id,
            'dateRanges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $end_date,
                ]),
            ],
            'dimensions' => $reportingDimensions,            
            'metrics' => $reportingMetrics,
            /*
            'orderBys' => [
                new OrderBy([
                    'desc' => $dimensions[0],
                    'dimension' => new DimensionOrderBy([
                        'dimension_name' => $dimensions[0],
                        'order_type' => Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy\OrderType::NUMERIC
                    ])                    
                ])
            ]  
            */          
        ]); 

        $report = self::_returnedReports($data);

        return $report;

    }   
    
    /**
     * private _returnedReports function.
     * This function takes in the $data object and reorganizes its structure.
     * The new structure takes the form of:
     * {
     *    'totals': {
     *        'metricItem' => metricValue,
     *        'metricItem' => metricValue,
     *        ...
     *     },
     *     'rows': [
     *         {
     *             'dimensions' => {
     *             'dimensionItem' => dimensionValue,
     *             'dimensionItem' => dimensionValue,
     *             ...
     *             },
     *             'metrics' => {
     *             'metricItem' => metricValue,
     *             'metricItem' => metricValue,
     *             ...
     *             }
     *         }, ...
     *     ]
     * }
     * @param  $data
     * @return array
     */
    private static function _returnedReports($data): array
    {

        $builtReport = [];
        $metricHeaders = [];
        $dimensionHeaders = [];        
        //$batchGetReports = $data->returnedReports;
        //$batchGetCount = count($batchGetReports);

        $dimensionsLookup = []; // keeps track of dimensions and its index for quicker insert.
        $dimensionsIndex = 0;
        //$builtReport['totals'] = ['metrics' => []];
        //$builtReport['rows'] = [];

        /** $metricHeader */
        foreach ($data->getMetricHeaders() as $metricHeader) {
            $metricHeaders[] = $metricHeader;
        }    
        
        /** $dimensionHeader */
        foreach ($data->getDimensionHeaders() as $dimensionHeader) {
            $dimensionHeaders[] = $dimensionHeader->getName();
        }

        /** $row */
        foreach ($data->getRows() as $row) {

            $totalMetricValues = $row->getMetricValues();
            $totalMetricValuesCount = count($totalMetricValues);
            for ($i = 0; $i < $totalMetricValuesCount; $i++) {
                $metricName = $metricHeaders[$i]->getName();
                //$builtReport['totals']['metrics'][$metricName] = $totalMetricValues[$i];
            }
            
            /** $dimensionValue */
            foreach ($row->getDimensionValues() as $idx => $dimensionValue) {
                $dimensions = $row->getDimensionValues();
                $dimensionsCount = count($dimensions);            
                $dimensionsDict = [];
                for ($i = 0; $i < $dimensionsCount; $i++) {
                   //$dimensionsDict[$dimensionHeaders[$i]] = $dimensions[$i]->getValue();
                   $dimensionsDict[$i]['value'] = $dimensions[$i]->getValue();
                }
                // preg_replace( '/\s+/', ' ', $str )
                if($dimensionHeaders[0] === 'country') {
                    $dimensionsDict['flags'] = strtolower(preg_replace('/\s+/', '-', $dimensionValue->getValue()));
                } 
                
                if($dimensionHeaders[0] === 'date') {
                    //date(get_current_date_format(true). ' g:i A', $date)
                    $dimensionsDict['date'] = date('Y-m-d', strtotime($dimensionValue->getValue()));
                }   
            }

            $builtReport[$dimensionsIndex] = [
                'dimensions' => $dimensionsDict,
                'metrics' => []
            ];                  
            /** $metricValue */
            foreach ($row->getMetricValues() as $idx => $metricValue) {
                $metricValues = $row->getMetricValues();
                $metricValuesCount = count($metricValues);             

                for ($i = 0; $i < $metricValuesCount; $i++) {
                    $metricName = $metricHeaders[$i]->getName();
                    /*
                    even though the package sets includeEmptyRows to
                    false, if any one of the metrics in the subreport
                    has a non-zero value, Google will still return
                    all 10 metrics, so if the verbose mode is not
                    enabled, we will trim metrics that have 0 value.
                     */
                    if ($metricValues[$i] !== 0) {
                        $builtReport[$dimensionsIndex]['metrics']['value'] = $metricValues[$i]->getValue();
                        $builtReport[$dimensionsIndex]['metrics']['formatting'] = bd_nice_number($metricValues[$i]->getValue());
                    }
                } 
            }
            
            $dimensionsIndex++;
        }

        return $builtReport;
    }    
    
    /*
    * @param  $data
    * @return array
    */
    private static function _returnedDataMetric($data): array
    {    
        $builtReport = [];   
        $metricHeaders = [];

        $dimensionsIndex = 0;
        $builtReport['totals'] = [];

        /** $metricHeader */
        foreach ($data->getMetricHeaders() as $metricHeader) {
            $metricHeaders[] = $metricHeader;
        }    

        /** $row */
        foreach ($data->getRows() as $row) {

            /** $metricValue */
            foreach ($row->getMetricValues() as $idx => $metricValue) {
                $metricValues = $row->getMetricValues();
                $metricValuesCount = count($metricValues);    

                for ($i = 0; $i < $metricValuesCount; $i++) {
                    $metricName = $metricHeaders[$i]->getName();
                    /*
                    even though the package sets includeEmptyRows to
                    false, if any one of the metrics in the subreport
                    has a non-zero value, Google will still return
                    all 10 metrics, so if the verbose mode is not
                    enabled, we will trim metrics that have 0 value.
                     */
                    if ($metricValues[$i] !== 0) {
                        $builtReport['totals'][$metricName] = bd_nice_number($metricValues[$i]->getValue());
                    }
                }             
            }


            $dimensionsIndex++;
        }   
        
        return $builtReport;
    }

    private function setReturnedReports(array $returnedReports): void
    {
        $this->returnedReports = $returnedReports;
    }    
}