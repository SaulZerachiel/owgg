const unavailableValues = new Set(['/', '-', '—', '–', 'n/a']);

export function isAvailableStatValue(value: unknown): boolean {
    if (value === null || value === undefined) {
        return false;
    }

    if (typeof value !== 'string') {
        return true;
    }

    const normalized = value.trim().toLocaleLowerCase();

    return normalized !== '' && !unavailableValues.has(normalized);
}
