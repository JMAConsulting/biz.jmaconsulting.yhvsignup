<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Recurringshifts API
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
function civicrm_api3_yhvsignup_Recurringshifts($params) {
  // Get all volunteer activities from the day before.
  $sql = "SELECT a.id, v.type_of_request_85 AS type, a.activity_date_time FROM civicrm_activity a
          INNER JOIN civicrm_value_volunteering_12 v ON v.entity_id = a.id
          WHERE a.activity_type_id IN (55)
          AND v.type_of_request_85 LIKE '%recurring%'
          AND DATE(a.activity_date_time) = DATE(NOW() - INTERVAL 1 DAY)";
  $activities = CRM_Core_DAO::executeQuery($sql)->fetchAll();
  /* Comment out to test.
  $activities = [
    [
      'id' => 1,
      'type' => 'monthly_recurring',
      'activity_date_time' => '2020-10-23',
    ],
  ];
  */
  if (!empty($activities)) {
    foreach ($activities as $activity) {
      $date = getNextDate($activity['activity_date_time'], $activity['type']);
      $originalActivity = civicrm_api3('Activity', 'get', [
        'id' => $activity['id'], // Change to copy from parent activity
      ])['values'];
      // Create new activity.
      unset($originalActivity['id']);
      $originalActivity['activity_date_time'] = $date;
      $originalActivity['status_id'] = "Scheduled";
      civicrm_api3('Activity', 'create', $originalActivity);
    }
  }
  return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Recurringshifts');
}

function getNextDate($date, $type) {
  if ($type == 'monthly_recurring') {
    $counter = [
      1 => 'first',
      2 => 'second',
      3 => 'third',
      4 => 'fourth',
      5 => 'fifth',
    ];
    $week = weekOfMonth($date);
    $day = strtolower(date('l', strtotime($date)));
    $month = strtolower(date('F', strtotime($date . ' + 1 month')));
    $year = date('Y', strtotime($date));
    return date("Ymd", strtotime($counter[$week] . " " . $day . " of " . $month . " " . $year));
  }
  else {
    return date("Ymd", strtotime($date . ' +1 week'));
  }
}

function weekOfMonth($date) {
  $dateArray = explode("-", $date);
  $date = new DateTime();
  $date->setDate($dateArray[0], $dateArray[1], $dateArray[2]);
  return floor((date_format($date, 'j') - 1) / 7) + 1;
}
