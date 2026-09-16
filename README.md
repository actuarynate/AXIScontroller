# ELink PHP API Console

A small server-side PHP front end for the ELink API described by the supplied OpenAPI document.

## Requirements

- PHP 8.1+
- PHP cURL extension
- HTTPS for production
- Network access from the PHP host to the ELink API

## Setup

1. Copy `config.example.php` to `config.php`.
2. Keep `verify_tls` enabled. If the non-production endpoint uses an internal CA, install that CA on the PHP host rather than disabling verification.
3. From this directory, test locally with `php -S localhost:8080`.
4. Open `http://localhost:8080` in a browser.

## Included workflow

- Login through `POST /srv-api/v0/account/login`
- Store the returned JWT only in the server-side PHP session
- Submit a batch through `POST /api/v0/tasks/batches`
- Poll task status through `GET /api/v0/tasks?id={id}`
- Retrieve output through `GET /api/v0/tasks/{id}/results`

## Security notes

Do not commit `config.php`, credentials, session files, tokens, or production URLs to source control. Place this application behind your organization's approved authentication and authorization controls before broader deployment. The browser calls only the local PHP proxy, so the JWT is not exposed to JavaScript.
