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

  if (!empty($params['actionmethod']) && $params['actionmethod'] == 'insert') {
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
      $processorParams['funder'] = getFunder($params);
      $call = wpcmrf_api('FormProcessor', 'volunteer_signup', $processorParams, $options, CMRF_PROFILE_ID);
      $returnValues = $call->getReply();
    }
  }
  if (!empty($params['actionmethod']) && $params['actionmethod'] == 'update') {
    if (!empty($params['ID'])) {
      $options = [];
      // Create Params.
      $processorParams = [
        'id' => $params['ID'],
        'contact_id' => $params['contact_id'],
        'job' => $params['Job'],
        'location' => $params['Location'],
        'division' => $params['Division'],
        'program' => $params['Program'],
        'status' => $params['Status'],
        'volunteer_hours' => $params['Volunteer_Hours'],
      ];

      $processorParams['funder'] = getFunder($params);

      if (!empty($params['Date'])) {
        $date = date('Ymd', strtotime($params['Date']));
      }
      if (!empty($params['Start_Time'])) {
        $time = date('His', strtotime($params['Start_Time']));
      }
      if (!empty($date) && !empty($time)) {
        $processorParams['date'] = date('YmdHis', strtotime("$date $time"));
      }
      elseif (!empty($date) && empty($time)) {
        $processorParams['date'] = date('YmdHis', strtotime("$date"));
      }
      $call = wpcmrf_api('FormProcessor', 'volunteer_signup', $processorParams, $options, CMRF_PROFILE_ID);
      $returnValues = $call->getReply();
    }
  }
  if (!empty($params['actionmethod']) && $params['actionmethod'] == 'search') {
    $params = array_filter($params);
    $params['Status'] = 'Scheduled';
    $params['activity_type_id'] = 'Volunteer';

    $options = [];
    $call = wpcmrf_api('Yhvsignup', 'filtershifts', $params, $options, CMRF_PROFILE_ID);
    $returnValues = $call->getReply();
  }
  if (!empty($params['batchupdate'])) {
    foreach ($params['batchupdate'] as $updateParams) {
      $options = [];
      $processorParams = [
        'id' => $updateParams['ID'],
        'contact_id' => (int) $params['cid'],
        'job' => $updateParams['Job'],
        'location' => $updateParams['Location'],
        'division' => $updateParams['Division'],
        'program' => $updateParams['Program'],
        'status' => $updateParams['Status'],
        'volunteer_hours' => $updateParams['Volunteer_Hours'],
      ];

      if (!empty($updateParams['Date'])) {
        $date = date('Ymd', strtotime($updateParams['Date']));
      }
      if (!empty($updateParams['Start_Time'])) {
        $time = date('His', strtotime($updateParams['Start_Time']));
      }
      if (!empty($date) && !empty($time)) {
        $processorParams['date'] = date('YmdHis', strtotime("$date $time"));
      }
      elseif (!empty($date) && empty($time)) {
        $processorParams['date'] = date('YmdHis', strtotime("$date"));
      } 
      $call = wpcmrf_api('FormProcessor', 'volunteer_signup', $processorParams, $options, CMRF_PROFILE_ID);
      $d = $call->getReply();
    }
  }

//  $returnValues['values'] = $params;

  if (empty($returnValues['values'])) {
    $returnValues['values'] = [];
  }
  return civicrm_api3_create_success($returnValues['values'], $params, 'Yhvsignup', 'Doaction');
}

function getFunder($params) {
  $values = [
    'location' => $params['Location'],
    'division' => $params['Division'],
  ];
  // Check first if we have a match for the program.
  $lookup = CRM_Core_DAO::singleValueQuery("SELECT Program FROM civicrm_volunteer_lookup WHERE Location = %1 AND Division = %2", [1 => [$params['Location'], 'String'], 2 => [$params['Division'], 'String']]);
  if ($lookup == 'Any') {
    $values['program'] = $lookup;
  }
  else {
    $values['program'] = $params['Program'];
  }
  return CRM_Yhvrequestform_Utils::getFunder($values);
}
