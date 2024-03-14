# Changelog

All notable changes to `lunar-api-stripe-adapter` will be documented in this file.

## v0.8.4 - 2024-03-14

### What's Changed

* Fix payment intent failed webhook by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/8

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.8.3...0.8.4

## v0.8.3 - 2024-02-16

### What's Changed

* Find order redundancy by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/7

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.8.2...0.8.3

## v0.8.2 - 2024-02-09

### What's Changed

* Comply with Lunar API 0.8.1 by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/6

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.8.1...0.8.2

## v0.8.1 - 2024-02-07

### What's Changed

* Stripe webhooks on queue by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/5
* Update tests

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.8.0...0.8.1

## v0.8.0 - 2024-02-06

### What's Changed

* Updated to [Lunar API 0.8](https://github.com/dystcz/lunar-api)
* Handle webhooks on queue by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/4

#### Breaking ⚠️

* `OrderPaid` event was renamed to `OrderPaymentSuccessful` https://github.com/dystcz/lunar-api-stripe-adapter/commit/d55de6298612cd9790f7f49b1dbc45934a77c7b9

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.7.1...0.8.0

## v0.7.2 - 2024-01-18

### What's Changed

* Handle webhooks on queue via `HandleWebhook` https://github.com/dystcz/lunar-api-stripe-adapter/commit/0b516495ab9c3fada78b073a1385b6e47a1a44c5

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.7.1...0.7.2

## v0.7.1 - 2024-01-12

### What's Changed

* Minor fixes, but should prevent webhooks from failing

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/compare/0.7.0...0.7.1

## v0.7.0 - 2024-01-02

* Re-release
* From now on this package version will follow [Lunar](https://github.com/lunarphp/lunar) main version again
* All changes should go through PRs

### What's Changed

* Upgrade to Lunar API 0.5 by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/1
* Return client secret when creating payment intent by @theimerj in https://github.com/dystcz/lunar-api-stripe-adapter/pull/2

**Full Changelog**: https://github.com/dystcz/lunar-api-stripe-adapter/commits/0.7.0
