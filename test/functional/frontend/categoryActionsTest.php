<?php

include(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new JobeetTestFunctional(new sfBrowser());

$browser->info('1 - the category page')->
info('	1.1 - Categories on homepage are clickable')->
get('/')->
click('Programming')->
with('request')->begin()->
isParameter('module','category')->
isParameter('action','show')->
isParameter('slug','programming')->
end()->

/*info('debug')->
get('/')->with('response')->
debug()->*/

info('	1.2 - Cateogrie with more than %s jobs also have a "more" link',sfConfig::get('app_max_jobs_on_homepage'))->
get('/')->
click('22')->
with('request')->begin()->
isParameter('module','category')->
isParameter('action','show')->
isParameter('slug','programming')->
end()->

info(sprintf('	1.3 - Only %s job are lister', sfConfig::get('app_max_jobs_on_category')))->
with('response')->checkElement('.jobs tr', sfConfig::get('app_max_jobs_on_category'))->

info('	1.4 - The job listed is paginated')->
with('response')->begin()->
checkElement('.pagination_desc', '/32 jobs/')->
checkElement('.pagination_desc', '#page 1/2#')->
end()->

click('2')->
with('request')->begin()->
isParameter('page',2)->
end()->
with('response')->checkElement('.pagination_desc', '#page 2/2#')
;