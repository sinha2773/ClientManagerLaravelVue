# Services module (domains, hosting, SSL)

Last updated: 2026-11-04

## Where things are

| Service | Controller | Pages | Start date | End date |
| --- | --- | --- | --- | --- |
| Domain | `DomainController` | `resources/js/Pages/Domains/` | `registration_date` | `expiry_date` |
| Hosting | `HostingServiceController` | `resources/js/Pages/HostingServices/` | `start_date` | `renewal_date` |
| SSL | `SslCertificateController` | `resources/js/Pages/SslCertificates/` | `issue_date` | `expiry_date` |

Date permission tests are in `tests/Feature/ServiceDateAuthorizationTest.php`.

## Date permissions

| Action | Account manager | Approver |
| --- | --- | --- |
| Create | Enters the start date; the end date is forced to one year later | Enters both |
| Edit | Cannot change either date | Can change both; the end date must be after the start date |

- The server enforces this in each controller's `update()`: date rules are only added for approvers (`canApproveBills()`), so dates sent by anyone else are dropped.
- The forms use the `canEditRenewal` prop to make the date inputs read-only for account managers.

## Behaviour to preserve

- **Bills move end dates:** a paid, approved bill updates the service's end date and `last_billing_date`. See `Bill::renewService()` and [BILLS_MODULE.md](BILLS_MODULE.md).
- **Bills are not updated from services:** changing dates on a service leaves its existing bills as they are. The next bill's start date comes from the previous bill's renewal date, not from the service.

## Date inputs

`DomainController::edit()` passes the whole model, so its dates arrive as ISO datetimes and `Domains/Edit.vue` passes them through `toDateInput()`. The hosting and SSL `edit()` actions already format them as `Y-m-d`. `toDateInput()` keeps the first 10 characters, which is only correct while `app.timezone` is `UTC`.
