export const CLIENT_TYPES = [
    { value: 'government', label: 'Government' },
    { value: 'non_government', label: 'Non-Government' },
    { value: 'mpo_based', label: 'MPO-Based' },
    { value: 'private', label: 'Private' },
    { value: 'other', label: 'Other' },
]

export const formatClientType = (value) => {
    return CLIENT_TYPES.find(type => type.value === value)?.label || 'Other'
}
