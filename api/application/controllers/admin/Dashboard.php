<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends AdminController
{

  /**
   * @var Google_Service_AnalyticsReporting
   */
  private $gapi;

  private $error;

	public function __construct()
	{
		parent::__construct();
        $this->load->library('Api_Google');	
        
        $this->gapi = new Api_Google(get_option('google_view_id'));
    }   
     

    /* This is admin dashboard view */
    public function index()
    {
        $dimensions = array(
            'deviceCategory', 
            //'browser', 
        );  
        
        $metrics = array(
            'activeUsers',
            //'newUsers',
        );          

        $data = array(
            'start_date' => null,
            'end_date' => null,
            'metrics' => $metrics,
            'dimensions' => $dimensions,
        );  

        $data = $this->gapi->requestReportData($data);

        $response = array();


        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));         
    }    

    
    public function getDateRange($start_date = null, $end_date = null)
    {
        $response = $this->gapi->createDateRange($start_date,$end_date);
                
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response)); 
    }   
    
    public function getReportData()
    {

        $formdata = json_decode(file_get_contents('php://input'), true);

        if(!empty($formdata)) {
            $start_date 	    = $formdata['start_date'];
            $end_date 	        = $formdata['end_date'];
            $metrics 	        = $formdata['metrics'];
            $dimensions 	    = $formdata['dimensions'];

            $data = array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'metrics' => $metrics,
                'dimensions' => $dimensions,
            );    
        
            $response = $this->gapi->requestReportData($data);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));         
    }

    public function getGranphReportData()
    {

        $formdata = json_decode(file_get_contents('php://input'), true);

        if(!empty($formdata)) {
            $start_date 	    = $formdata['start_date'];
            $end_date 	        = $formdata['end_date'];
            $metrics 	        = $formdata['metrics'];
            $dimensions 	    = $formdata['dimensions'];

            $data = array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'metrics' => $metrics,
                'dimensions' => $dimensions,
            );    
        
            $response = $this->gapi->requestReportData($data);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));         
    }    

    public function getRealtimeReportData()
    {

        $formdata = json_decode(file_get_contents('php://input'), true);

        if(!empty($formdata)) {
            $start_date 	    = $formdata['start_date'];
            $end_date 	        = $formdata['end_date'];
            $metrics 	        = $formdata['metrics'];
            $dimensions 	    = $formdata['dimensions'];

            $data = array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'metrics' => $metrics,
                'dimensions' => $dimensions,
            );    
        
            $response = $this->gapi->requestRealtimeReportData($data);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));         
    }    

    public function getReportMetricData()
    {
        $formdata = json_decode(file_get_contents('php://input'), true);

        if(!empty($formdata)) {
            $start_date 	    = $formdata['start_date'];
            $end_date 	        = $formdata['end_date'];
            $metrics 	        = $formdata['metrics'];

            $data = array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'metrics' => $metrics,
            );    
        
            $response = $this->gapi->requestDataMetric($data);
        }    

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response)); 
    }
    
}