(function (Drupal, $) {
  var placeholderSelector = '#dynamic-breadcrumb-placeholder';
  var mainMenuClass = 'site-menu';
  var siteMainMenuSelector = '.site-menu:first';
  var sideMenuSelector = '.side-menu:first';
  var sideMenuClass = 'side-menu';
  /*
  var bodySelectorPathMapping = {
    // each of these keys will be checked with $('body').is(key)
    '.is-page-node-type-something': '[href="/something"]',
  } */
  var template = '<div class="dynamic-breadcrumb">'
  + '<nav class="breadcrumb " role="navigation" aria-labelledby="system-breadcrumb">'
  + '<h2 id="system-breadcrumb" class="visually-hidden">Breadcrumb</h2>'
  + '<ol class="breadcrumb__list">'
  + '  <li itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="breadcrumb__item"><a href="{{home_url}}" itemprop="url"><span itemprop="title">{{home_title}}</span></a><span class="breadcrumb__separator" role="presentation">  &#187; </span></li>'
  + '</ol>'
  + '</nav>'
  + '</div>';
  var breadcrumbItem = '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="breadcrumb__item"><span class="breadcrumb__separator" role="presentation">  &#187; </span></li>';
  function log(msg){
    if (window.console && window.console.log) window.console.log(msg);
  }
  var stripHomeStringFromBCLinks = true;
  function stripHomeString($item){
	  var itemText = $.trim($item.text());
	  var homeString = ' home';
	  var index = itemText.toLowerCase().indexOf(homeString);
	  if (index > -1 && index === itemText.length - homeString.length) 
		  $item.text(itemText.substr(0, itemText.length - homeString.length));
  }
  function generateBreadcrumbs($menuLink, addCurrentPageTitle, menuClass, detectExternalHomeLink){
    //log("generateBreadcrumbs() for link with href: " + $menuLink.attr("href"));
    var bc = [];
    if (!$menuLink || !$menuLink.length) return bc;
	var urls = {}; //associative array of urls already in the breadcrumb trail, to avoid duplicates
	
	var externalHomeHREF = null;
	var $externalHomeLink = null;
	var externalHomeHREFFound = false;
	if(detectExternalHomeLink){
		//we should add the first main menu item
		$externalHomeLink = $menuLink.closest("." + menuClass).find('.tab-wrapper:first').find('a').first();
		if ($externalHomeLink.length){
			externalHomeHREF =  $externalHomeLink.attr('href');
			if (externalHomeHREF === '/') {
				//not really external menu...
				$externalHomeLink = null;
				externalHomeHREF = null;
			}
		}
//					log($externalHome.attr('href'));
	}
	var getParentLI = function($li){
		 $li = $li.parent();
		  if($li.is('li') && !$li.hasClass(menuClass)) return $li;
		 
		 var wayTooManyOuter = 20; //infinite loop preventer - the closest li shouldn't take more than this number of parent() calls
		  while(wayTooManyOuter--) {
			//log("loop 2... wayTooManyOuter=" + wayTooManyOuter);
			  if ($li.hasClass(menuClass)) {
				  log("externalHomeHREF = " + externalHomeHREF + ", externalHomeHREFFound=" + externalHomeHREFFound);
				  if ($externalHomeLink && externalHomeHREF && !externalHomeHREFFound){
					  $li = $externalHomeLink.closest('li');
					  externalHomeHREF = null; //prevent infinite while loop possibility...
					  $externalHomeLink = null; //prevent infinite while loop possibility...
					  return $li;
				  }
				  else {
					return null;
				  }
			  }
			  $li = $li.parent();
			if($li.is('li') && !$li.hasClass(menuClass)) return $li;
		  }
		  return null;
	}
    var $li = $menuLink.closest('li');
	
    var maxBreadcrumbLinks = 20; //mostly an infinite loop preventer, no breadcrumb should really have more than this number of links
	while($li && $li.length && maxBreadcrumbLinks--){
      log("loop 1... maxBreadcrumbLinks=" + maxBreadcrumbLinks);
      var $item = $li.children('a,span.nolink').clone();
      log("item:" + $item.text())
	  if(stripHomeStringFromBCLinks) stripHomeString($item);
      if ($item.is('.nolink,.no-link,[href=""]')){
        $item = $('<span class="no-link">' + $item.html() + '</span>');
      }
      if ($item.is('a')) $item.attr('itemprop', 'url').wrapInner('<span itemprop="title"/>');
      else if ($item.is('span')) $item.attr('itemprop', 'title');
	  if (externalHomeHREF && externalHomeHREF == $item.attr('href')) externalHomeHREFFound = true;
      bc.unshift($item);
	  urls[$item.attr('href')] = true;
      $li = getParentLI($li);
    }
    if (addCurrentPageTitle){
      // the final breadcrumb should come from the current page title:
      var pageTitleText = $('.page-title h1,h1.page-title').text();
	  // SPECIAL CASE: if COC, get the text from document title instead:
	  if($('body').hasClass('section-council') && document.title.indexOf('>') > -1) pageTitleText = $.trim(document.title.split('|')[0].split('>')[1]);
      if (pageTitleText) {
		  var curPageURL =  window.location.pathname;
		  var ind = curPageURL.lastIndexOf('/index');
		  if(ind > -1 && ind == (curPageURL.length - '/index'.length)) curPageURL = curPageURL.substr(0, ind);
		  if(!(urls[curPageURL])){
			  var $currentPageBC = $('<a itemprop="url" ><span itemprop="title" class="page-title-breadcrumb"></span></a>');
			  var $currentPageBCText = $currentPageBC.find('.page-title-breadcrumb').text(pageTitleText);
			  $currentPageBC.attr('href', curPageURL);
			  if(stripHomeStringFromBCLinks) stripHomeString($currentPageBCText);
			  bc.push($currentPageBC);
			  urls[$currentPageBC.attr('href')] = true;
		  }
	  }
    }
	
    return bc;
  }
  function findMenuLink($menu, selector){
    var $l = null;
    if (!selector || !$menu) return null;
    if (typeof selector === 'string') {
      $l = $menu.find(selector).first();
      if ($l && $l.length) return $l;

      return null;
    }
    for (var i=0; i<selector.length; i++){
      $l = findMenuLink($menu, selector[i]);
      if ($l && $l.length) return $l;
    }
    return null;
  }
  function findMenuLinkByText($menu, txt){
    //this function will allow a case-insensitive text search...
    var $l = null;
    if (!txt || !$menu) return null;
    if (typeof txt === 'string') {
      txt = $.trim(txt.toLowerCase());
      $l = $menu.find("a").filter(function(){
        return $.trim($(this).text().toLowerCase()) === txt;
      }).first();
      if ($l && $l.length) return $l;

      return null;
    }
    for (var i=0; i<txt.length; i++){
      $l = findMenuLinkByText($menu, txt[i]);
      if ($l && $l.length) return $l;
    }
    return null;
  }
  function findMenuLinkByHREF($menu, href){
    //this function will allow a case-insensitive href search...
	    //first check for NIH login trigger and replace it
    if (href.indexOf("/eo/intranet/") !== -1) {
        href = href.replace("/eo/intranet/", "/");
    }
    var $l = null;
    if (!href || !$menu) return null;
    if (typeof href === 'string') {
      href = href.toLowerCase();
      $l = $menu.find("a[href]").filter(function(){
        return $(this).attr('href').toLowerCase() === href;
      }).first();
      if ($l && $l.length) return $l;

      return null;
    }
    for (var i=0; i<href.length; i++){
      $l = findMenuLinkByHREF($menu, href[i]);
      if ($l && $l.length) return $l;
    }
    return null;
  }
  function getMenuLinkFromPath($menu, path){
    //first let us try the path exactly "as-is", without changing the case
    var result = findMenuLinkByHREF($menu, path);
    if (result) return result;
    if (path.lastIndexOf('/') === path.length - 1){
      path = path.substr(0, path.length - 1);
    }
    else {
      path = path + '/';
    }
    result = findMenuLinkByHREF($menu, path);
    return result;
  }
  function getSubdomain(){
	  return window.location.hostname.split('.')[0];
  }
  var subdomainHomeTitleMappings = {
	  'dpcpsi' : 'DPCPSI',
	  'dpcpsi-test' : 'DPCPSI',
	  'commonfund' : 'Common Fund',
	  'commonfund-test' : 'Common Fund',
	  'ncf' : 'Common Fund',
	  'ncf-test' : 'Common Fund'
  };
  function getHomeTitle(){
	  var subdomain = getSubdomain();
	  return subdomainHomeTitleMappings[subdomain] || 'Home';
  }
  Drupal.behaviors.dynamic_breadcrumb = {
    // Purpose: Add a breadcrumb based on the current path's location in the main menu, or in the side menu plus the main menu.
    attach: function (context, settings) {
      $('body').not('.dynamic-breadcrumb-processed').each(function(){
          $(this).addClass('dynamic-breadcrumb-processed');
		  if (window.location.pathname === '/') return; // we do not want a breadcrumb on the home page
          var $existingBreadcrumb = $(this).find('.dynamic-breadcrumb');
          if($existingBreadcrumb.length && $.trim($existingBreadcrumb.text()) ) return;//there is already a breadcrumb... no need to create our own
          var $mainMenu = $(siteMainMenuSelector);
          if (!$mainMenu.length) return; // nothing to do, no menu to base it on...
          var $sideMenu = $(sideMenuSelector);
		  
          var $placeholder = $(placeholderSelector);
          if (!$placeholder.length) return; // nothing to do, cannot insert breadcrumbs anywhere...
          var $body = $('body');

          var homeURL = '/';
		  var homeTitle = getHomeTitle();
          var $bc = $(template.replace('{{home_url}}', homeURL).replace('{{home_title}}', homeTitle));
          var $bcList = $bc.find('.breadcrumb__list');
          var $menuLink = getMenuLinkFromPath($mainMenu, window.location.pathname);
          var $sideMenuLink = getMenuLinkFromPath($sideMenu, window.location.pathname);
          var linkFoundInMainMenu = $menuLink && $menuLink.length;
          var linkFoundInSideMenu = $sideMenuLink && $sideMenuLink.length;
		  var addCurrentPageTitle = false;
		  if (!(linkFoundInMainMenu || linkFoundInSideMenu)){
			  //if the current url is not found as-is in either menu, let's try to find the closest link...
			  var pathParts = window.location.pathname.split('/');
			  while(pathParts.length){
				pathParts.pop();
				$menuLink = getMenuLinkFromPath($mainMenu, pathParts.join('/'));
				linkFoundInMainMenu = $menuLink && $menuLink.length;
				if (linkFoundInMainMenu) {
					addCurrentPageTitle = true;
					break;
				}
			  }
		  }
		  var isExternalSection = $('body').is('.section-external:not(.section-ncf,.section-oar)');
		  
		  if (linkFoundInSideMenu){
				var bc2 = generateBreadcrumbs($sideMenuLink, addCurrentPageTitle, sideMenuClass);
				var sideMenuTitle = $sideMenu.find('.block__title:first').text();
				$menuLink = findMenuLinkByText($mainMenu, sideMenuTitle);
				var bc1 = $menuLink && $menuLink.length ? generateBreadcrumbs($menuLink, addCurrentPageTitle, mainMenuClass, isExternalSection) : [];
				
				//log("the generated breadcrumbs (via side menu):");
				//log(bc);
				for(var i=0; i<bc1.length; i++){
				  var $bi = $(breadcrumbItem);
				  $bi.prepend(bc1[i]);
				  $bcList.append($bi);
				}
				for(var i=0; i<bc2.length; i++){
				  var $bi = $(breadcrumbItem);
				  $bi.prepend(bc2[i]);
				  $bcList.append($bi);
				}
				$bcList.children().last().find('.breadcrumb__separator').remove();
				
				$placeholder.replaceWith($bc);
		  }
		  else if (linkFoundInMainMenu){
				var bc = generateBreadcrumbs($menuLink, addCurrentPageTitle, mainMenuClass, isExternalSection);
				log("the generated breadcrumbs (via main menu):");
				log(bc);
				for(var i=0; i<bc.length; i++){
				  //log(bc[i].html());
				  var $bi = $(breadcrumbItem);
				  $bi.prepend(bc[i]);
				  $bcList.append($bi);
				}
				$bcList.children().last().find('.breadcrumb__separator').remove();
				$placeholder.replaceWith($bc);
		  }
		  else {
            var selector = null;
			
			
			/*
            for(var selector in bodySelectorPathMapping){
              if ($body.is(selector)){
                //log("dynamic-breadcrumb.js: BODY MATCHES SELECTOR: " + selector)
                selector = bodySelectorPathMapping[selector];
                break;
              }
            } */
            if (!selector){
              log("dynamic-breadcrumb.js: no mapped selector for this node was found");
              return;
            }
            $menuLink = findMenuLink($mainMenu, selector);
            if (!$menuLink || !$menuLink.length){
              log("dynamic-breadcrumb.js: could not find link in main menu with href " + selector);
              return;
            }
			
          }
      });
    }
  };
})(Drupal, jQuery);