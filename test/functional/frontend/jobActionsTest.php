<?php

include(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new JobeetTestFunctional(new sfBrowser());

$browser->loadData();
$browser->setTester('propel','sfTesterPropel');

$browser->info('1 - The homepage')->
get('/')->
with('request')->begin()->
isParameter('module','job')->
isParameter('action','index')->
end()->
with('response')->begin()->
info('	1.1 - Expired jobs are not listed')->
checkElement('.job td.position:contains("expired")',false)->
end()
;

$max = sfConfig::get('app_max_jobs_on_homepage');

$browser->info('2 - The homePage')->
get('/')->
info(sprintf('	1.2 - Only %s jobs are listed for a category', $max))->
with('response')->
checkElement('.category_programming tr', $max)
;

$browser->info('3 - The homepage')->
get('/')->
info('	1.3 - A category has a link to the cateogry page only if too many jobs')->
with('response')->begin()->
checkElement('.category_design .more_jobs',false)->
checkElement('.category_programming .more_jobs')->
end();

$browser->info('1 - The homepage')->
get('/')->
info('	1.4 - Job are sorted by date')->
with('response')->
checkElement(sprintf('.category_programming tr:first a[href*="/%d/"]',$browser->getMostRecentProgrammingJob()->getId()))
;

$job = $browser->getMostRecentProgrammingJob();

$browser->info('2 - the job page')->
get('/')->
info(' 2.1 - each job on the homepage is clickable and give detailed information')->
click('Web Developer', array(),array('position =>1'))->
with('request')->begin()->
isParameter('module','job')->
isParameter('action','show')->
isParameter('company_slug',$job->getCompanySlug())->
isParameter('location_slug',$job->getLocationSlug())->
isParameter('position_slug',$job->getPositionSlug())->
isParameter('id',$job->getId())->
end()->

info('	2.2 - A non-ecistence job forward the user to a 404')->
get('/job/foo-inc/milano-italy/0/painter')->
with('response')->isStatusCode(404)->

info('	2.3 - An expired job page forward the user to a 404')->
get(sprintf('/job/sensio-labs/paris-france/%d/web-developer',$browser->getExpiredJob()->getId()))->
with('response')->isStatusCode(404)
;

$browser->info('3 - Post a Job page')->
info('	3.1 - submit a Job')->

get('/job/new')->
with('request')->begin()->
isParameter('module','job')->
isParameter('action','new')->
end()->

click('preview your job', array('job' => array(
	'company'=>'Sensio Labs',
	'url' =>'http:://www.sensio.com',
	'logo'=> sfConfig::get('sf_upload_dir').'/jobs/sensio_labs.gif',
	'position'=>'Developer',
	'location'=>'Atlanta, USA',
	'description'=>'You will with symfony to develop website ...',
	'how_to_apply'=>'send me an Email',
	'email'=>'for.a.job@example.com',
	'is_public'=>false,
)))->
with('request')->begin()->
isParameter('module','job')->
isParameter('action','create')->
end()->

with('form')->begin()->
hasErrors(false)->
end()->

with('response')->isRedirected()->
followRedirect()->

with('request')->begin()->
isParameter('module','job')->
isParameter('action','show')->
end()->

with('propel')->begin()->
check('JobeetJob',array(
	'location' => 'Atlanta, USA',
	'is_activated'=>false,
	'is_public' => false,
))->
end();

$browser->info('	3.2 - Submit a Job with invalid values')->
  get('/job/new')->
  click('preview your job', array('job' => array(
    'company'      => 'Sensio Labs',
    'position'     => 'Developer',
    'location'     => 'Atlanta, USA',
    'email'        => 'not.an.email',
  )))->

with('form')->begin()->
hasErrors(3)->
isError('description', 'required')->
isError('how_to_apply', 'required')->
isError('email','invalid')->
end();

$browser->info('	3.3 - On the preview page , you can publish the job')->
createJob(array('position'=>'F001'))->
click('Publish', array(),array('method'=>'put','_with_csrf'=>true))->
with('propel')->begin()->
check('JobeetJob',array(
	'position' => 'F001',
	'is_activated'=>true
))->
end()
;

$browser->info('	3.4 - On the preview page, you can delete the job')->
createJob(array('position'=>'F002'))->
click('Delete', array(), array('method'=>'delete','_with_csrf'=>true))->

with('propel')->begin()->
check('JobeetJob', array('position'=>'F002'), false)->
end();

$browser->info('	3.5 - when a job is published, it cannot be edited')->
createJob(array('position'=>'F003'),true)->
get(sprintf('/job/%s/edit', $browser->getJobByposition('F003')->getToken()))->
with('response')->begin()->
isStatusCode(404)->
end();

$browser->info('	3.6 - A job validity cannot be extended before the job expires soon')->
createJob(array('position'=>'F004'),true)->
call(sprintf('/job/%s/extend',$browser->getJobByposition('F004')->getToken()),'put',array('_with_csrf'=>true))->
with('response')->begin()->
isStatusCode(404)->
end();

$browser->info('	3.7 - A jobe validty can be extended when the job expiresoon')->
createJob(array('position'=>'F005'), true);

$job =$browser->getJobByposition('F005');
$job->setExpiresAt(time());
$job->save();

$browser->
call(sprintf('job/%s/extend',$job->getToken()),'put', array('_with_csrf' => true))->
with('response')->isRedirected();

$job->reload();
$browser->test()->is(
	$job->getExpiresAt('y/m/d'),date('y/m/d', time()+86400 *sfConfig::get('app_active_days'))
);

