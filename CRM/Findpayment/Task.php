<?php

class CRM_Findpayment_Task {
  const EXPORT_PAYMENTS = 1;

  /**
   * The task array
   *
   * @var array
   */
  static $_tasks = NULL;

  /**
   * These tasks are the core set of tasks that the user can perform
   * on a contact / group of contacts
   *
   * @return array
   *   the set of tasks for a group of contacts
   */
  public static function tasks() {
    if (!(self::$_tasks)) {
      self::$_tasks = array(
        1 => array(
          'title' => ts('Export Payments'),
          'class' => 'CRM_Findpayment_Form_Task_Export',
          'result' => FALSE,
        ),
      );
    }

    return self::$_tasks;
  }

  /**
   * These tasks are the core set of task titles
   * on contributors
   *
   * @return array
   *   the set of task titles
   */
  public static function &taskTitles() {
    self::tasks();
    $titles = array();
    foreach (self::$_tasks as $id => $value) {
      $titles[$id] = $value['title'];
    }
    return $titles;
  }

  /**
   * These tasks are the core set of tasks that the user can perform
   * on contributors
   *
   * @param int $value
   *
   * @return array
   *   the set of tasks for a group of contributors
   */
  public static function getTask($value) {
    self::tasks();
    if (!$value || !CRM_Utils_Array::value($value, self::$_tasks)) {
      // make the print task by default
      $value = 1;
    }
    // this is possible since hooks can inject a task
    // CRM-13697
    if (!isset(self::$_tasks[$value]['result'])) {
      self::$_tasks[$value]['result'] = NULL;
    }
    return array(
      self::$_tasks[$value]['class'],
      self::$_tasks[$value]['result'],
    );
  }

}
