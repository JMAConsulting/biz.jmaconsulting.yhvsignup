<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Doaction API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_yhvsignup_Doaction($params) {
  $returnValues = [];

  if ($params['actionmethod'] == 'insert') {
    $params = array_filter($params);

    if (!empty($params)) {
      $options = [];
      // Format params for formProcessor.
      $date = date('Ymd', strtotime($params['Date']));
      $time = date('His', strtotime($params['Start_Time']));
      $processorParams = [
        'contact_id' => $params['contact_id'],
        'date' => date('YmdHis', strtotime("$date $time")),
        'job' => $params['Job'],
        'location' => $params['Location'],
        'division' => $params['Division'],
        'program' => $params['Program'],
        'status' => $params['Status'],
        'volunteer_hours' => $params['Volunteer_Hours'],
      ];
      $call = wpcmrf_api('FormProcessor', 'volunteer_signup', $processorParams, $options, CMRF_PROFILE_ID);
      $returnValues = $call->getReply();
    }
  }
  if ($params['actionmethod'] == 'update') {
    if (!empty($params['ID'])) {
      $options = [];
      // Create Params.
      $date = date('Ymd', strtotime($params['Date']));
      $time = date('His', strtotime($params['Start_Time']));
      $processorParams = [
        'id' => $params['ID'],
        'contact_id' => $params['contact_id'],
        'date' => date('YmdHis', strtotime("$date $time")),
        'job' => $params['Job'],
        'location' => $params['Location'],
        'division' => $params['Division'],
        'program' => $params['Program'],
        'status' => $params['Status'],
        'volunteer_hours' => $params['Volunteer_Hours'],
      ];
      $call = wpcmrf_api('FormProcessor', 'volunteer_signup', $processorParams, $options, CMRF_PROFILE_ID);
      $returnValues = $call->getReply();
    }
  }
  if ($params['actionmethod'] == 'search') {
    $params = array_filter($params);
    $params['Status'] = 'Scheduled';
    $params['activity_type_id'] = 'Volunteer';

    $options = [];
    $call = wpcmrf_api('Yhvsignup', 'filtershifts', $params, $options, CMRF_PROFILE_ID);
    $returnValues = $call->getReply();
  }

  if (empty($returnValues['values'])) {
    $returnValues['values'] = [];
  }
  return civicrm_api3_create_success($returnValues['values'], $params, 'Yhvsignup', 'Doaction');
}
