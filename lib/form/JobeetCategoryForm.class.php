<?php

/**
 * JobeetCategory form.
 *
 * @package    jobeet
 * @subpackage form
 * @author     Your name here
 */
class JobeetCategoryForm extends BaseJobeetCategoryForm
{
  public function configure()
  {
	unset($this['jobeet_category_affiliate_list']);
  }
}
