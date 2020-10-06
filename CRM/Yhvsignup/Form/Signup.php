<?php
print_R($db);exit;
use CRM_Yhvsignup_ExtensionUtil as E;

// Alias Editor classes so they are easy to use
use
  DataTables\Editor,
  DataTables\Editor\Field,
  DataTables\Editor\Format,
  DataTables\Editor\Mjoin,
  DataTables\Editor\Options,
  DataTables\Editor\Upload,
  DataTables\Editor\Validate;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Yhvsignup_Form_Signup extends CRM_Core_Form {
  public function buildQuickForm() {


    $editor = Editor::inst( $db, 'staff' )
      ->fields(
        Field::inst( 'first_name' ),
        Field::inst( 'last_name' ),
        Field::inst( 'position' ),
        Field::inst( 'email' ),
        Field::inst( 'office' )
      )
      ->process( $_POST )
      ->json();

    //CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.yhvsignup', 'js/signup.js');
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    parent::postProcess();
  }

}
