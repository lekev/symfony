<?php
class JobeetTestFunctional extends sfTestFunctional
{
	public function createJob($value = array(), $publish = false)
	{
		 $this->
		  get('/job/new')->
		      click('preview your job', array('job' => array_merge(array(
		        'company'      => 'Sensio Labs',
		        'url'          => 'http://www.sensio.com/',
		        'position'     => 'Developer',
		        'location'     => 'Atlanta, USA',
		        'description'  => 'You will work with symfony to develop websites for our customers.',
		        'how_to_apply' => 'Send me an email',
		        'email'        => 'for.a.job@example.com',
		        'is_public'    => false,
		      ), $value)))->
		      followRedirect()
		    ;
		if($publish)
		{
			$this->click('Publish', array(), array('method'=>'put','_with_csrf'=>true))->
			followRedirect();
		}
		
		return $this;
		
	}
	
	public function getJobByPosition($position)
	{
		$criteria = new Criteria();
		$criteria->add(JobeetJobPeer::POSITION, $position);
		
		return JobeetJobPeer::doSelectOne($criteria);
	}
	
	public function getMostRecentProgrammingJob()
	{
		$criteria = new Criteria();
		$criteria->add(JobeetCategoryPeer::SLUG,'programming');
		$category = JobeetCategoryPeer::doSelectOne($criteria);

		$criteria = new Criteria();
		$criteria->add(JobeetJobPeer::EXPIRES_AT,time(), Criteria::GREATER_THAN);
		$criteria->add(JobeetJobPeer::CATEGORY_ID,$category->getId());
		$criteria->addDescendingOrderByColumn(JobeetJobPeer::CREATED_AT);
		
		return JobeetJobPeer::doSelectOne($criteria);
	}
	
	public function getExpiredJob()
	{
		$criteria = new Criteria();
		$criteria->add(JobeetJobPeer::EXPIRES_AT,time(),Criteria::LESS_THAN);
		
		return JobeetJobPeer::doSelectOne($criteria);
	}
	
	public function loadData()
	{
		$loader = new sfPropelData();
		$loader->loadData(sfConfig::get('sf_test_dir'."/fixtures"));
		
		return $this;
	}
	
	
}

?>