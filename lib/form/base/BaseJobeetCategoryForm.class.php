<?php

/**
 * JobeetCategory form base class.
 *
 * @method JobeetCategory getObject() Returns the current form's model object
 *
 * @package    jobeet
 * @subpackage form
 * @author     Your name here
 */
abstract class BaseJobeetCategoryForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'name'                           => new sfWidgetFormInputText(),
      'slug'                           => new sfWidgetFormInputText(),
      'jobeet_category_affiliate_list' => new sfWidgetFormPropelChoice(array('multiple' => true, 'model' => 'JobeetAffiliate')),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorChoice(array('choices' => array($this->getObject()->getId()), 'empty_value' => $this->getObject()->getId(), 'required' => false)),
      'name'                           => new sfValidatorString(array('max_length' => 255)),
      'slug'                           => new sfValidatorString(array('max_length' => 255)),
      'jobeet_category_affiliate_list' => new sfValidatorPropelChoice(array('multiple' => true, 'model' => 'JobeetAffiliate', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorPropelUnique(array('model' => 'JobeetCategory', 'column' => array('slug')))
    );

    $this->widgetSchema->setNameFormat('jobeet_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'JobeetCategory';
  }


  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['jobeet_category_affiliate_list']))
    {
      $values = array();
      foreach ($this->object->getJobeetCategoryAffiliates() as $obj)
      {
        $values[] = $obj->getAffiliateId();
      }

      $this->setDefault('jobeet_category_affiliate_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveJobeetCategoryAffiliateList($con);
  }

  public function saveJobeetCategoryAffiliateList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['jobeet_category_affiliate_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(JobeetCategoryAffiliatePeer::CATEGORY_ID, $this->object->getPrimaryKey());
    JobeetCategoryAffiliatePeer::doDelete($c, $con);

    $values = $this->getValue('jobeet_category_affiliate_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new JobeetCategoryAffiliate();
        $obj->setCategoryId($this->object->getPrimaryKey());
        $obj->setAffiliateId($value);
        $obj->save();
      }
    }
  }

}
