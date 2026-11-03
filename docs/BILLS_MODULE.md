# Bills module

Last updated: 2026-11-03

## Where things are

| Concern | File |
| --- | --- |
| Routes | `routes/web.php` (`Route::resource('bills', ...)`, plus `approve`, `payment`, `fetch-student-summary`) |
| Controller | `app/Http/Controllers/BillController.php` |
| Validation (create and update) | `BillController::validateBillData()` |
| Model, renewal logic, bill number | `app/Models/Bill.php` |
| Forms | `resources/js/Pages/Bills/Create.vue`, `Edit.vue` |
| Display | `resources/js/Pages/Bills/Show.vue`, `Index.vue` |
| Date helpers | `resources/js/utils/servicePeriod.js` |
| Tests | `tests/Feature/BillCreationTest.php` |

`Create.vue` and `Edit.vue` are separate forms with no shared component, so a form change has to be made in both.

## Adding a field

1. Add a migration in `database/migrations/` that alters `bills`.
2. `Bill.php`: add to `$fillable`, and to `$casts` if it is not a string.
3. `BillController::validateBillData()`: add a rule. Fields without a rule are dropped, because only validated data is saved.
4. `Create.vue`: add the input and the `useForm` default.
5. `Edit.vue`: add the input and the `useForm` value from `props.bill`.
6. `Show.vue` and `Index.vue`: add it if it should be displayed.
7. `BillCreationTest.php`: add a test.
8. Update the field table below.

The `edit` and `show` actions pass the whole `Bill` model, so a new column reaches the pages with no controller change.

## Fields worth knowing

| Field | Type | Notes |
| --- | --- | --- |
| `bill_number` | string | Generated on create as `BILL-YYYYMM-0001` |
| `manual_invoice` | string, nullable, max 255 | Free text. Optional, and not required to be unique. Empty input is stored as `NULL`. |
| `adjust_with_salary` | boolean, default `0` | Checkbox "Adjust with Salary?": `1` = Yes, `0` = No |
| `service_type` | enum | `domain`, `hosting`, `ssl_certificate`, `eims_fee` |
| `service_id` | int | Points at the table for `service_type`. For `eims_fee` it is a domain id. |
| `service_started_date`, `service_renewal_date` | date | `NULL` for `eims_fee` |
| `academic_year` | string | Required. Options come from `App\Support\AcademicYear`. |

`manual_invoice` and `adjust_with_salary` are edited on the two forms and shown on `Index.vue`, under Bill Number and Payment Status. They are not on `Show.vue` or the printed invoice, and `adjust_with_salary` has no link to payroll (`PaySalaryController`) yet.

## Index filters

`BillController::index()` filters by `payment_status`, `status`, `service_type`, `academic_year` and `adjust_with_salary` (`1` = Yes, `0` = No, empty = all). `search` matches bill number, description and client name, but not `manual_invoice`.

A new filter needs a query condition, an entry in the `filters` list passed to the page, and a control plus default in `Index.vue`.

## Permissions

Checked in the controller through `User::canManageBills()` and `User::canApproveBills()`.

| Action | Account manager | Approver |
| --- | --- | --- |
| View, create | Yes | Yes |
| Edit a draft | Yes | Yes |
| Edit a non-draft bill | No | Yes, but `client_id`, `service_type` and `service_id` are locked |
| Change started and renewal dates | Only the started date on a service's first bill | Yes, on any bill, including non-draft bills |
| Delete | No | Yes |

## Behaviour to preserve

- **Renewal period:** the first bill for a service takes a manually entered start date, and later bills start at the previous bill's renewal date. Renewal defaults to one year later. An approver's submitted dates override all of this, and the renewal date must still be after the started date. See `normalizeServicePeriod()` and `billingPeriods()`.
- **Renewing a service:** a bill renews its service only once it is both paid and approved, and only once (`Bill::renewService()`, `service_renewed_at`). Editing the renewal date of an already renewed bill updates the service through `syncRenewedService()`.
- **EIMS amount:** the forms calculate `students × monthly fee × months − discount`, floored at 0.
- **Dates in forms:** the model serializes dates as ISO datetimes, which a date input cannot display, so `Edit.vue` passes them through `toDateInput()`.
- **Due date rules:** it must be today or later when creating a bill. Editing allows past dates, so an overdue bill can still be saved.
