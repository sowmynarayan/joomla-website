<?php

/**************************************************************
* This file is part of Remository
* Copyright (c) 2006 Martin Brampton
* Issued as open source under GNU/GPL
* For support and other information, visit http://remository.com
* To contact Martin Brampton, write to martin@remository.com
*
* Remository started life as the psx-dude script by psx-dude@psx-dude.net
* It was enhanced by Matt Smith up to version 2.10
* Since then development has been primarily by Martin Brampton,
* with contributions from other people gratefully accepted
*/

// Affects number of files shown per page
DEFINE('_ITEMS_PER_PAGE','10');
// Default file listing order
// Possible values are 1 = File ID, 2 = File title, 3 = Download count (descending), 
// 4 = Submit date (descending), 5 = User Name (submitter), 6 = Author, 
// 7 = rating (descending)
DEFINE('_REM_DEFAULT_ORDERING', 2);

// Definitions for the log file type
DEFINE('_LOG_DOWNLOAD', 1);
DEFINE('_LOG_UPLOAD', 2);
DEFINE('_REM_VOTE_USER_GENERAL','3');

// Determines how many pages will be shown around the current one
DEFINE('_PAGE_SPREAD','9');

// Determines whether email notifications will be sent
DEFINE('_REMOSITORY_EMAIL_ACCESSORS', 0);
DEFINE('_REMOSITORY_EMAIL_COMMENTS_ACCESSORS', 0);

// Determine whether search will be shown on every folder/file listing page
DEFINE ('_REMOSITORY_TOP_SEARCH', 0);

// Definitions for use with subscription management
// Resource types
DEFINE ('_REMOSITORY_SUBS_EVERYTHING', 1);
DEFINE ('_REMOSITORY_COUNT_DOWNLOAD', 41);
DEFINE ('_REMOSITORY_COUNT_UPLOAD', 42);
DEFINE ('_REMOSITORY_COUNT_EDIT', 43);
DEFINE ('_REMOSITORY_COUNT_DOWNLOAD_PLUS', 51);
DEFINE ('_REMOSITORY_COUNT_UPLOAD_PLUS', 52);
DEFINE ('_REMOSITORY_COUNT_EDIT_PLUS', 53);

DEFINE('_THUMBNAILS_PER_COLUMN',2);
DEFINE('_REMOS_OPERATING_SYSTEMS','Linux,Windows,Mac,Palm,Other');
DEFINE('_REMOS_APP_BUGBEARS','no-ads,no-nags,no-spyware,no-limited-functionality');
DEFINE('_REMOS_LEGAL_TYPES','Free,Free for non-commercial use,GNU GPL');

// Used to check if a remote file has been specified
DEFINE('_REMOSITORY_REGEXP_URL','^(https?|ftps?)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*[^\.\,\)\(\s]');
DEFINE('_REMOSITORY_REGEXP_IP','^(https?|ftps?)\://\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*[^\.\,\)\(\s]');
