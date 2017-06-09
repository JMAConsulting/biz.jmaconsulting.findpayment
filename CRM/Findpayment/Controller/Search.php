<?php

class CRM_Findpayment_Controller_Search extends CRM_Core_Controller {

  /**
   * Class constructor.
   *
   * @param string $title
   * @param bool|int $action
   * @param bool $modal
   */
  public function __construct($title = NULL, $action = CRM_Core_Action::NONE, $modal = TRUE) {

    parent::__construct($title, $modal);
    $this->_stateMachine = new CRM_Findpayment_StateMachine_Search($this, $action);
    $this->addPages($this->_stateMachine, $action);
    $this->addActions();
  }

}
