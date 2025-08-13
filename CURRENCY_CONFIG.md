# Currency Configuration

The application now supports configurable currency settings via environment variables.

## Environment Variables

Add these variables to your `.env` file to configure currency display:

```bash
# Currency Configuration
CURRENCY_SYMBOL=$
CURRENCY_CODE=USD
CURRENCY_DECIMALS=2
CURRENCY_DECIMAL_SEPARATOR=.
CURRENCY_THOUSANDS_SEPARATOR=,
```

## Supported Settings

| Variable | Description | Default | Examples |
|----------|-------------|---------|----------|
| `CURRENCY_SYMBOL` | Currency symbol to display | `$` | `$`, `€`, `£`, `¥` |
| `CURRENCY_CODE` | Three-letter currency code | `USD` | `USD`, `EUR`, `GBP`, `JPY` |
| `CURRENCY_DECIMALS` | Number of decimal places | `2` | `0`, `2`, `3` |
| `CURRENCY_DECIMAL_SEPARATOR` | Decimal separator character | `.` | `.`, `,` |
| `CURRENCY_THOUSANDS_SEPARATOR` | Thousands separator character | `,` | `,`, `.`, ` ` (space) |

## Examples

### US Dollar (Default)
```bash
CURRENCY_SYMBOL=$
CURRENCY_CODE=USD
CURRENCY_DECIMALS=2
CURRENCY_DECIMAL_SEPARATOR=.
CURRENCY_THOUSANDS_SEPARATOR=,
```
Result: `$1,234.56`

### Euro
```bash
CURRENCY_SYMBOL=€
CURRENCY_CODE=EUR
CURRENCY_DECIMALS=2
CURRENCY_DECIMAL_SEPARATOR=,
CURRENCY_THOUSANDS_SEPARATOR=.
```
Result: `€1.234,56`

### British Pound
```bash
CURRENCY_SYMBOL=£
CURRENCY_CODE=GBP
CURRENCY_DECIMALS=2
CURRENCY_DECIMAL_SEPARATOR=.
CURRENCY_THOUSANDS_SEPARATOR=,
```
Result: `£1,234.56`

### Japanese Yen (No decimals)
```bash
CURRENCY_SYMBOL=¥
CURRENCY_CODE=JPY
CURRENCY_DECIMALS=0
CURRENCY_DECIMAL_SEPARATOR=.
CURRENCY_THOUSANDS_SEPARATOR=,
```
Result: `¥1,235`

## Usage in Code

### Backend (PHP)
```php
use App\Helpers\CurrencyHelper;

// Format currency
echo CurrencyHelper::format(1234.56); // $1,234.56

// Get currency symbol
echo CurrencyHelper::symbol(); // $

// Get currency code
echo CurrencyHelper::code(); // USD
```

### Frontend (Vue.js)
```javascript
import { formatCurrency, getCurrencySymbol, getCurrencyCode } from '@/utils/currency.js'

// Format currency
const price = formatCurrency(1234.56) // $1,234.56

// Get currency symbol
const symbol = getCurrencySymbol() // $

// Get currency code
const code = getCurrencyCode() // USD
```

## Implementation Details

- Currency configuration is loaded from environment variables in `config/app.php`
- Backend helper class: `App\Helpers\CurrencyHelper`
- Frontend utility functions: `resources/js/utils/currency.js`
- Currency settings are shared with frontend via Inertia.js middleware
- All price displays throughout the application use these currency functions

## After Changing Currency Settings

1. Update your `.env` file with new currency values
2. Clear application cache: `php artisan config:clear`
3. Restart your development server
4. The new currency format will be applied throughout the application 