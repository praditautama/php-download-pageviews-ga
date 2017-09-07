# php-download-pageviews-ga

simple php script to download data from Google Analytics

# Requirement
*PHP
*Access Token from https://ga-dev-tools.appspot.com/query-explorer/

# Usage

```
git@github.com:praditautama/php-download-pageviews-ga.git
cd php-download-pageviews-ga
```

Change access_token

then run
```
php ga.php [START_DATE] [END_DATE]
```

```
php ga.php 2016-01-01 2016-12-31
```

# CSV Fields

[slug],[PV],[YYYY-MM]