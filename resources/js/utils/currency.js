import { usePage } from '@inertiajs/vue3'

/**
 * Format a number as currency using the configured currency settings
 */
export function formatCurrency(amount) {
    const { currency } = usePage().props
    
    if (isNaN(amount) || amount === null || amount === undefined) {
        amount = 0
    }
    
    const formattedAmount = Number(amount).toLocaleString('en-US', {
        minimumFractionDigits: currency.decimals,
        maximumFractionDigits: currency.decimals
    })
    
    return currency.symbol + formattedAmount
}

/**
 * Get the currency symbol
 */
export function getCurrencySymbol() {
    const { currency } = usePage().props
    return currency.symbol
}

/**
 * Get the currency code
 */
export function getCurrencyCode() {
    const { currency } = usePage().props
    return currency.code
} 