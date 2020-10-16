<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Filtershifts API
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
function civicrm_api3_yhvsignup_Filtershifts($params) {
  if (!empty($params)) {
    $job = CRM_Yhvrequestform_Utils::getCustomFieldID('Job', VOLUNTEERING_CUSTOM);
    $location = CRM_Yhvrequestform_Utils::getCustomFieldID('Location', VOLUNTEERING_CUSTOM);
    $program = CRM_Yhvrequestform_Utils::getCustomFieldID('Program', VOLUNTEERING_CUSTOM);
    $division = CRM_Yhvrequestform_Utils::getCustomFieldID('Division', VOLUNTEERING_CUSTOM);
    $fieldsToReturn = [
      "target_contact_id",
      "status_id",
      "id",
      $job,
      $location,
      $program,
      $division,
      "activity_date_time",
      "duration",
    ];
    $searchParams = [
      'sequential' => 1,
      'return' => $fieldsToReturn,
      'target_contact_id' => $params['cid'],
      'status_id' => CRM_Utils_Array::value('Status', $params),
      $job => CRM_Utils_Array::value('Job', $params),
      $location => CRM_Utils_Array::value('Location', $params),
      $program => CRM_Utils_Array::value('Program', $params),
      $division => CRM_Utils_Array::value('Division', $params),
      'activity_date' => CRM_Utils_Array::value('Date', $params),
      'activity_type_id' => $params['activity_type_id'],
    ];
    $options = ["limit" => 0, "sort" => "activity_date_time ASC"];
    $call = wpcmrf_api('Activity', 'get', $searchParams, $options, CMRF_PROFILE_ID);
    if ($call->getStatus() == \CMRF\Core\Call::STATUS_FAILED) {
      throw new API_Exception('There was a problem fetching the volunteer information', 'volunteer_error');
    }
    $activities = $call->getReply();
    $returnValues = [];
    if ($activities['count'] > 0) {
      foreach ($activities['values'] as $activity) {
        $returnValues[] = [
          'ID' => (int) $activity['id'],
          'Contact ID' => (int) $activity['target_contact_id'][0],
          'Job' => $activity[$job],
          'Division' => $activity[$division],
          'Program' => $activity[$program],
          'Location' => $activity[$location],
          'Volunteer Hours' => (float) $activity["duration"],
          'Date' => date('Y-m-d', strtotime($activity['activity_date_time'])),
          'Start Time' => date('h:i A', strtotime($activity['activity_date_time'])),
          'Status' => "Scheduled",
        ];
      }
    }
    return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Filtershifts');
  }
  else {
    throw new API_Exception('There was a problem fetching the volunteer information', 'volunteer_error');
  }
}
