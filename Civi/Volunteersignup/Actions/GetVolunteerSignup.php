<?php
/**
 * Gets volunteer activities of a contact.
 *
 * Returns the activity details.
 *
 */

namespace Civi\VolunteerSignup\Actions;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_Yhvsignup_ExtensionUtil as E;

class GetVolunteerSignup extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $bag = new SpecificationBag();
    $bag->addSpecification(new OptionGroupSpecification('activity_type_id', 'activity_type', E::ts('Activity Type'), false, null, true));
    $bag->addSpecification(new OptionGroupSpecification('status_id', 'activity_status', E::ts('Activity Status'), false, null, true));
    $bag->addSpecification(new Specification('error', 'Boolean', E::ts('Error on no activity found'), false, false));
    return $bag;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Contact ID'), true),
    ]);
    return $bag;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    $bag->addSpecification(new Specification('activity_id', 'Integer', E::ts('Activity ID'), false, null, null, false, true));
    $bag->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), false, null, null, false, true));
    $bag->addSpecification(new Specification('status_id', 'Integer', E::ts('Status ID'), false, null, null, false, true));
    $bag->addSpecification(new Specification('location', 'Text', E::ts('Location'), false, null, null, false, true));
    $bag->addSpecification(new Specification('division', 'Text', E::ts('Division'), false, null, null, false, true));

    return $bag;
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $id = $parameters->getParameter('id');
    $activity_type_ids = $this->configuration->getParameter('activity_type_id');
    $status_ids = $this->configuration->getParameter('status_id');
    $error = $this->configuration->getParameter('error');

    $activities = civicrm_api3('Activity', 'get', [
      'sequential' => 1,
      'target_contact_id' => $id,
      'activity_type_id' => $activity_type_ids[0],
      'status_id' => $status_ids[0],
    ]);

    if ($error && empty($activities['values'])) {
      throw new ExecutionException(E::ts('Could not find an activity'));
    }
    else {
      foreach($activities['values'] as $activity) {
        $activityIds[] = (int) $activity['id'];
        $contactIds[] = (int) $activity['target_contact_id'];
        $locations[] = $activity[\CRM_Yhvrequestform_Utils::getCustomFieldID('Location', VOLUNTEERING_CUSTOM)];
        $divisions[] = $activity[\CRM_Yhvrequestform_Utils::getCustomFieldID('Division', VOLUNTEERING_CUSTOM)];
        $programs[] = $activity[\CRM_Yhvrequestform_Utils::getCustomFieldID('Program', VOLUNTEERING_CUSTOM)];

       /* $returnValues[] = [
          //'Job' => $activity[CRM_Yhvrequestform_Utils::getCustomFieldID('Job', VOLUNTEERING_CUSTOM)],
          'ID' => (int) $activity['id'],
          'Contact ID' => (int) $activity['target_contact_id'],
          'Division' => $activity[CRM_Yhvrequestform_Utils::getCustomFieldID('Division', VOLUNTEERING_CUSTOM)],
          'Program' => $activity[CRM_Yhvrequestform_Utils::getCustomFieldID('Program', VOLUNTEERING_CUSTOM)],
          'Location' => $activity[CRM_Yhvrequestform_Utils::getCustomFieldID('Location', VOLUNTEERING_CUSTOM)],
          'Work Hours' => (int) $activity[CRM_Yhvrequestform_Utils::getCustomFieldID('Work_Hours', VOLUNTEERING_CUSTOM)],
          'Start Date' => date('Y-m-d', strtotime($activity['activity_date_time'])),
          'Status' => (int) $activity['status_id'],
        ];*/
      }

    }
    $output->setParameter('activity_id', serialize($activityIds));
    $output->setParameter('contact_id', serialize($contactIds));
  }
}
