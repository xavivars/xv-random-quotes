XV Random Quotes
======================

[![Build Status](https://api.travis-ci.org/xavivars/xv-random-quotes.png?branch=master)](https://travis-ci.org/xavivars/xv-random-quotes)

Welcome to the XV Random Quotes GitHub repository
----------------------------------------------

XV Random Quotes is a plugin that displays and rotates quotes and expressions 
anywhere on your blog. Easy to customize and manage. Multiuser-powered. Ajax enabled.

Support
-------
This is a developer's portal for Yoast SEO and should not be used for support. Please visit the
[support forums](https://wordpress.org/support/plugin/xv-random-quotes).

Reporting bugs
----
I'm actively working on this plugin, and I'll try to fix as many bugs as I can

[![Throughput Graph](https://graphs.waffle.io/xavivars/xv-random-quotes/throughput.svg)](https://waffle.io/xavivars/xv-random-quotes/metrics)

If you find an issue, [let me know here](https://github.com/xavivars/xv-random-quotes/issues/new)! 

## Development

This plugin uses Docker with a single `docker-compose.yml` providing two isolated environments:

### Manual Development & Testing (web + db)

```bash
# Start WordPress site
docker-compose up -d

# Access at http://localhost:8080
# Database is persistent - data survives restarts

# Stop (keeps data)
docker-compose down

# Fresh start (wipes database)
docker-compose down -v
docker-compose up -d
```

### Automated Testing (cli + testdb)

```bash
# Run tests
docker-compose run --rm cli vendor/bin/phpunit

# Database resets automatically - clean slate for each test run
```

**Key Point:** Both environments can run simultaneously without interfering - they use separate databases (`db` vs `testdb`).

See [DOCKER_USAGE.md](DOCKER_USAGE.md) for detailed usage.

Contributions
-------------
Anyone is welcome to contribute to XV Random Quotes plugin. 
Please create a pull request with your changes and I'll try to review it as soon as possible