# Cloudflare Rules Notes

This file documents the recommended Cloudflare rules for the EIMS websites and
ClientManager server-to-server requests.

## Goal

- Allow Bangladesh users to access the website normally.
- Block all non-Bangladesh users.
- Allow selected server IPs to access the website/API using cURL or Laravel HTTP.
- Allow trusted API/machine calls from Bangladesh or selected server IPs.
- Disable Browser Integrity Check only for trusted API paths.
- Do not allow public non-Bangladesh users just because they send an AJAX header.

## Allowed ClientManager Server IPs

Use both IPv4 and IPv6 because Cloudflare may see either address depending on
the outbound connection.

```text
45.77.170.147
2001:19f0:4401:1147:5400:5ff:fe99:4344
```

## Rule 1: Only BD Or Allowed Server IPs

Use this as the main country restriction rule.

Rule name:

```text
Only BD or Allowed Server IPs
```

Expression:

```text
(
  not ip.src.country in {"BD"}
  and not ip.src in {
    45.77.170.147
    2001:19f0:4401:1147:5400:5ff:fe99:4344
  }
)
```

Action:

```text
Block
```

What this does:

```text
BD users: allowed
Selected server IPs: allowed
All other non-BD users: blocked
```

Important note:

Do not add a general AJAX allow rule such as `X-Requested-With =
XMLHttpRequest`. That header can be spoofed by anyone, so it should not be used
as the only security condition.

## Rule 2: Browser Integrity Check Off For Trusted API Paths

Use Cloudflare Configuration Rules for this.

Purpose:

```text
Stop Browser Integrity Check from blocking trusted API/machine requests.
```

Known API paths:

```text
/api/v1/RocketApi.php
/api/ClntManApi.php
/EIMS/HttpCall.php
```

Cloudflare may reject complex `OR` expressions in the visual Expression Builder.
If that happens, create smaller builder-friendly rules.

### Rule 2A: BIC Off For BD API Requests

Rule type:

```text
Configuration Rule
```

Rule name:

```text
BIC Off for BD API Requests
```

Expression:

```text
http.request.uri.path in {"/api/v1/RocketApi.php" "/api/ClntManApi.php" "/EIMS/HttpCall.php"}
and
ip.src.country eq "BD"
```

Setting:

```text
Browser Integrity Check: Off
```

### Rule 2B: BIC Off For ClientManager IPv4

Rule type:

```text
Configuration Rule
```

Rule name:

```text
BIC Off for ClientManager IPv4
```

Expression:

```text
http.request.uri.path in {"/api/v1/RocketApi.php" "/api/ClntManApi.php" "/EIMS/HttpCall.php"}
and
ip.src eq 45.77.170.147
```

Setting:

```text
Browser Integrity Check: Off
```

### Rule 2C: BIC Off For ClientManager IPv6

Rule type:

```text
Configuration Rule
```

Rule name:

```text
BIC Off for ClientManager IPv6
```

Expression:

```text
http.request.uri.path in {"/api/v1/RocketApi.php" "/api/ClntManApi.php" "/EIMS/HttpCall.php"}
and
ip.src eq 2001:19f0:4401:1147:5400:5ff:fe99:4344
```

Setting:

```text
Browser Integrity Check: Off
```

What these rules do:

```text
BD API/machine requests: Browser Integrity Check disabled
ClientManager server API requests: Browser Integrity Check disabled
Public non-BD users: still blocked by Rule 1
```

## Optional Rule 3: Skip WAF For Server API Calls

Use this only if the selected server IP is still blocked by Managed Rules, Bot
Fight Mode, Browser Integrity Check, or rate limiting.

Place this rule above the country block rule.

Rule name:

```text
Skip WAF for ClientManager API Calls
```

Expression for `salandarhighschool.edu.bd`:

```text
(
  http.request.uri.path eq "/api/ClntManApi.php"
  and ip.src in {
    45.77.170.147
    2001:19f0:4401:1147:5400:5ff:fe99:4344
  }
)
```

Expression for `dhst.edu.bd`:

```text
(
  http.request.uri.path eq "/EIMS/HttpCall.php"
  and ip.src in {
    45.77.170.147
    2001:19f0:4401:1147:5400:5ff:fe99:4344
  }
)
```

If both paths exist on the same Cloudflare zone, use this combined expression:

```text
(
  http.request.uri.path in {"/api/ClntManApi.php" "/EIMS/HttpCall.php"}
  and ip.src in {
    45.77.170.147
    2001:19f0:4401:1147:5400:5ff:fe99:4344
  }
)
```

Action:

```text
Skip
```

Recommended skip options:

```text
All remaining custom rules
All managed rules
All rate limiting rules
Super Bot Fight Mode
```

