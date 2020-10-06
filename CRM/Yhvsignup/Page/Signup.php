<?php
use CRM_Yhvsignup_ExtensionUtil as E;

class CRM_Yhvsignup_Page_Signup extends CRM_Core_Page {

  public function run() {
    $vals = $_POST;

    if (!empty($vals['ID'])) {
      $options = [];
      // Create Params.
      $params = [
        'id' => $vals['ID'],
        'contact_id' => $vals['contact_id'],
        'date' => date('Y-m-d', strtotime($vals['Date'])),
        'job' => $vals['Job'],
        'location' => $vals['Location'],
        'division' => $vals['Division'],
        'program' => $vals['Program'],
        'status' => $vals['Status'],
        'volunteer_hours' => $vals['Volunteer_Hours'],
      ];
      $call = wpcmrf_api('FormProcessor', 'volunteer_signup', $params, $options, CMRF_PROFILE_ID);
      $call->getReply();
    }
  }

}
