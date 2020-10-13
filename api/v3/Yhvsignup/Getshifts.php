<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Getshifts API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_yhvsignup_Getshifts_spec(&$spec) {
  $spec['contact_id']['api.required'] = 1;
}

/**
 * Yhvsignup.Getshifts API
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
function civicrm_api3_yhvsignup_Getshifts($params) {
  if (array_key_exists('contact_id', $params)) {
    $job = CRM_Yhvrequestform_Utils::getCustomFieldID('Job', VOLUNTEERING_CUSTOM);
    $location = CRM_Yhvrequestform_Utils::getCustomFieldID('Location', VOLUNTEERING_CUSTOM);
    $program = CRM_Yhvrequestform_Utils::getCustomFieldID('Program', VOLUNTEERING_CUSTOM);
    $division = CRM_Yhvrequestform_Utils::getCustomFieldID('Division', VOLUNTEERING_CUSTOM);
    $hours = CRM_Yhvrequestform_Utils::getCustomFieldID('Work_Hours', VOLUNTEERING_CUSTOM);
    $fieldsToReturn = [
      "target_contact_id",
      "status_id",
      "id",
      $job,
      $location,
      $program,
      $division,
      $hours,
      "activity_date_time",
      "duration",
    ];
    $activityParams = [
      'sequential' => 1,
      'return' => $fieldsToReturn,
      'target_contact_id' => $params['contact_id'],
      'activity_type_id' => "Volunteer",
      'status_id' => ['IN' => ["Scheduled"]],
    ];
    $options = ['limit' => 0, "sort" => "activity_date_time ASC"];
    $call = wpcmrf_api('Activity', 'get', $activityParams, $options, CMRF_PROFILE_ID);

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
          'Volunteer Hours' => (float) $activity['duration'],
          'Date' => date('Y-m-d', strtotime($activity['activity_date_time'])),
          'Start Time' => date('h:i A', strtotime($activity['activity_date_time'])),
          'Status' => "Scheduled",
        ];
      }
    }
    return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Getshifts');
  }
  else {
    throw new API_Exception('Contact ID is required', 'contact_unspecified');
  }
}