What this does:

```text
Only the selected server IPs can bypass Cloudflare security checks for the API
paths. Public users outside Bangladesh remain blocked by Rule 1.
```

## Optional Rule 4: Skip Browser Integrity Check Via WAF Skip

Use this when Cloudflare Security Events shows:

```text
Mitigation: Block by Browser integrity check
```

This often happens for server-to-server/API callbacks with user agents such as:

```text
Java/1.8.0_451
curl/*
GuzzleHttp/*
```

Example blocked path:

```text
/api/v1/RocketApi.php
```

Recommended rule name:

```text
Skip BIC for Trusted API Calls
```

Safer expression for Rocket API callbacks from Bangladesh:

```text
(
  http.request.uri.path eq "/api/v1/RocketApi.php"
  and ip.src.country eq "BD"
)
```

More restrictive expression if the bank/provider gives fixed IPs:

```text
(
  http.request.uri.path eq "/api/v1/RocketApi.php"
  and ip.src in {
    103.11.136.153
  }
)
```

If the provider uses multiple IPs from the same ASN and you trust that ASN:

```text
(
  http.request.uri.path eq "/api/v1/RocketApi.php"
  and ip.geoip.asnum eq 58445
)
```

Action:

```text
Skip
```

Recommended skip options:

```text
Browser Integrity Check
All managed rules
All rate limiting rules
Super Bot Fight Mode
```

Prefer Rule 2 Configuration Rules above. Use this WAF Skip approach only if your
Cloudflare plan/UI supports skipping Browser Integrity Check from WAF custom
rules.

If `Browser Integrity Check` is not available inside the WAF skip options, use
Rule 2 Configuration Rules instead.

```text
Expression:
http.request.uri.path eq "/api/v1/RocketApi.php"

Setting:
Browser Integrity Check = Off
```

For better security, include a trusted source condition in the Configuration
Rule expression:

```text
(
  http.request.uri.path eq "/api/v1/RocketApi.php"
  and (
    ip.src.country eq "BD"
    or ip.src in {
      45.77.170.147
      2001:19f0:4401:1147:5400:5ff:fe99:4344
    }
  )
)
```

## Recommended Rule Order

Final recommended setup:

```text
1. Configuration Rule: BIC Off for BD API Requests
2. Configuration Rule: BIC Off for ClientManager IPv4
3. Configuration Rule: BIC Off for ClientManager IPv6
4. WAF Custom Rule: Only BD or Allowed Server IPs
5. Other security rules
```

Optional setup if selected server IPs are still blocked by WAF/Bot/Rate Limiting:

```text
1. WAF Custom Rule: Skip WAF for ClientManager API Calls
2. Configuration Rule: BIC Off for BD API Requests
3. Configuration Rule: BIC Off for ClientManager IPv4
4. Configuration Rule: BIC Off for ClientManager IPv6
5. WAF Custom Rule: Only BD or Allowed Server IPs
6. Other security rules
```

Important:

```text
Configuration Rules and WAF Custom Rules are different rule systems.
Use Configuration Rules to turn Browser Integrity Check off.
Use WAF Custom Rules to block non-BD traffic.
```

## Applying Rules To All Domains

Cloudflare dashboard rules are normally configured per domain/zone.

For all domains at once:

```text
Enterprise plan: use Account-level WAF custom rulesets.
Non-Enterprise plans: create the same rules on each zone/domain.
```

Alternatives for many domains:

```text
1. Manually copy the rules to each zone.
2. Use Cloudflare API automation.
3. Use Terraform to manage Cloudflare rules as code.
```

Be careful with global/account-level rules because API paths may not exist on
every domain.

## Testing From The Server

Test using IPv4:

```bash
curl -4 -i "https://salandarhighschool.edu.bd/api/ClntManApi.php?action=getTotalStudent&api_token=YOUR_TOKEN&year=2026"
```

Test the other endpoint:

```bash
curl -4 -i "https://dhst.edu.bd/EIMS/HttpCall.php"
```

Expected result:

```text
The response should be JSON or the expected API output.
It should not be a Cloudflare 403 HTML page.
```

## Debugging

If requests are still blocked:

1. Open Cloudflare dashboard.
2. Go to `Security > Events`.
3. Trigger the request again.
4. Open the blocked event.
5. Check which rule or security feature blocked it.

Common blockers:

```text
Country block custom rule
Managed WAF rule
Browser Integrity Check
Bot Fight Mode
Rate limiting rule
IP Access Rule
```

If Cloudflare shows the request IP as IPv6, make sure this IP is included:

```text
2001:19f0:4401:1147:5400:5ff:fe99:4344
```
