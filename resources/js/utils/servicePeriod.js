export const toDateInput = (value) => {
    if (!value) {
        return ''
    }

    return String(value).slice(0, 10)
}

export const servicePeriodStart = (service, serviceType) => {
    if (!service) {
        return ''
    }

    const currentDate = serviceType === 'hosting'
        ? service.renewal_date
        : service.expiry_date
    const initialDate = {
        domain: service.registration_date,
        hosting: service.start_date,
        ssl_certificate: service.issue_date,
    }[serviceType]

    return toDateInput(service.last_billing_date || currentDate || initialDate)
}

export const addOneYear = (value) => {
    const dateValue = toDateInput(value)
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(dateValue)

    if (!match) {
        return ''
    }

    const [, year, month, day] = match
    const targetYear = Number(year) + 1
    const targetMonth = Number(month)
    const targetDay = Number(day)
    const daysInTargetMonth = new Date(Date.UTC(targetYear, targetMonth, 0)).getUTCDate()

    return [
        targetYear,
        String(targetMonth).padStart(2, '0'),
        String(Math.min(targetDay, daysInTargetMonth)).padStart(2, '0'),
    ].join('-')
}
