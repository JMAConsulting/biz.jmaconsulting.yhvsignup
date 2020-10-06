<?php
use CRM_Yhvsignup_ExtensionUtil as E;

class CRM_Yhvsignup_Page_SearchSignup extends CRM_Core_Page {

  public function run() {
    $vals = $_POST;
    $vals = array_filter($vals);
    $vals['Status'] = 'Scheduled';
    $vals['activity_type_id'] = 'Volunteer';

    if (!empty($vals)) {
      $options = [];
      $call = wpcmrf_api('Yhvsignup', 'filtershifts', $vals, $options, CMRF_PROFILE_ID);
      $filteredShifts = $call->getReply();

      CRM_Utils_JSON::output($filteredShifts['values']);
    }
  }

}
